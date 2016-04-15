<?php

namespace Workshop
{
	class SignUp extends \System\Model\Perm
	{
		const USE_INITIAL_DATA = true;
		const PRICE_DISCOUNT = 1200;
		const PRICE_FULL     = 1400;
		const PRICE_LUNCH    = 180;
		const PRICE_ACCOMODATION = 600;

		const DEADLINE_DISCOUNT = '2016-04-01';

		protected static $prices_cancel = array(
			array(
				"after" => '2016-01-01',
				"price" => .2
			),

			array(
				"after" => '2016-04-08',
				"price" => .8
			),

			array(
				"after" => '2016-05-01',
				"price" => .8
			),
		);


		protected static $attrs = array(
			'name_first' => array("type" => 'varchar'),
			'name_last'  => array("type" => 'varchar'),
			'team'       => array("type" => 'varchar'),
			'email'      => array("type" => 'email'),
			'phone'      => array("type" => 'varchar'),
			'birthday'   => array("type" => 'varchar'),

			'lunch'      => array("type" => 'bool'),
			'hotel' => array("type" => 'bool'),
			'newsletter' => array("type" => 'bool'),
			'rules' => array("type" => 'bool'),
			'paid'       => array("type" => 'bool'),
			'solved'     => array("type" => 'bool'),

			'sent_confirm' => array("type" => 'bool', "default" => false),
			'sent_general' => array("type" => 'bool', "default" => false),
			'sent_lunch'   => array("type" => 'bool', "default" => false),
			'sent_match'   => array("type" => 'bool', "default" => false),
			'sent_camp'    => array("type" => 'bool', "default" => false),
			'sent_workshopAdditional'=> array("type" => 'bool', "default" => false),

			'check'      => array(
				"type"    => 'has_one',
				"model"   => 'Workshop\Check',
				"is_null" => true
			),

			'workshops'  => array(
				"bound_to" => 'signups',
				"type" => 'has_many',
				"model" => 'Workshop',
				"is_bilinear" => true,
				"is_master" => true
			),

			'assigned_to' => array(
				"bound_to" => 'assignees',
				"type"     => 'belongs_to',
				"model"    => 'Workshop',
				"is_null"  => true
			),

			'food' => array(
				"type"  => 'has_many',
				"model" => 'Food\Item',
				"is_bilinear" => true,
				"is_master"   => true
			),
		);


		protected static $access = array(
			"browse" => true,
			"schema" => true,
		);


		public function save()
		{
			parent::save();

			if (!$this->check) {
				$this->create_check();
			}

			if ($this->solved) {
				$this->mail_reassignment($this->get_reassignment_op());
			}

			return $this;
		}


		public function get_reassignment_op()
		{
			$ass_ini = null;
			$ass = $this->assigned_to;
			$notify = null;

			if (isset($this->data_initial['id_assigned_to'])) {
				$ass_ini = $this->data_initial['id_assigned_to'];
			}

			if (!$ass_ini && $ass) {
				$notify = 'assigned';
			} else if ($ass_ini && !$ass) {
				$notify = 'removed';
			} else if ($ass_ini && $ass) {
				$id_new = $ass;

				if ($ass instanceof \Workshop) {
					$id_new = $ass->id;
				}

				if ($ass_ini != $id_new) {
					$notify = 'reassigned';
				}
			}

			return $notify;
		}


		public function mail_reassignment($op)
		{
			$prev = null;

			if (!$op) {
				return $this;
			}

			if (isset($this->data_initial['id_assigned_to'])) {
				$prev = \Workshop::find($this->data_initial['id_assigned_to']);
			}

			$pref = $this->workshops->sort_by('id_workshop_signup_has_workshop')->fetch();

			$ren = new \System\Template\Renderer\Txt();
			$ren->reset_layout();
			$ren->partial('mail/signup/assignment', array(
				"item" => $this,
				"op"   => $op,
				"ws"   => $this->assigned_to,
				"prev" => $prev,
				"pref" => $pref,
			));

			$mail = new \Helper\Offcom\Mail(array(
				'rcpt'     => array($this->email),
				'subject'  => 'Improtřesk 2016 - Přihláška, zařazení na workshop',
				'reply_to' => \System\Settings::getSafe(array('offcom', 'default', 'reply_to'), null),
				'message'  => $ren->render_content()
			));

			$mail->send();
		}


		public function get_price()
		{
			$deadline = new \DateTime(self::DEADLINE_DISCOUNT);
			$price    = self::PRICE_FULL;

			if ($this->created_at < $deadline) {
				$price = self::PRICE_DISCOUNT;
			}

			if ($this->lunch) {
				$price += self::PRICE_LUNCH;
			}

			if ($this->hotel) {
				$price += self::PRICE_ACCOMODATION;
			}

			return $price;
		}


		protected function create_check()
		{
			$check = new \Workshop\Check(array(
				'amount' => $this->get_price(),
				'signup' => $this
			));

			$check->save();
		}


		public function mail_confirm(\System\Http\Response $res)
		{
			$ren = \System\Template\Renderer\Txt::from_response($res);
			$ren->reset_layout();
			$ren->partial('mail/signup/confirm', array(
				"item" => $this,
				"check" => $this->check,
				"workshops" => $this->workshops->sort_by('id_workshop_signup_has_workshop')->fetch()
			));

			$mail = new \Helper\Offcom\Mail(array(
				'rcpt'     => array($this->email),
				'subject'  => 'Improtřesk 2016 - Potvrzení přihlášky',
				'reply_to' => \System\Settings::getSafe(array('offcom', 'default', 'reply_to'), null),
				'message'  => $ren->render_content()
			));

			$mail->send();

			$this->sent_confirm = true;
			$this->save();
		}


		public function update_ballance()
		{
			$this->paid = $this->check->is_paid;
			$this->save();

			return $this;
		}


		public function mail_payment_update()
		{
			$check = $this->check;

			if (!$check) {
				return;
			}

			$payments = $check->payments->fetch();
			$paid = 0;

			foreach ($payments as $p) {
				$paid += $p->amount;
			}

			$ren = new \System\Template\Renderer\Txt();
			$ren->reset_layout();
			$ren->partial('mail/signup/payment-update', array(
				"item" => $this,
				"check" => $check,
				"paid"  => $paid,
				"payments" => $payments
			));

			$mail = new \Helper\Offcom\Mail(array(
				'rcpt'     => array($this->email),
				'subject'  => 'Improtřesk 2016 - Přihláška, platební aktualizace',
				'reply_to' => \System\Settings::getSafe(array('offcom', 'default', 'reply_to'), null),
				'message'  => $ren->render_content()
			));

			$mail->send();

			$this->sent_notif = true;
			$this->save();
		}
	}
}

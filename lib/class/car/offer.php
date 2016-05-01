<?php

namespace Car
{
	class Offer extends \System\Model\Perm
	{
		protected static $attrs = array(
			"color"     => array("type" => 'varchar'),
			"desc"      => array("type" => 'text', 'is_null' => true),
			"driver"    => array("type" => 'varchar'),
			"from"      => array("type" => 'varchar'),
			"departure" => array("type" => 'datetime'),
			"phone"     => array("type" => 'varchar'),
			"email"     => array("type" => 'email'),
			"ident"     => array("type" => 'varchar', 'is_unique' => true),
			"icon"      => array(
				"type" => 'varchar',
				"default" => 'sedan',
				"options" => array(
					'sedan'        => 'sedan',
					'combi'        => 'combi',
					'hatchback'    => 'hatchback',
					'cabriolet'    => 'cabriolet',
					'mpv'          => 'mpv',
					'suv'          => 'suv',
					'limousine'    => 'limousine',
					'tank'         => 'tank',
				),
			),
			"requests"  => array(
				"type"  => 'has_many',
				"model" => 'Car\Request'
			),
			"seats"     => array(
				"type" => 'int',
				"is_unsigned" => true,
				"min" => 1,
			),

			"used" => array(
				"type" => 'int',
				"is_unsigned" => true,
				"default" => 0,
			),

			"visible"    => array('type' => 'bool'),
			"sent_notif" => array('type' => 'bool'),
		);


		protected static $access = array(
			'schema' => true,
			'browse' => true,
		);


		public function to_object_with_perms(\System\User $user)
		{
			$data = parent::to_object_with_perms($user);

			if (!$user) {
				unset($data['phone']);
				unset($data['email']);
				unset($data['ident']);
			}

			return $data;
		}


		public function save()
		{
			parent::save();

			if (!$this->ident) {
				$this->ident = md5($this->id.'-'.time());
				$this->save();
			}

			return $this;
		}


		public function update_status()
		{
			$this->used = $this->requests->add_filter(array(
				'attr'  => 'status',
				'type'  => 'exact',
				'exact' => \Car\Request::STATUS_APPROVED
			))->count();

			return $this->save();
		}


		public function is_full()
		{
			$current = $this->requests->where(array('status' => \Car\Request::STATUS_APPROVED))->count();
			return $current >= $this->seats;
		}


		public function send_notif(\System\Http\Response $res)
		{
			$ren = \System\Template\Renderer\Txt::from_response($res);
			$ren->reset_layout();
			$ren->partial('mail/car/offer', array(
				"admin" => $res->url_full('carshare.admin', array($this->ident))
			));

			$mail = new \Helper\Offcom\Mail(array(
				'rcpt'     => array($this->email),
				'subject'  => 'Improtřesk 2016 - Sdílení auta',
				'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
				'message'  => $ren->render_content()
			));

			$mail->send();

			$this->sent_notif = true;
			$this->save();
		}
	}
}

<?php

namespace Helper\Cli\Module
{
	class Notif extends \Helper\Cli\Module
	{
		protected static $info = array(
			'name' => 'bank',
			'head' => array(
				'Manage your account transactions',
			),
		);


		protected static $attrs = array(
			"help"    => array("type" => 'bool', "value" => false, "short" => 'h', "desc"  => 'Show this help'),
			"verbose" => array("type" => 'bool', "value" => false, "short" => 'v', "desc" => 'Be verbose'),
		);


		protected static $commands = array(
			"confirm" => array('Send confirmation to unconfirmed'),
			"general" => array('Send notification containing general information'),
			"lunch"   => array('Send notification about lunch picker'),
			"match"   => array('Send notification about match survey'),
			"camp"    => array('Send notification about summer impro camp 2016'),
			"workshopAdditional" => array('Send notification about additional workshop to 2016'),
			"hotel" => array('Send notification about hotel accomodation'),
			"hotelUpdate" => array('Send update notification about hotel accomodation'),
		);


		public function cmd_confirm()
		{
			\System\Init::full();

			$signups = \Workshop\SignUp::get_all()
				->where(array(
					"sent_confirm" => false,
					"solved" => false,
				))
				->fetch();

			\Helper\Cli::do_over($signups, function($key, $signup) {
				$res = new \System\Http\Response();
				$signup->mail_confirm($res);
			});
		}


		public function cmd_lunch()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"sent_lunch" => false,
					"solved" => true,
					"lunch" => true
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/notif/lunch', array(
					"user"   => $user,
					"symvar" => $user->check->symvar
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - Výběr oběda',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_lunch = true;
				$user->save();
			});
		}


		public function cmd_match()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"sent_match" => false,
					"solved" => true
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/notif/match', array(
					"user"   => $user,
					"symvar" => $user->check->symvar
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - Týmy na zápas',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_match = true;
				$user->save();
			});
		}


		public function cmd_general()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"sent_general" => false,
					"solved" => true
				))
				->add_filter(array(
					"attr"    => 'id_assigned_to',
					"type"    => 'is_null',
					"is_null" => false
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/notif/general', array(
					"user"   => $user,
					"symvar" => $user->check->symvar
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - S sebou',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_general = true;
				$user->save();
			});
		}


		public function cmd_camp()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"sent_camp" => false,
					"solved" => true
				))
				->add_filter(array(
					"attr"    => 'id_assigned_to',
					"type"    => 'is_null',
					"is_null" => false
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/after/improcamp', array(
					"user"   => $user,
					"symvar" => $user->check->symvar
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - Pozvánka na letní IMPRO CAMP 2016 se slevou',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_camp = true;
				$user->save();
			});
		}


		public function cmd_workshopAdditional()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"sent_workshopAdditional" => false,
					"solved" => true,
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/signup/new-workshop', array(
					"user" => $user,
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - Otevřeli jsme nový workshop',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_workshopAdditional = true;
				$user->save();
			});
		}


		public function cmd_hotel()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"sent_hotel" => false,
					"hotel" => true,
					"solved" => true,
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/notif/hotel', array(
					"user" => $user,
					"symvar" => $user->check->symvar,
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - Ubytování v hotelu',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_hotel = true;
				$user->save();
			});
		}


		public function cmd_hotelUpdate()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"sent_hotel_update" => false,
					"sent_hotel" => true,
					"hotel" => true,
					"solved" => true,
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/notif/hotel_update', array(
					"user" => $user,
					"symvar" => $user->check->symvar,
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - Ubytování v hotelu, aktualizace',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_hotel_update = true;
				$user->save();
			});
		}

		public function cmd_lostAndFound()
		{
			\System\Init::full();

			$users = \Workshop\SignUp::get_all()
				->where(array(
					"solved" => true,
					"newsletter" => true,
					"sentLostAndFound" => false,
				))
				->fetch();

			\Helper\Cli::do_over($users, function($key, $user) {
				$ren = new \System\Template\Renderer\Txt();
				$ren->reset_layout();
				$ren->partial('mail/notif/lost-and-found', array(
					"user" => $user,
					"symvar" => $user->check->symvar,
				));

				$mail = new \Helper\Offcom\Mail(array(
					'rcpt'     => array($user->email),
					'subject'  => 'Improtřesk 2016 - Ztráty a nálezy, pozvánka',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content(),
				));

				$mail->send();

				$user->sentLostAndFound = true;
				$user->save();
			});
		}
	}
}


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
			"camp"    => array('Send notification about summer impro camp 2015'),
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
					'subject'  => 'Improtřesk 2015 - Výběr oběda',
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
					'subject'  => 'Improtřesk 2015 - Týmy na zápas',
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
					'subject'  => 'Improtřesk 2015 - To nejdůležitější',
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
					'subject'  => 'Improtřesk 2015 - Pozvánka na letní IMPRO CAMP 2015 se slevou',
					'reply_to' => \System\Settings::get('offcom', 'default', 'reply_to'),
					'message'  => $ren->render_content()
				));

				$mail->send();

				$user->sent_camp = true;
				$user->save();
			});
		}
	}
}


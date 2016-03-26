<?php

namespace Helper\Cli\Module
{
	class Bank extends \Helper\Cli\Module
	{
		const URL_TRANSACTIONS = 'https://www.fio.cz/ib_api/rest/periods/{token}/{from}/{to}/transactions.json';

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
			"pair" => array('Pair transactions'),
		);


		public function cmd_pair()
		{
			\System\Init::full();

			$token = \System\Settings::get('bank', 'token');
			$from  = \System\Settings::get('bank', 'from');
			$to    = date('Y-m-d');

			$url = str_replace('{token}', $token, self::URL_TRANSACTIONS);
			$url = str_replace('{from}', $from, $url);
			$url = str_replace('{to}', $to, $url);

			$res = \Helper\Offcom\Request::get($url);

			if (!$res->ok()) {
				if ($res->status == 409) {
					\Helper\Cli::out('Please wait 30 seconds and try again.');
					exit(4);
				} else {
					\Helper\Cli::out('Unknown error during transaction scrape.');
					exit(5);
				}
			}

			$feed = \System\Json::decode($res->content);
			\Workshop\Payment::pair_with_feed($feed);
		}
	}
}


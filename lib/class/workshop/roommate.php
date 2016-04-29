<?php

namespace Workshop
{
	class Roommate extends \System\Model\Perm
	{
		protected static $attrs = array(
			'signup' => array(
				"type"    => 'belongs_to',
				"model"   => 'Workshop\SignUp',
				"rel"     => 'roommate',
			),

			'roommate' => array(
				"type"    => 'belongs_to',
				"model"   => 'Workshop\SignUp',
			),
		);

		protected static $access = array(
			"browse" => true,
			"schema" => true,
			"create" => true,
		);
	}
}

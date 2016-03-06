<?php

namespace
{
	class Workshop extends \System\Model\Perm
	{
		const SEATS_OPENED = 12;

		protected static $attrs = array(
			'name'        => array("type" => 'varchar'),
			'lector'      => array("type" => 'varchar'),
			'desc'        => array("type" => 'html', "length" => 65535),
			'desc_lector' => array("type" => 'html', "default" => '', "length" => 65535),
			'difficulty'  => array("type" => 'varchar', "default" => ''),
			'visible'     => array("type" => 'bool', "default" => true),
			'opened'   => array("type" => 'int', "default" => self::SEATS_OPENED),

			'assignees' => array(
				"bound_to" => 'assigned_to',
				"type"     => 'has_many',
				"model"    => 'Workshop\SignUp'
			),

			'signups'  => array(
				"type" => 'has_many',
				"model" => 'Workshop\SignUp',
				"is_bilinear" => true,
				"is_master" => false
			),

			'photos'  => array(
				"type" => 'has_many',
				"model" => 'Workshop\Photo',
			),
		);


		protected static $access = array(
			"browse" => true,
			"schema" => true,
		);


		public function to_object_with_perms(\System\User $user)
		{
			$data = parent::to_object_with_perms($user);
			$data['free'] = $this->opened - $this->assignees->count();

			return $data;
		}
	}
}

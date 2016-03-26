<?php

namespace Module\Stats
{
	class NonPayers extends \System\Module
	{
		public function run()
		{
			$items = \Workshop\SignUp::get_all()->where(array(
				'paid' => false,
				'solved' => false
			))->fetch();

			$this->partial('stats/non-payers', array(
				"items" => $items,
			));
		}
	}
}

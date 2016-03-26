<?php

namespace Module\Workshop
{
	class Browse extends \System\Module
	{
		public function run()
		{
			$items = \Workshop::get_all()->fetch();

			for ($i = count($items); $i<8; $i++) {
				$items[] = new \Workshop(array(
					"name" => "Připravujeme",
					"desc" => 'Workshop '.($i + 1),
				));
			}

			$this->partial('pages/workshops', array(
				"items" => $items,
			));
		}
	}
}

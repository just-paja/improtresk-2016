<?php

namespace Module\Stats
{
	class Delivery extends \System\Module
	{
		const DATE = '2016-05-14';

		public function run()
		{
			$signups = \Workshop\SignUp::get_all()
				->where(array(
					"lunch" => true,
					"canceled" => false,
				))
				->add_filter(array(
					"attr" => "id_assigned_to",
					"type" => "exact",
					"exact" => [3,5,9,10],
				))
				->fetch();

			$stats = array();
			$default = [1,2];
			$foods = \Food\Item::get_all()->where(array('date' => $this::DATE))->fetch();
			$canSend = array();

			foreach ($foods as $food) {
				$canSend[$food->id] = $food;
				$stats[$food->id] = 0;
			}

			foreach ($signups as $person) {
				$food = $person->food->where(array('date' => $this::DATE))->fetch();

				if (count($food)) {
					foreach ($food as $item) {
						$stats[$item->id] += 1;
					}
				} else {
					foreach ($default as $item) {
						$stats[$item] += 1;
					}
				}
			}

			$this->partial('stats/delivery', array(
				"stats" => $stats,
				"foods" => $canSend,
			));

		}
	}
}

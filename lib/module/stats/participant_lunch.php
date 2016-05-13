<?php

namespace Module\Stats
{
	class ParticipantLunch extends \System\Module
	{
		public function run()
		{
			$ppl = \Workshop\SignUp::get_all(array("solved" => true, "lunch" => true, 'canceled' => false))
				->add_filter(array(
					"attr"    => 'id_assigned_to',
					"type"    => 'is_null',
					"is_null" => false,
				))
				->sort_by('name_first, name_last')
				->fetch();

			$defaults = array(
				'2016-05-14' => array(\Food\Item::find(1), \Food\Item::find(2)),
				'2016-05-15' => array(\Food\Item::find(5), \Food\Item::find(7)),
			);

			$participants = array();
			$focus = array('2016-05-14', '2016-05-15');

			foreach ($ppl as $person) {
				$days = array();

				foreach ($focus as $date_str) {
					$date = new \DateTime($date_str);
					$days[$date_str] = array(
						"date"  => $date->format('d. m. Y'),
						"items" => array(),
						'food' => $defaults[$date->format('Y-m-d')],
					);

					$food = $person->food->where(array("date" => $date_str))->fetch();

					if (any($food)) {
						$days[$date_str]['food'] = $food;
					}
				}

				$participants[] = array(
					"days" => $days,
					"name" => $person->toName(),
				);
			}

			$this->partial('stats/participant-lunch', array(
				"participants" => $participants,
			));
		}
	}
}

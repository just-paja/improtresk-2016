<?

namespace Module\Stats
{
	class Meals extends \System\Module
	{
		public function run()
		{
			$food = \Food\Item::get_all(array("edible" => true))->sort_by('type, blank desc')->fetch();
			$days = array();

			foreach ($food as $item) {
				$date = $item->date->format('Y-m-d');

				if (!array_key_exists($date, $days)) {
					$days[$date] = array(
						"date"  => $item->date,
						"items" => array(),
					);
				}

				$item->type_label = $item->type == 1 ? 'Polévka':'Hlavní';
				$item->total = $item->eaters->where(array(
					"solved" => true
				))->count();

				if ($item->blank) {
					$today_picked = \Workshop\SignUp::get_all()
						->where(array(
							'solved' => true
						))
						->join('workshop_signup_has_food_item', 'USING(id_workshop_signup)', 'wshfi', 'left')
						->join('food_item', 'USING(id_food_item)', 'fi', 'left')
						->where(array(
							'type' => $item->type,
							'date' => $item->date->format('Y-m-d H:i:s')
						), 'fi')
						->count();

					$signups_total = \Workshop\SignUp::get_all()->where(array('solved' => true, 'lunch' => true))->count();
					$unpicked = $signups_total - $today_picked;

					$item->total += $unpicked;
				}

				$days[$date]['items'][] = $item;
			}

			$this->partial('stats/meals', array(
				"days" => $days,
			));
		}
	}
}

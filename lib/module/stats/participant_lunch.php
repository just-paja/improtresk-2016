<?

namespace Module\Stats
{
	class ParticipantLunch extends \System\Module
	{
		public function run()
		{
			$ppl = \Workshop\SignUp::get_all(array("solved" => true))
				->add_filter(array(
					"attr"    => 'id_assigned_to',
					"type"    => 'is_null',
					"is_null" => false,
				))
				->sort_by('name_first, name_last')
				->fetch();

			$days = array();
			$focus = array('2015-05-08', '2015-05-09');

			foreach ($focus as $date_str) {
				$date   = new \DateTime($date_str);
				$days[$date_str] = array(
					"date"  => $date->format('d. m. Y'),
					"items" => array()
				);

				foreach ($ppl as $person) {
					$food = $person->food->where(array(
						"date" => $date_str,
						"type" => 2
					))
					->fetch_one();

					if (any($food)) {
						$days[$date_str]['items'][] = array(
							"person" => $person,
							"food"   => $food
						);
					}
				}
			}

			$this->partial('stats/participant-lunch', array(
				"days" => $days,
			));
		}
	}
}

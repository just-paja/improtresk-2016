<?

namespace Module\Food
{
	class Picker extends \System\Module
	{
		const EDITABLE_UNTIL = '2016-05-02 00:00:00';


		public function run()
		{
			$end = \DateTime::createFromFormat('Y-m-d H:i:s', static::EDITABLE_UNTIL);
			$now = new \DateTime();

			if ($now > $end) {
				return $this->run_list();
			}

			$this->run_form();
		}


		public function run_list()
		{
			$check = \Workshop\Check::get_first(array(
				"symvar" => $this->symvar
			))->fetch();

			if (!$check) {
				throw new \System\Error\NotFound();
			}

			$signup = $check->signup;

			if (!$signup) {
				throw new \System\Error\NotFound();
			}

			$picked = $signup->food->fetch();
			$days = array();

			foreach ($picked as $item) {
				$date = $item->date->format('Y-m-d');
				$type = $item->type == 1 ? 'soup':'main';

				if (!array_key_exists($date, $days)) {
					$days[$date] = array(
						"date" => $item->date,
						"form" => $item->date->format('d. m.'),
						"soup" => array(),
						"main" => array(),
					);
				}

				$days[$date][$type][] = array(
					"name" => $item->name,
					"value" => $item->id
				);
			}

			$this->partial('pages/food-user', array(
				"days" => $days
			));
		}


		public function run_form()
		{
			$end = \DateTime::createFromFormat('Y-m-d H:i:s', static::EDITABLE_UNTIL);
			$this->req('symvar');

			$check = \Workshop\Check::get_first(array(
				"symvar" => $this->symvar
			))->fetch();

			if (!$check) {
				throw new \System\Error\NotFound();
			}

			$signup = $check->signup;

			if (!$signup) {
				throw new \System\Error\NotFound();
			}

			$picked = $signup->food->fetch();
			$food = \Food\Item::get_all(array("edible" => true))->fetch();
			$days = array();
			$data = array(
				'soup_2016-05-14' => 1,
				'main_2016-05-14' => 666,
				'soup_2016-05-15' => 5,
				'main_2016-05-15' => 666,
			);

			foreach ($picked as $item) {
				$date = $item->date->format('Y-m-d');
				$type = $item->type == 1 ? 'soup':'main';
				$name = $type.'_'.$date;

				$data[$name] = $item->id;
			}

			foreach ($food as $item) {
				$date = $item->date->format('Y-m-d');
				$type = $item->type == 1 ? 'soup':'main';

				if (!array_key_exists($date, $days)) {
					$days[$date] = array(
						"date" => $item->date,
						"soup" => array(),
						"main" => array(
							array("name" => 'Překvapte mě', "value" => 666),
						),
					);
				}

				$days[$date][$type][] = array(
					"name" => $item->name,
					"value" => $item->id
				);
			}

			$f = $this->response->form(array(
				"desc"    => 'Předvol si obědy na Improtřesk 2015. Tato možnost končí '.$end->format('j. n. v H:i').'. Můžeš si zvolit "překvapte mě" a my jídlo vybereme za tebe.',
				"id"      => 'food-picker',
				"default" => $data,
			));

			foreach ($days as $date=>$day) {
				$f->input(array(
					"type"  => 'radio',
					"label" => 'Polévka '.$day['date']->format('j.n.'),
					"name"  => 'soup_'.$date,
					"options" => $day['soup'],
					"required" => true,
				));

				$f->input(array(
					"type"  => 'radio',
					"label" => 'Hlavní chod '.$day['date']->format('j.n.'),
					"name"  => 'main_'.$date,
					"options" => $day['main'],
					"required" => true,
				));
			}

			$f->submit('Uložit');
			$f->out($this);

			if ($f->submited()) {
				if ($f->passed()) {
					$data = $f->get_data();
					$use  = array();

					foreach ($data as $key=>$val) {
						if ($val != 666 && (strpos($key, 'soup') === 0 || strpos($key, 'main') === 0)) {
							$use[] = $val;
						}
					}

					$signup->food = $use;
					$signup->save();

					$this->partial('forms/message', array(
						"msg" => 'Uloženo'
					));
				} else {
					$this->partial('forms/message', array(
						"msg" => 'Neplatný vstup, zkus to prosím znova'
					));
				}
			}

		}
	}
}

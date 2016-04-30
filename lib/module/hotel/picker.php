<?

namespace Module\Hotel
{
	class Picker extends \System\Module
	{
		const EDITABLE_UNTIL = '2016-05-04 00:00:00';


		public function run()
		{
			$end = \DateTime::createFromFormat('Y-m-d H:i:s', static::EDITABLE_UNTIL);
			$now = new \DateTime();

			if ($now > $end) {
				return $this->run_list();
			}

			$this->run_form();
		}

		public function getSignup()
		{
			$this->req('symvar');
			$check = \Workshop\Check::get_first(array("symvar" => $this->symvar))->fetch();

			if (!$check) {
				throw new \System\Error\NotFound();
			}

			$signup = $check->signup;

			if (!$signup || !$signup->hotel) {
				throw new \System\Error\NotFound();
			}

			return $signup;
		}


		public function run_list()
		{
			$this->partial('pages/hotel');
		}

		public function run_form()
		{
			$end = \DateTime::createFromFormat('Y-m-d H:i:s', static::EDITABLE_UNTIL);
			$signup = $this->getSignup();
			$roommates = $signup->room->fetch();

			$defData = $signup->get_data();
			$defData['roommates'] = array();

			foreach ($roommates as $r) {
				$defData['roommates'][] = $r->roommate->to_object_with_perms($this->request->user);
			}

			$f = $this->response->form(array(
				"desc"    => 'Protože jsi v přihlášce zaškrtnul hotel, potřebujeme od tebe dodatečné údaje.',
				"id"      => 'hotel-form',
				"default" => $defData,
			));

			$f->input(array(
				"type"  => 'text',
				"label" => 'Jméno',
				"name"  => 'name_first',
				"required" => true,
			));

			$f->input(array(
				"type"  => 'text',
				"label" => 'Příjmení',
				"name"  => 'name_last',
				"required" => true,
			));

			$f->input(array(
				"type"  => 'text',
				"label" => 'Adresa trvalého bydliště',
				"name"  => 'address',
				"required" => true,
			));

			$f->input(array(
				"type"  => 'text',
				"label" => 'Datum narození',
				"name"  => 'birthday',
				"required" => true,
			));

			$f->input(array(
				"type"  => 'text',
				"label" => 'Číslo občanského průkazu',
				"name"  => 'id_number',
				"required" => true,
			));

			$f->input(array(
				"type"  => 'collection',
				"filter" => array(
					array('attr' => 'solved', 'type' => 'exact', 'exact' => true),
					array('attr' => 'hotel', 'type' => 'exact', 'exact' => true),
				),
				"multiple" => 4,
				"model" => 'Workshop.SignUp',
				"label" => 'Spolubydlící (nepovinné)',
				"name"  => 'roommates',
				"placeholder" => 'Začni psát jméno',
				"desc" => 'Vyber si až čtyři parťáky které bys chtěl na pokoji. Pomocí této tajné seznamky zjistíme jak všechny účastníky ubytovat. Ve vyhledávání naleznete pouze jména účastníků se zaplacenenou přihlášky v hotelu.',
			));

			$f->submit('Uložit');
			$f->out($this);

			if ($f->submited()) {
				if ($f->passed()) {
					$data = $f->get_data();

					if ($data['roommates']) {
						$data['roommates'] = json_decode($data['roommates']);
					}


					foreach ($roommates as $rm) {
						$rm->drop();
					}

					foreach ($data['roommates'] as $rm) {
						\Workshop\Roommate::create(array(
							"signup" => $signup->id,
							"roommate" => $rm,
						));
					}

					$signup->update_attrs($data)->save();
				}
			}

		}
	}
}

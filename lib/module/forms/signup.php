<?php

namespace Module\Forms
{
	class Signup extends \System\Module
	{
		public function get_form()
		{
			$opts = array();
			$f = $this->response->form();
			$ws = \Workshop::get_all();

			foreach ($ws as $w) {
				$opts[$w->id] = $w->name;
			}

			$f->input(array(
				'name' => 'name_first',
				'type' => 'text',
				'required' => true
			));

			$f->input(array(
				'name' => 'name_last',
				'type' => 'text',
				'required' => true
			));

			$f->input(array(
				'name' => 'team',
				'type' => 'text',
				'required' => false
			));

			$f->input(array(
				'name' => 'email',
				'type' => 'email',
				'required' => true
			));

			$f->input(array(
				'name' => 'phone',
				'type' => 'text',
				'required' => true
			));

			$f->input(array(
				'name' => 'birthday',
				'type' => 'text',
				'required' => true
			));

			$f->input(array(
				'name' => 'lunch',
				'type' => 'checkbox',
				'required' => false
			));

			$f->input(array(
				'name' => 'hotel',
				'type' => 'checkbox',
				'required' => false
			));

			$f->input(array(
				'name' => 'workshop_0',
				'type' => 'select',
				'options' => $opts,
				'required' => true
			));

			$f->input(array(
				'name' => 'workshop_1',
				'type' => 'select',
				'options' => $opts,
			));

			$f->input(array(
				'name' => 'workshop_2',
				'type' => 'select',
				'options' => $opts,
			));

			$f->input(array(
				'name' => 'rules',
				'type' => 'checkbox',
				'required' => true
			));

			$f->input(array(
				'name' => 'newsletter',
				'type' => 'checkbox',
				'required' => false
			));
			return $f;
		}


		public function run()
		{
			$start = new \DateTime("2016-04-01 09:00:00+01:00");
			$end   = new \DateTime("2016-05-12 00:00:00+01:00");
			$now   = new \DateTime();

			$started = $now > $start;
			$ended   = $now > $end;

			if ($this->request->method == 'post') {
				$f = $this->get_form();

				if ($f->passed()) {
					$d = $f->get_data();

					$item = new \Workshop\SignUp($d);
					$ws_list = array_filter(array(
						$d['workshop_0'],
						$d['workshop_1'],
						$d['workshop_2'],
					));

					$item->workshops = $ws_list;

					$item->save();
					$item->mail_confirm($this->response);

					return $this->json_response(200, 'saved');

				} else {
					return $this->json_response(400, 'fill-all-fields', array(
						'form' => $f->get_data(),
						'errors' => $f->get_errors()
					));
				}
			}

			$this->partial('forms/signup', array(
				"start"   => $start,
				"start_f" => $start->format('j. n. \v G:i'),
				"end"     => $end,
				"started" => $started,
				"ended"   => $ended,
				"show"    => $started && !$ended,
			));
		}
	}
}

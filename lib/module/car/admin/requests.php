<?php

namespace Module\Car\Admin
{
	class Requests extends \System\Module
	{
		public function run()
		{
			$offer = $this->req('offer');
			$rqs_new = $offer->requests->where(array('status' => 1))->fetch();

			if (count($rqs_new) > 0) {
				$fn = \System\Form::from_module($this, array(
					'id' => 'new-requests',
					'prefix'  => 'n_',
					'heading' => 'Nové žádosti uživatelů',
					'desc'    => 'Vyber si, koho chceš svézt. Změny budou okamžitě propsány a uživatelům bude poslán informativní e-mail s kontaktem na Tebe. Pokud si nejsi jistý, zavolej a nebo napiš.',
				));

				foreach ($rqs_new as $item) {
					$fn->input(array(
						'type'     => 'select',
						'name'     => 'status_'.$item->id,
						'label'    => $item->name,
						'desc'     => 'Telefon: '.$item->phone.', E-mail: '.$item->email,
						'required' => true,
						'options'  => array(
							array('name' => 'Potvrdit', 'value' => 2),
							array('name' => 'Zamítnout', 'value' => 4),
						)
					));
				}

				$fn->submit('Uložit');

				if ($fn->passed()) {
					$data = $fn->get_data();

					foreach ($rqs_new as $rq) {
						$name = 'status_'.$rq->id;
						$stat = $rq->status;

						if (isset($data[$name])) {
							$stat = $data[$name];
						}

						$rq->status = $stat;
						$rq->save();
						$rq->send_notif_author($this->response);
					}

					$offer->update_status();
					$this->response->redirect($this->request->path);
				} else {
					$fn->out($this);
				}
			}

			$rqs_cur = $offer->requests->add_filter(array(
				'attr'  => 'status',
				'type'  => 'exact',
				'exact' => \Car\Request::STATUS_APPROVED
			))->fetch();

			if (count($rqs_cur) > 0) {
				$fc = \System\Form::from_module($this, array(
					'id'      => 'approved-requests',
					'prefix'  => 'c_',
					'heading' => 'Potvrzené žádosti uživatelů',
				));

				foreach ($rqs_cur as $item) {
					$fc->input(array(
						'type'     => 'select',
						'name'     => 'status_'.$item->id,
						'label'    => $item->name,
						'desc'     => 'Telefon: '.$item->phone.', E-mail: '.$item->email,
						'options'  => array(
							array('name' => 'Zrušit', 'value' => 3),
						)
					));
				}

				$fc->submit('Uložit');

				if ($fc->passed()) {
					$data = $fc->get_data();

					foreach ($rqs_cur as $rq) {
						$name = 'status_'.$rq->id;
						$stat = $rq->status;

						if (isset($data[$name]) && $data[$name]) {
							$stat = $data[$name];
						}

						$rq->status = $stat;
						$rq->save();
						$rq->send_notif_author($this->response);
					}

					$offer->update_status();
					$this->response->redirect($this->request->path);
				} else {
					$fc->out($this);
				}
			}
		}
	}
}


<?php


namespace Module\Car
{
	class Share extends \System\Module
	{
		public function run()
		{
			$res = $this->response;
			$status = 400;
			$message = 'fill-all-fields';
			$data = null;

			$icons = \Car\Offer::get_attr_options('icon');
			$opts = array();

			foreach ($icons as $icon) {
				$opts[] = array('value' => $icon, 'name' => $icon);
			}

			$ident = $this->ident;
			def($ident, null);

			$f = $res->form(array(
				'use_comm' => true,
			));

			$f->input(array(
				'type' => 'text',
				'name' => 'driver',
				'required' => true
			));

			$f->input(array(
				'type' => 'text',
				'name' => 'from',
				'required' => true
			));

			$f->input(array(
				'type' => 'number',
				'name' => 'seats',
				'min'  => 1,
				'required' => true
			));

			$f->input(array(
				'type' => 'datetime',
				'name' => 'departure',
				'required' => true
			));

			$f->input(array(
				'type' => 'select',
				'name' => 'icon',
				'required' => true,
				'options' => $opts
			));

			$f->input(array(
				'name' => 'phone',
				'type' => 'phone',
				'required' => true
			));

			$f->input(array(
				'type' => 'email',
				'name' => 'email',
				'required' => true
			));

			$f->input(array(
				'type' => 'textarea',
				'name' => 'desc',
				'required' => false
			));


			if ($f->submited()) {
				if ($f->passed()) {
					$attrs = $f->get_data();
					$status = 200;
					$message = 'saved';

					if ($ident) {
						$item = \Car\Offer::get_first()->where(array('ident' => $ident))->fetch();

						if (!$item) {
							throw new \System\Error\NotFound();
						}
					} else {
						$item = new \Car\Offer();
					}

					$item->update_attrs($attrs);
					$item->visible = true;
					$item->save();

					if (!$item->sent_notif) {
						$item->send_notif($res);
					}

					$data = $item->get_data();
				} else {
					$data = $f->get_errors();
				}

				$res->mime = 'text/html';

				if (!isset($ident) || $f->submited()) {
					$this->json_response($status, $message, $data);
				}
			}
		}
	}
}

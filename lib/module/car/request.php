<?php

namespace Module\Car
{
	class Request extends \System\Module
	{
		public function run()
		{
			$res = $this->response;
			$id  = $this->req('id');
			$offer = \Car\Offer::find($id);

			if ($offer) {
				$status = 400;
				$message = 'fill-all-fields';
				$data = null;

				$f = $res->form(array(
					'use_comm' => true,
				));


				$f->input(array(
					'type' => 'text',
					'name' => 'name',
					'required' => true
				));

				$f->input(array(
					'type' => 'text',
					'name' => 'phone',
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
						$attrs['car'] = $id;
						$status = 200;
						$message = 'saved';

						$item = new \Car\Request($attrs);
						$item->save();

						$item->send_notif($res);
						$data = $item->get_data();
					} else {
						$data = $f->get_errors();
					}
				}


				$res->mime = 'text/html';
				$this->json_response($status, $message, $data);

			} else throw new \System\Error\NotFound();
		}
	}
}

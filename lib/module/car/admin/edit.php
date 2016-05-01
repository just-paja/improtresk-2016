<?php

namespace Module\Car\Admin
{
	class Edit extends \System\Module
	{
		public function run()
		{
			$this->req('ident');

			$offer = \Car\Offer::get_first()
				->where(array(
					'ident'   => $this->ident,
					'visible' => true
				))
				->fetch();

			if ($offer) {
				$cfg = $this->request->fconfig;

				$cfg['ui']['data'] = array(
					array(
						'model' => 'Car.Offer',
						'items' => array(
							$offer->to_object_with_perms($this->request->user)
						)
					)
				);

				$this->request->fconfig = $cfg;
				$this->response->subtitle = 'úpravy nabídky na sdílení auta';

				$this->partial('car/admin/form', array(
					"item"     => $offer,
					"requests" => $offer->requests->fetch()
				));
			} else throw new \System\Error\NotFound();
		}
	}
}

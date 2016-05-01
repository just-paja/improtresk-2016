<?php

namespace Module\Car\Admin
{
	class Detail extends \System\Module
	{
		public function run()
		{
			$rq = $this->request;
			$res = $this->response;
			$ident = $this->req('ident');

			$offer = \Car\Offer::get_first()
				->where(array(
					'ident'   => $ident,
					'visible' => true
				))
				->fetch();

			if ($offer) {
				$cfg = $rq->fconfig;

				$cfg['ui']['data'] = array(
					array(
						'model' => 'Car.Offer',
						'items' => array(
							$offer->to_object_with_perms($rq->user)
						)
					)
				);

				$rq->fconfig = $cfg;
				$res->subtitle = 'úpravy nabídky na sdílení auta';
				$this->propagate('offer', $offer);
				$this->propagate('id', $offer->id);

				$this->partial('car/admin/links', array(
					"ident" => $ident,
          "slot" => 'detail',
				));

			} else throw new \System\Error\NotFound();
		}
	}
}

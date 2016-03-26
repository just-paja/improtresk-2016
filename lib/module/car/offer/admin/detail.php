<?php

namespace Module\Car\Offer\Admin
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

				$this->partial('pages/carshare-detail', array(
					"item"      => $offer,
					"free"      => $offer->seats - $offer->requests->where(array('status' => 2))->count(),
					"show_form" => false,
					"show_rq"   => true,
					"requests"  => $offer->requests->add_filter(array(
						'attr'  => 'status',
						'type'  => 'exact',
						'exact' => array(1,2)
					))->fetch(),
				));

				$this->partial('pages/carshare-admin-links', array(
					"ident" => $ident
				));

			} else throw new \System\Error\NotFound();
		}
	}
}

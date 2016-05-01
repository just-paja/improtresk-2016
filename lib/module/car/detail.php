<?php

namespace Module\Car
{
  class Detail extends \System\Module
  {
    public function run()
    {
      $data = $this->dbus->get_data('admin');
      $offer = null;

      if (isset($data['offer'])) {
        $offer = $data['offer'];
      }

      if (!$offer) {
        $id = $this->req('id');

        $offer = \Car\Offer::get_first()
          ->where(array(
            'id_car_offer' => $id,
            'visible' => true,
          ))
          ->fetch();
      }

      if ($offer) {
        $this->response->subtitle = $offer->driver.' vás zve na cestu na Improtřesk 2016';

        $this->partial('car/offer-detail', array(
          "item"      => $offer,
          "free"      => $offer->seats - $offer->requests->where(array('status' => 2))->count(),
          "show_form" => !!$this->show_form,
          "show_rq"   => true,
          "requests"  => $offer->requests->where(array('status' => array(1,2)))->fetch()
        ));
      } else throw new \System\Error\NotFound();
    }
  }
}

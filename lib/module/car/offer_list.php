<?php

namespace Module\Car
{
  class OfferList extends \System\Module
  {
    public function run()
    {
      $all = \Car\Offer::get_all()
        ->add_filter(array(
          'attr' => 'seats',
          'type' => 'gt',
          'gt' => 'used',
          'self' => true,
        ))
        ->add_filter(array(
          'attr' => 'seats',
          'type' => 'gt',
          'gt' => 0
        ))
        ->sort_by('departure')
        ->fetch();

      $this->partial('car/offer-list', array(
        'list' => $all
      ));
    }
  }
}

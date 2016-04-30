<?php

namespace Module\Stats
{
  class Hotel extends \System\Module
  {
    public function run()
    {
      $total = \Workshop\Check::get_all()
          ->add_cols(array('SUM(amount) as `total`'))
          ->where(array(
            "hotel" => true,
            "solved" => true,
          ))
          ->assoc_with(null)
          ->fetch_one();

      $totalChecked = \Workshop\Roommate::count();
      $singles = array();
      $matched = array();
      $used = array();
      $rooms = array();
      $skipped = array();
      $checked = \Workshop\Roommate::get_all()->fetch();

      foreach ($checked as $room) {
        $roommates = \Workshop\Roommate::get_all()->where(array(
          "roommate" => $room->signup,
          "signup" => $room->roommate,
        ))->fetch();

        if (!$roommates) {
          $singles[] = $room->signup;
        } else {
          if (!$matched[$room->signup->id]) {
            $matched[$room->signup->id] = array($room->signup->id);
          }

          array_push($matched[$room->signup->id], $room->roommate->id);
          $matched[$room->signup->id] = sort($matched[$room->signup->id]);
        }
      }

      foreach ($matched as $room) {
        $checked = 0;

        if ($room) {
          foreach ($matched as $index => $check) {
            if ($room == $check) {
              $checked ++;
            }

            if ($checked == count($room)) {
              unset($matched[$index]);
              break;
            }
          }

          if ($checked == count($room)) {
            $rooms[] = $room;
          } else {
            $skipped[] = $room;
          }
        }
      }

      v($singles);
      v($rooms);
      v($skipped);
      exit;

      $stats = array(
        "total" => $total,
        "totalChecked" => $totalChecked,
        "checked"   => $checked,
      );

      $this->partial('stats/signups', array(
        "stats" => $stats,
        "workshops" => $ws,
      ));
    }
  }
}

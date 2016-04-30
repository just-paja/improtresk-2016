<?php

namespace Module\Stats
{
  class Hotel extends \System\Module
  {
    public function run()
    {
      $total = \Workshop\SignUp::count_all(array(
        "hotel" => true,
        "solved" => true,
      ));

      $totalChecked = \Workshop\Roommate::count_all();
      $matched = array();
      $used = array();
      $rooms = array();
      $checked = \Workshop\Roommate::get_all()->fetch();

      foreach ($checked as $room) {
        $roommates = \Workshop\Roommate::get_all()->where(array(
          "id_roommate" => $room->signup->id,
          "id_signup" => $room->roommate->id,
        ))->fetch();

        if (!$roommates) {
          $matched[$room->signup->id] = array($room->signup->id);
        } else {
          if (!isset($matched[$room->signup->id])) {
            $matched[$room->signup->id] = array($room->signup->id);
          }

          array_push($matched[$room->signup->id], $room->roommate->id);
          sort($matched[$room->signup->id]);
          $matched[$room->signup->id] = array_unique($matched[$room->signup->id]);
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
          }
        }
      }

      $allMatched = array();

      foreach ($rooms as $i => $room) {
        foreach ($room as $index => $id) {
          $allMatched[] = $id;
          $room[$index] = \Workshop\SignUp::find($id);
        }

        $rooms[$i] = $room;
      }

      $unMatched = \Workshop\SignUp::get_all()
        ->where(array(
          ' `id_workshop_signup` NOT IN (' . implode(',', $allMatched) . ')',
          "hotel" => true,
          "solved" => true,
        ))
        ->fetch();

      $this->partial('stats/hotel', array(
        "total" => $total,
        "totalChecked" => $totalChecked,
        "rooms" => $rooms,
        "unMatched" => $unMatched,
      ));
    }
  }
}

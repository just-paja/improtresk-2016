<?php

namespace Module\Stats
{
  class Hotel extends \System\Module
  {
    const ROOM_MAX = 4;
    const ROOM_MIN = 1;

    public $roomNumbers = array(
      array( 'label' => '22 A', 'max' => 4 ),
      array( 'label' => '22 B', 'max' => 2 ),
      array( 'label' => '23 A', 'max' => 2 ),
      array( 'label' => '23 B', 'max' => 4 ),
      array( 'label' => '24 A', 'max' => 2 ),
      array( 'label' => '24 B', 'max' => 3 ),
      array( 'label' => '25 A', 'max' => 2 ),
      array( 'label' => '25 B', 'max' => 3 ),
      array( 'label' => '26',   'max' => 2 ),
      array( 'label' => '27 A', 'max' => 2 ),
      array( 'label' => '27 B', 'max' => 2 ),
      array( 'label' => '28 A', 'max' => 2 ),
      array( 'label' => '28 B', 'max' => 2 ),
      array( 'label' => '29',   'max' => 2 ),
      array( 'label' => '30',   'max' => 2 ),
    );

    public function getAvailableRoommates($signup)
    {
      $roommates = $signup->room->fetch();
      $realmates = array($signup->id);

      foreach ($roommates as $room) {
        $roommate = $room->roommate;

        if (!$roommate->canceled) {
          $confirm = $roommate->room->where(array(
            'id_roommate' => $signup->id
          ))->count();

          if ($confirm) {
            $realmates[] = $room->roommate->id;
          }
        }
      }

      return array_unique($realmates);
    }

    public function getRoomAvailableRoommates($ids)
    {
      $possible = array();

      foreach ($ids as $id) {
        $signup = \Workshop\SignUp::get_first()->where(array(
          "id_workshop_signup" => $id,
          "canceled" => false,
        ))->fetch();

        if ($signup) {
          $possible[] = $this->getAvailableRoommates($signup);
        }
      }

      return array_values(
        array_filter(call_user_func_array('array_intersect', $possible), function($item) use($ids) {
          return !in_array($item, $ids);
        })
      );
    }

    public function getPossibleRoommates($availableRoommates, $master)
    {
      $possible = [];

      foreach ($availableRoommates as $search) {
        if (in_array($master[0], $search)) {
          $possible[] = $search;
        }
      }

      return $possible;
    }

    public function getRoommates(array $availableRoommates)
    {
      $mates = array();

      do {
        $master = array_shift($availableRoommates);

        if ($master) {
          $possible = $this->getPossibleRoommates($availableRoommates, $master);

          if (count($possible) > 1) {
            // Best possible candidates
            $best = call_user_func_array('array_intersect', $possible);

            if (count($best) > 1) {
              $availableRoommates = $this->removeIdsFromArray($availableRoommates, $best);
              $mates[] = array_values($best);
            } else {
              $mates[] = $master;
            }
          } else {
            $mates[] = $master;
          }
        }
      } while ($master);

      return $mates;
    }

    public function removeIdsFromArray($array, $ids) {
      // Filter choices of besties from possible
      $array = array_filter($array, function($result) use($ids) {
        return !in_array($result[0], $ids);
      });

      // Filter besties from choices
      $array = array_map(function($result) use($ids) {
        return array_values(array_filter($result, function($item) use ($ids) {
          return !in_array($item, $ids);
        }));
      }, $array);

      return $array;
    }

    public function optimizeRoommates($roommates)
    {
      $optimized = array();

      do {
        $match = array_shift($roommates);

        if ($match) {
          if (count($match) > $this::ROOM_MIN && count($match) < $this::ROOM_MAX) {
            $possible = $this->getRoomAvailableRoommates($match);

            foreach ($possible as $add) {
              if (count($match) < $this::ROOM_MAX) {
                array_push($match, $add);
              }
            }

            $optimized[] = $match;
            $roommates = $this->removeIdsFromArray($roommates, $match);
          } else {
            $optimized[] = $match;
          }
        }
      } while ($match);

      return $optimized;
    }

    public function getRooms($roommates)
    {
      $roommates = array_map(function($item) {
        sort($item);
        return $item;
      }, $roommates);

      rsort($roommates);
      return array_unique($roommates, SORT_REGULAR);
    }

    public function fetchRoomsSignups($rooms)
    {
      return array_map(function($id) {
        return \Workshop\Signup::find($id);
      }, $rooms);
    }

    public function fetchRooms()
    {
      $signups = \Workshop\SignUp::get_all(array(
        "hotel" => true,
        "solved" => true,
        "canceled" => false,
      ))->fetch();

      $availableRoommates = array_map(function($signup) {
        return $this->getAvailableRoommates($signup);
      }, $signups);

      $roommates = $this->getRoommates($availableRoommates);
      $roommates = $this->optimizeRoommates($roommates);

      $rooms = $this->getRooms($roommates);
      $housedIds = array_reduce($rooms, function($carry, $item) {
        if (count($item) > 1) {
          $carry = array_merge($carry, $item);
        }

        return $carry;
      }, []);

      $roomNumbers = $this->roomNumbers;

      usort($roomNumbers, function($a, $b) {
        if ($a['max'] == $b['max']) {
          return 0;
        }

        return $a['max'] > $b['max'] ? -1 : 1;
      });

      $rooms = array_map(function($item) use (&$roomNumbers) {
        $end = \DateTime::createFromFormat('Y-m-d H:i:s', \Module\Hotel\Picker::EDITABLE_UNTIL);
        $now = new \DateTime();

        if (count($item) > 1 || $now > $end) {
          foreach ($roomNumbers as $key=>$room) {
            if ($room['max'] <= count($item)) {
              break;
            }
          }
        }

        $label = 'Nespárováno';
        $max = '?';
        $paired = false;

        if (isset($room)) {
          array_splice($roomNumbers, $key, 1);
          $label = $room['label'];
          $max = $room['max'];
          $paired = true;
        }

        return array(
          "label" => $label,
          "max" => $max,
          "paired" => $paired,
          "housed" => $this->fetchRoomsSignups($item),
        );
      }, $rooms);

      return array(
        'rooms' => $rooms,
        'housed' => $housedIds,
        'free' => $roomNumbers,
        'matched' => array_map(function($item) { return $item->id; }, $signups),
      );
    }

    public function run()
    {
      $total = \Workshop\SignUp::count_all(array(
        "hotel" => true,
        "solved" => true,
        "canceled" => false,
      ));

      $rooms = $this->fetchRooms();

      $this->partial('stats/hotel', array(
        "total" => $total,
        "free" => $rooms['free'],
        "rooms" => $rooms['rooms'],
        "housed" => $rooms['housed'],
      ));
    }
  }
}

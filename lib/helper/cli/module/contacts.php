<?php

namespace Helper\Cli\Module
{
  class Contacts extends \Helper\Cli\Module
  {
    protected static $info = array(
      'name' => 'contacts',
      'head' => array(
        'Get contacts out of database',
      ),
    );


    protected static $attrs = array(
      "help"    => array("type" => 'bool', "value" => false, "short" => 'h', "desc"  => 'Show this help'),
      "verbose" => array("type" => 'bool', "value" => false, "short" => 'v', "desc" => 'Be verbose'),
    );


    protected static $commands = array(
      "workshops" => array('Get contacts for all workshops'),
    );


    public function cmd_workshops()
    {
      \System\Init::full();

      $ws = \Workshop::get_all()->fetch();
      $dump = array();

      foreach ($ws as $workshop) {
        $signups = $workshop->assignees
          ->where(array(
            "solved" => true,
          ))
          ->fetch();

        array_push($dump, $workshop->name, "------\n");

        foreach ($signups as $signup) {
          array_push($dump, $signup->toName().': '.$signup->email.' '.$signup->phone);
        }

        array_push($dump, "\n");
      }

      echo implode("\n", $dump);
    }
  }
}


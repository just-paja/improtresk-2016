<?php

namespace Module\Forms
{
	class Concept extends \System\Module
	{
		public function run()
		{
			$end = new \DateTime("2016-02-20 20:00:00+01:00");
			$now = new \DateTime();

			$this->partial('forms/concepts', array(
				"show_form" => $now <= $end
			));
		}
	}
}

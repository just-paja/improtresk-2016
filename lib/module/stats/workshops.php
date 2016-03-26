<?php

namespace Module\Stats
{
	class Workshops extends \System\Module
	{
		public function run()
		{
			$ws = \Workshop::get_all()->where(array('visible' => true))->fetch();

			foreach ($ws as $w) {
				$w->ass_total = $w->assignees->count();
				$w->ass_all   = $w->assignees->fetch();
			}

			$this->partial('stats/workshops', array(
				"workshops" => $ws,
			));
		}
	}
}

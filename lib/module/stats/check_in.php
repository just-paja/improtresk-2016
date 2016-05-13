<?php

namespace Module\Stats
{
	class CheckIn extends \System\Module
	{
		public function run()
		{
			$ppl = \Workshop\SignUp::get_all(array("solved" => true))
				->add_filter(array(
					"attr"    => 'id_assigned_to',
					"type"    => 'is_null',
					"is_null" => false,
				))
				->sort_by('name_first, name_last')
				->fetch();

			$this->partial('stats/check-in', array(
				"participants" => $ppl,
			));
		}
	}
}

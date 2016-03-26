<?php

namespace Module\Workshop
{
	class Detail extends \System\Module
	{
		public function run()
		{
			$item = \Workshop::find($this->workshop);

			$this->partial('pages/workshop', array(
				"ws" => $item,
			));
		}
	}
}

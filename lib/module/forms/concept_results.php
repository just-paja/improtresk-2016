<?php

namespace Module\Forms
{
	class ConceptResults extends \System\Module
	{
		public function run()
		{
			$count = \Workshop\Request::get_all()->count();
			$concepts = \Workshop\Concept::get_all()->sort_by('name')->fetch();
			$requests = \Workshop\Request::get_all()->add_filter(array(
				'attr' => 'other',
				'type' => 'is_null',
				'is_null' => false
			))->sort_by('created_at desc')->fetch();


			foreach ($concepts as $ws) {
				$ws->total = $ws->requests->count();
			}

			usort($concepts, function($a, $b) {
				if ($a->total == $b->total) {
					return 0;
				}

				return $a->total > $b->total ? -1:1;
			});

			$this->partial('concept/results', array(
				"count"    => $count,
				"concepts" => $concepts,
				"requests" => $requests
			));
		}
	}
}

<?

namespace Module\Stats
{
	class Match extends \System\Module
	{
		public function run()
		{
			$items   = \Survey\TeamAnswer::get_all()->fetch();
			$teams   = \Module\Forms\Match::$teams;
			$results = array();

			foreach ($items as $item) {
				$ans = $item->response;

				foreach ($ans as $id) {
					if (!array_key_exists($id, $results)) {
						$results[$id] = array(
							"name"  => $teams[$id],
							"total" => 0
						);
					}

					$results[$id]['total'] ++;
				}
			}

			$this->partial('stats/match', array(
				"results" => $results,
			));
		}
	}
}

<?

namespace Module\Stats
{
	class Teams extends \System\Module
	{
		public function run()
		{
			$conds = array(
				'paid' => true,
				'solved' => true
			);

			$items = \Workshop\SignUp::get_all()
				->reset_cols()
				->add_cols(array(
					"team"  => 'team',
					"total" => 'COUNT(*)',
				))
				->where($conds)
				->group_by('team')
				->assoc_with(null)
				->sort_by('total desc')
				->fetch();

			$this->partial('stats/teams', array(
				"items" => $items,
			));
		}
	}
}

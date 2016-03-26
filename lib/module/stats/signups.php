<?php

namespace Module\Stats
{
	class Signups extends \System\Module
	{
		public function run()
		{
			$account_pay = \Workshop\Check::get_all()
					->add_cols(array('SUM(amount) as `total`'))
					->where(array("is_paid" => false))
					->assoc_with(null)
					->fetch_one();

			$account_sum = \Workshop\Payment::get_all()
					->add_cols(array('SUM(amount) as `total`'))
					->assoc_with(null)
					->fetch_one();

			$stats = array(
				"total"   => \Workshop\SignUp::get_all()->count(),
				"unpaid"  => \Workshop\SignUp::get_all()->where(array('paid' => false))->count(),
				"paid"    => \Workshop\SignUp::get_all()->where(array('paid' => true))->count(),
				"waiting" => \Workshop\SignUp::get_all()->where(array('paid' => true, 'solved' => false))->count(),
				"solved"  => \Workshop\SignUp::get_all()->where(array('solved' => true))->count(),
				"meals"   => \Workshop\SignUp::get_all()->where(array('solved' => true, 'lunch' => true))->count(),
				"expected_cnt"  => \Workshop\Check::get_all()->where(array("is_paid" => false))->count(),
				"expected_sum"  => $account_pay['total'],
				"received_cnt"  => \Workshop\Payment::get_all()->count(),
				"received_sum"  => $account_sum['total'],
			);

			$ws = \Workshop::get_all()->fetch();

			foreach ($ws as $w) {
				$w->sig_total = $w->signups->count();
				$w->ass_total = $w->assignees->count();
			}

			$this->partial('stats/signups', array(
				"stats" => $stats,
				"workshops" => $ws,
			));
		}
	}
}

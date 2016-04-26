<?

namespace Module\User
{
	class Dispatcher extends \System\Module
	{
		const URL_TARGET   = 'food.pick';
		const FORM_HEADING = null;
		const FORM_DESC    = null;


		public function run()
		{
			$f = $this->response->form(array(
				"heading" => static::FORM_HEADING,
				"desc"    => static::FORM_DESC
			));

			$f->input(array(
				"label"    => 'Variabilní symbol',
				"name"     => 'symvar',
				"required" => true,
				"type"     => 'number',
				"min"      => 0
			));

			$f->submit('Zobrazit');

			$f->out($this);

			if ($f->passed()) {
				$data = $f->get_data();

				$this->flow->redirect($this->response->url(static::URL_TARGET, array(
					"symvar" => $data['symvar']
				)));
			}
		}
	}
}

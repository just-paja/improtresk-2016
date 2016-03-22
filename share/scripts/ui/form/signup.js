(function()
{
	"use strict";

	pwf.reg_class('ui.form.signup', {
		"parents":['model.list', 'form'],
		'requires':['Workshop'],

		"storage":{
			"opts":{
				'action':'/formulare/signup',
				'model':'Workshop',
				'sort':[
					{
						'attr':'name',
					}
				],

				'on_ready':function(err, res) {
					if (err) {
						v(err);
						v(res);
						alert('Něco selhalo. Zkus to prosím znovu později.');
					} else {
						var
							jobs = [],
							obj = this;

						jobs.push(function(next) {
							obj.get_el('form').slideUp(500, next);
						});

						jobs.push(function(next) {
							obj.get_el('form').html('Přihláška byla úspěšně odeslána. Potvrzení a její detaily naleznete v e-mailu. Neváhej nás kontaktovat, kdyby se něco zdálo v nepořádku.');
							obj.get_el('form').slideDown(200, next);
						});

						pwf.async.series(jobs);
					}
				},

				'on_invalid':function(err) {
					alert('Vyplň prosím všechna pole.');
				},

				"elements":[
					{
						'element':'container',
						'type':'basic-info',
						'heading':'Základní údaje',
						'elements':[
							{
								'name':'submited',
								'type':'hidden',
								'value':1
							},

							{
								'element':'container',
								'type':'name',
								'elements':[
									{
										'name':'name_first',
										'type':'text',
										'label':'Jméno',
										'placeholder':'Křestní',
										'required':true
									},

									{
										'name':'name_last',
										'type':'text',
										'placeholder':'Příjmení',
										'required':true
									}
								]
							},

							{
								'name':'team',
								'type':'text',
								'label':'Skupina'
							},

							{
								'name':'email',
								'type':'email',
								'label':'Tvůj e-mail',
								'required':true,
								'desc':'Na e-mail ti pošleme potvrzení přihlášky a detaily platby.'
							},

							{
								'name':'phone',
								'type':'text',
								'label':'Tvoje telefonní číslo',
								'required':true,
								'desc':'Pokud se vyskytne nějaký problém, budeme ti volat.'
							},

							{
								'name':'birthday',
								'type':'text',
								'label':'Datum narození',
								'required':true
							}
						]
					},
					{
						'element':'container',
						'type':'inputs',
						'heading':'Volitelné',
						'elements':[
							{
								'name':'lunch',
								'type':'checkbox',
								'label':'Chci obědy <span style="font-size:14px">+180 Kč</span>',
								'desc':'Více o obědech se dočtete v <a href="/jidlo">sekci pro účastníky</a>'
							},

							{
								'name':'hotel',
								'type':'checkbox',
								'label':'Chci ubytovat v hotelu <span style="font-size:14px">+600 Kč</span>',
								'desc':'Za pár kaček můžeš spát v hotelu který je pár kroků od DK Milevsko. Více v <a href="/jidlo">sekci ubytování</a>'
							}
						]
					},

					{
						'element':'container',
						'type':'workshops',
						'heading':'Workshopy',
						'desc':'Vyber si tři workshopy v pořadí podle preferencí. Pokud se naplní první než nám přijde tvoje platba, dáme tě do druhého. Pokud se naplní druhý, dáme tě do třetího.',
						'elements':[
							{
								"name":'workshop_0',
								'label':'Primární workshop',
								'type':'select',
								'required':true,
								'model':'Workshop',
								'on_change':function() {
									var
										val = this.val(),
										sel = this.get('form').get_input('workshop_1'),
										ex  = pwf.get_class('Workshop').get_all_existing(),
										op  = [];

									for (var i = 0; i < ex.length; i++) {
										if (ex[i].get('id') != val && ex[i].get('free') > 0) {
											op.push({
												'name':ex[i].get('name') + ', ' + ex[i].get('free') + ' míst',
												'value':ex[i].get('id')
											});
										}
									}

									sel.set('options', op);
									sel.get_el('input').html('');
									sel.job('create_options');
								}
							},

							{
								"name":'workshop_1',
								'label':'Sekundární workshop',
								'type':'select',
								'required':true,
								'on_change':function() {
									var
										val = this.val(),
										pri = this.get('form').get_input('workshop_0').val(),
										sel = this.get('form').get_input('workshop_2'),
										ex  = pwf.get_class('Workshop').get_all_existing(),
										op  = [];

									for (var i = 0; i < ex.length; i++) {
										var id = ex[i].get('id');

										if (id != val && id != pri && ex[i].get('free') > 0) {
											op.push({
												'name':ex[i].get('name') + ', ' + ex[i].get('free') + ' míst',
												'value':ex[i].get('id')
											});
										}
									}

									sel.set('options', op);
									sel.get_el('input').html('');
									sel.job('create_options');
								}
							},

							{
								"name":'workshop_2',
								'label':'Terciální workshop',
								'type':'select',
								'required':true
							}
						]
					},

					{
						'element':'container',
						'heading':'Odeslání',
						'desc':'Po úspěšném odeslání ti pošleme potvrzení přihlášky a detaily platby. Tato přihláška je závazná.',
						'elements':[
							{
								'type':'checkbox',
								'label':'Souhlasím s <a href="/pro-ucastniky">podmínkami pro účastníky</a>',
								'required':true
							},

							{
								'type':'checkbox',
								'label':'Chci posílat informace o událostech Improligy',
							},

							{
								'label':'Odeslat',
								'element':'button',
								'type':'submit',
								'attrs':{
									'class':'btn btn-primary',
								}
							}
						]
					}
				]
			}
		},


		'proto':{
			'create_struct':function(p)
			{
				this.load();
			},


			'loaded':function(p)
			{
				var
					items = p.storage.dataray.data,
					opts  = [];

				for (var i = 0 ; i < items.length; i++) {
					if (items[i].get('free') > 0) {
						opts.push({
							'name':items[i].get('name') + ', ' + items[i].get('free') + ' míst',
							'value':items[i].get('id')
						});
					}
				}

				var use = p.storage.opts.elements[2].elements;

				for (var i = 0 ; i < use.length; i++) {
					if (typeof use[i].model != 'undefined' && use[i].model == 'Workshop') {
						use[i].options = opts;
					}
				}

				p('create_meta');

				if (opts.length > 0) {
					p('create_form_obj');

					this.get_input('workshop_0').job('change');
					this.get_input('workshop_1').job('change');

					if (opts.length < 2) {
						this.get_input('workshop_1')
							.set('required', false)
							.get_el().remove();
					}

					if (opts.length < 3) {
						this.get_input('workshop_2')
							.set('required', false)
							.get_el().remove();
					}
				} else {
					this.get_el('form').html('Bohužel, všechny workshopy jsou už obsazené.');
				}
			}
		},


		'public':{
			'display_error':function(err)
			{
				alert('Ajaj. Něco tady nefunguje. Zkus to prosím znovu později.');
				v(err);
			},


			'display_thanks':function(p, next)
			{
				var jobs = [];

				jobs.push(function(next) {
					p.object.get_el('form').stop(true).slideUp(500, next);
				});

				jobs.push(function(next) {
					var el = p.object.get_el();

					el.create_divs(['thanks']);

					el.thanks
						.html('Děkujeme. Sledujte Facebookovou událost kvůli novinkám.')
						.hide()
						.slideDown(500, next);
				});


				pwf.async.series(jobs, next);
			}
		}
	});
})();

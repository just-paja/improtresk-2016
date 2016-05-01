pwf.wait_for('module', 'jquery', function() {
	pwf.wait_for('class', 'input.datetime', function() {
		pwf.queue.on('models-loaded', {
			'check':true,
			'cb':function()
			{
				"use strict";

				var map = [
					{
						'ui':'ui.form.workshop_ideas',
						'selector':'.form-concepts'
					},
					{
						'ui':'ui.form.signup',
						'selector':'.form-signup'
					},
					{
						'ui':'ui.car.offers.form',
						'selector':'.car-offers-form'
					},
					{
						'ui':'ui.car.requests.form',
						'selector':'.car-requests-form'
					}
				];


				for (var i = 0 ; i < map.length; i++) {
					pwf.wait_for('class', map[i].ui, function(item) {
						return function() {
							var el = pwf.jquery(item.selector);

							if (el.length) {
								pwf.create(item.ui, {
									'tag_overtake':true,
									'parent':el
								});
							}
						};
					}(map[i]));
				}

				pwf.scan();
			}
		});
	});
});

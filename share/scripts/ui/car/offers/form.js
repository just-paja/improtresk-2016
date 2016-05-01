(function()
{
  "use strict";

  pwf.reg_class('ui.car.offers.form', {
    "parents":['form'],
    'requires':['input.select', 'input.datetime', 'Car.Offer'],

    'storage':{
      'opts':{
        'action':'/autem/nabidka',

        'before_send':function()
        {
          var el = this.get_el().trigger('loading', this);
          //~ el.find('.form-group-typed-buttons').stop(true).slideUp();

          this.get_el('errors').hide();
          return true;
        },

        'on_ready':function(err, res)
        {
          var el = this.get_el().trigger('loaded', this);

          this.get_el('form').find('button').addClass('btn').addClass('btn-primary');

          if (err) {
            el.find('.form-group-typed-buttons').stop(true).slideDown();
            this.get_el('errors')
              .html('Nepovedlo se nasdílet tuto nabídku. Zkuste to prosím později.')
              .append(err)
              .stop(true)
              .slideDown();
          } else {
            this.get_el('form').removeClass('open');
            this.get_el('result').html('<p class="success">Tvoje nabídka byla uložena, děkujeme.</p>');
            res = JSON.parse(res);

            this.get_el('result')
              .stop(true)
              .slideDown();
          }
        },

        'elements':[
          {
            'element':'container',
            'type':'inputs',
            'elements':[
              {
                'name':'submited',
                'type':'hidden',
                'value':true
              },

              {
                'name':'driver',
                'label':'Tvoje jméno',
                'type':'text',
                'required':true,
                'desc':'Stačí přezdívka, která bude použita jako podpis.'
              },

              {
                'name':'from',
                'label':'Město',
                'type':'text',
                'required':true,
                'desc':'Jméno města, ze kterého budeš vyjíždět.'
              },

              {
                'name':'seats',
                'label':'Počet míst',
                'type':'number',
                'min':1,
                'step':1,
                'value':1,
                'required':true,
                'desc':'Kolik míst v autě nabídneš? Minimum je 1.'
              },

              {
                'name':'departure',
                'label':'Plánovaný odjezd',
                'type':'datetime',
                'required':true,
                'value':'2015-05-07T13:00:00+02:00',
                'desc':'Orientační čas tvého odjezdu'
              },

              {
                'name':'email',
                'label':'Tvůj e-mail',
                'type':'text',
                'required':true,
                'desc':'Na tento e-mail ti budeme posílat automatická upozornění pokud tvoje nabídka vyvolá zájem. Tvoje e-mailová adresa nebude zveřejněna.'
              },

              {
                'name':'icon',
                'type':'select',
                'label':'Ikona',
                'options':[
                  {"name":"sedan","value":"sedan"},
                  {"name":"combi","value":"combi"},
                  {"name":"hatchback","value":"hatchback"},
                  {"name":"cabriolet","value":"cabriolet"},
                  {"name":"mpv","value":"mpv"},
                  {"name":"suv","value":"suv"},
                  {"name":"limousine","value":"limousine"},
                  {"name":"tank","value":"tank"}
                ],
                'required':true
              },

              {
                'name':'desc',
                'label':'Zpráva pro zájemce',
                'type':'textarea',
                'desc':'Pokud chceš sdělit dodatečné informace pro zájemce, tady je k tomu prostor.'
              }
            ]
          },

          {
            'element':'container',
            'type':'buttons',
            'elements':[
              {
                'cname': ['btn', 'btn-primary'],
                'element':'button',
                'type':'submit',
                'label':'Vložit'
              }
            ]
          }
        ]
      }
    },

    'proto':{
      'els':[
        'heading',
        'desc',
        'result',
        'errors',

        {
          'name':'form',
          'prefix':'the',
          'tag':'form',
          'attrs':{
            'novalidate':'true',
            'enctype':'multipart/form-data'
          },

          'els':[
            {
              'name':'inner',
              'prefix':'form'
            }
          ]
        },

        {
          'name':'open-div',
          'cname': ['open'],
          'els':[
            {
              'name':'open',
              'cname':['btn', 'btn-primary'],
              'html':'Nabídnout místo v autě'
            }
          ]
        }
      ],


      'create_struct':function(p)
      {
        var
          id = this.get('id'),
          item;

        if (!id) {
          id = this.get_el().attr('data-id');
        }

        if (id) {
          item = pwf.get_class('Car.Offer').find_existing(id);

          if (item) {
            this.set('data', item.get_data());
            this.set('action', '');
            p.storage.opts.elements[1].elements[0].label = 'Aktualizovat';
          }
        }

        p('create_meta');
        p('create_form_obj');

        if (item) {
          this.get_el('form').show().addClass('open');
          this.get_el('open-div').remove();
        } else {
          this.get_el('open-div').find('.btn').bind('click touchend', p, p.get('actions.open'));
        }
      },


      'actions':
      {
        'open':function(e)
        {
          e.preventDefault();
          e.data.object.open();
        }
      }
    },


    'public':{
      'open':function()
      {
        this.get_el('form').addClass('open');
        this.get_el('open-div').removeClass('open');
      }
    }
  });
})();

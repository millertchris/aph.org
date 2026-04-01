var ac,
	accordions = {
		settings: {
		},
		init: function() {
			ac = this.settings;
			this.bindUIActions();
			console.log('accordions loaded!');
		},
		bindUIActions: function() {
			jQuery('.layout.accordions.tabbed .acc-item').hide();
			jQuery('.layout.accordions.tabbed #item-1').show();

			jQuery('.layout.accordions.tabbed .title a').on('click', function(e) {
				e.preventDefault();
				var anchor = jQuery(this).attr('href');
				jQuery('.layout.accordions.tabbed .acc-title').removeClass('active');
				jQuery('.layout.accordions.tabbed .acc-item').hide();
				jQuery('.layout.accordions.tabbed').find(anchor).show();
				jQuery(this).closest('.acc-title').addClass('active');
			});
		},
	};

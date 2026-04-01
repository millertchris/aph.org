var st,
	scrollTo = {
		settings: {
		},
		init: function () {
			ac = this.settings;
			this.bindUIActions();
			console.log('scrollTo loaded!');
		},
		bindUIActions: function () {
			jQuery('a[href*="#"]:not(a[href="#"])').on('click', function (e) {
				//e.preventDefault();
				jQuery('html, body').animate({
					scrollTop: jQuery(jQuery(this).attr('href')).offset().top - 150,
				}, 0, 'linear');
				console.log('scroll');
			});
		},
	};

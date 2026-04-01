var lc,
	LayoutCard = {
		settings: {
            clickableCard: jQuery('.card.btn.accessible-card.clickable-card')
		},
		init: function () {
			lc = this.settings;
			this.bindUIActions();
			// console.log('scrollTo loaded!');
		},
		bindUIActions: function () {
			lc.clickableCard.click(function(e){
				var cardAnchor = jQuery(this).find('a.btn');
				window.open(cardAnchor.attr('href'), cardAnchor.attr('target'));
            });
		},
	};

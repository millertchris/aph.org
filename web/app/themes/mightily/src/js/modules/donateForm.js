var df,
donateForm = {

    settings: {
		layout: jQuery('.layout.donate'),
        amount: jQuery('.amount'),
		description: jQuery('.description'),
        buttons: jQuery('button.donate')
    },

    init: function() {
        df = this.settings;
        this.bindUIActions();
        // console.log('donate form loaded!');
    },

    bindUIActions: function() {
		if (df.layout.length > 0) {
			df.buttons.each(function() {
				var button = jQuery(this);
				button.click(function(evt) {
					// evt.preventDefault();
					if (button.hasClass('active')) {
						// remove class
						button.removeClass('active');
					} else {
						// remove active class from all other buttons
						df.buttons.removeClass('active');
						// add class
						button.addClass('active');
						// get data value
						var newAmount = button.data('value');
						var newDescription = button.data('description');
						// change out heading with new amount
						df.amount.html(newAmount);
						df.description.html(newDescription);
					}
				});
			});
		}
    }

};

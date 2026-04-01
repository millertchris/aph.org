var s,
accessibleTabbing = {

    settings: {

    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        console.log('accessible tabbing loaded!');
    },

    bindUIActions: function() {

        jQuery(document)
            .keyup(function(evt) {
                if (evt.which == 9) {
                    var focused = jQuery(':focus');
                	var closestParent = focused.closest('.menu > li');
                	if (closestParent) {
                		var focusedParent = jQuery('.menu .focused');
                		if (focusedParent.length != 0 && !closestParent.hasClass('focused')) {
                			focusedParent.removeClass('focused');
                			closestParent.addClass('focused');
                		} else {
                			closestParent.addClass('focused');
                		}
                	}

                }
            });
            
    }

};

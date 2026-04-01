var s,
formLabels = {

    settings: {
        input: jQuery('.input-wrapper input'),
        header: jQuery('header')
    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        console.log('formLabels loaded!');
    },

    bindUIActions: function() {
        s.input.blur(function(){
            var tmpval = jQuery(this).val();
            if(tmpval == '') {
                jQuery(this).closest('.input-wrapper').find('label').addClass('empty');
                jQuery(this).closest('.input-wrapper').find('label').removeClass('not-empty');
                jQuery(this).addClass('empty');
                jQuery(this).removeClass('not-empty');
            } else {
              jQuery(this).closest('.input-wrapper').find('label').addClass('not-empty');
              jQuery(this).closest('.input-wrapper').find('label').removeClass('empty');
                jQuery(this).addClass('not-empty');
                jQuery(this).removeClass('empty');
            }
        });
    }

};

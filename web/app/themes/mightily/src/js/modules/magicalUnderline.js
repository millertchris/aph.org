var s,
magicalUnderline = {

    settings: {
        magicalUnderline: jQuery('.magical-underline')
    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        // console.log('magicalUnderline loaded!');
    },

    bindUIActions: function() {
        setTimeout(function(){
            jQuery('.magical-underline').addClass('rollin');
        }, 2500);
    }

};

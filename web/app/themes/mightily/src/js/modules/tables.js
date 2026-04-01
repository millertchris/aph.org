var ct,
    carbonTables = {
        settings: {
        },
        init: function () {
            ac = this.settings;
            this.updateWishlistTables();
            this.updateBasicContentTables();
            // console.log('scrollTo loaded!');
        },
        updateWishlistTables: function () {
            jQuery('.wl-table').each(function () {
                jQuery(this).addClass('bx--data-table');
                jQuery(this).removeClass('wl-table');
            });
        },
        updateBasicContentTables: function () {
            jQuery('.layout.basic-content table').each(function () {
                jQuery(this).addClass('bx--data-table');
                // If a thead is not present, move the first row to the thead
                if (!jQuery(this).find('thead').length) {
                    // console.log('appending head');
                    jQuery(this).prepend('<thead></thead>');
                    var newHead = jQuery(this).find('thead');
                    jQuery(this).find('tr').first().appendTo(newHead);
                }
            });
        }
    };

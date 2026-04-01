jQuery( function ( $ ) {
	
    function bes_get_order_data(){

        var shippingData = {};

        var orderData = {};

        // Create cart contents array
        var cartContents = [];

        if(jQuery("input[name='bes_ship']:checked").length > 0){
            jQuery("input[name='bes_ship']:checked").each(function(){
                console.log(jQuery(this).val());
                cartContents.push({
                    "productId" : jQuery(this).val(),
                    "qty" : jQuery(this).parents('.item').find('.quantity .view').text().trim().substr(2),
                    "itemId" : jQuery(this).parents('.item').data('order_item_id')
                });
            });
        } else {
            return false;
        }

        orderData.cartContents = cartContents;

        // Create ship to object
        // Check if shipping fields have values. If not, use billing fields.
        var addressType = 'shipping';
        if(jQuery('#_'+addressType+'_city').val() == '' || jQuery('#_'+addressType+'_postcode').val() == ''){
            addressType = 'billing';
        }
        orderData.shipTo = {};
        orderData.shipTo.city = jQuery('#_'+addressType+'_city').val();
        orderData.shipTo.state = jQuery('#_'+addressType+'_state').val();
        orderData.shipTo.postCode = jQuery('#_'+addressType+'_postcode').val();
        orderData.shipTo.country = jQuery('#_'+addressType+'_country').val();
        if(wc_enhanced_select_params.current_customer_role){
            orderData.customerRole = wc_enhanced_select_params.current_customer_role;
        } else {
            orderData.customerRole = 'guest';
        }
        return JSON.stringify(orderData);

    }
	
    var bes_shipping_request = {};

    var wc_meta_boxes_bes_shipping = {

        init: function() {

            this.stupidtable.init();
            $( '#woocommerce-order-items' ).off( 'click', 'a.delete-order-item');
            $( '#woocommerce-order-items' )

                .on( 'click', 'button.calculate-shipping', this.add_item )

                .on( 'click', 'a.delete-order-item', this.delete_item )
                .on( 'wc_order_items_reload', this.reload_items )		
				.on( 'wc_order_items_reloaded', this.reloaded_items )			
            ;

            $( document.body )
                .on( 'wc_backbone_modal_loaded', this.backbone.init )
                .on( 'wc_backbone_modal_response', this.backbone.response )
//				.on( 'wc_backbone_modal_response', function(){
//					setTimeout(function(){
//						wc_meta_boxes_bes_shipping.toggleShipCheckbox();
//					}, 3000);
//				})
				.on( 'order-totals-recalculate-complete', this.toggleShipCheckbox );
			
			$( document )
				.on( 'click' , '.modal-close', this.unblock)
				.on( 'click' , '.bes_ship_checkbox', this.toggleShipCheckbox)
				.on( 'click' , '.bes_ship_checkbox_select_all', function(){
					wc_meta_boxes_bes_shipping.toggleShipCheckboxSelectAll(this);
				});
				//.on( 'click' , '.bes_ship_checkbox_select_all', this.toggleShipCheckbox);
                //.on( 'wc_backbone_modal_removed', this.unblock );
        },

        init_tiptip: function() {
            $( '#tiptip_holder' ).removeAttr( 'style' );
            $( '#tiptip_arrow' ).removeAttr( 'style' );
            $( '.tips' ).tipTip({
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            });
        },
		
		toggleShipCheckbox: function(){
			if($(".bes_ship_checkbox").length == $(".bes_ship_checkbox:checked").length) {
				$(".bes_ship_checkbox_select_all").prop("checked", true);
			} else {
				$(".bes_ship_checkbox_select_all").prop("checked", false);
			}			
		},
		
		toggleShipCheckboxSelectAll: function(el){
			if($(el).prop("checked")) {
				$(".bes_ship_checkbox").prop("checked", true);
			} else {
				$(".bes_ship_checkbox").prop("checked", false);
			}			
		},

        block: function() {
            $( '#woocommerce-order-items' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        unblock: function() {
            $( '#woocommerce-order-items' ).unblock();
        },

        reload_items: function() {

            var data = {
                order_id: woocommerce_admin_meta_boxes.post_id,
                action:   'woocommerce_load_order_items',
                security: woocommerce_admin_meta_boxes.order_item_nonce
            };

            wc_meta_boxes_bes_shipping.block();

            $.ajax({
                url:  woocommerce_admin_meta_boxes.ajax_url,
                data: data,
                type: 'POST',
                success: function( response ) {
                    $( '#woocommerce-order-items' ).find( '.inside' ).empty();
                    $( '#woocommerce-order-items' ).find( '.inside' ).append( response );
                    wc_meta_boxes_bes_shipping.reloaded_items();
                    wc_meta_boxes_bes_shipping.unblock();
                }
            });
        },

        reloaded_items: function() {
            wc_meta_boxes_bes_shipping.init_tiptip();
            wc_meta_boxes_bes_shipping.stupidtable.init();
			wc_meta_boxes_bes_shipping.toggleShipCheckbox();
        },

        get_taxable_address: function() {
			var country          = '';
			var state            = '';
			var postcode         = '';
			var city             = '';

			if ( 'shipping' === woocommerce_admin_meta_boxes.tax_based_on ) {
				country  = $( '#_shipping_country' ).val();
				state    = $( '#_shipping_state' ).val();
				postcode = $( '#_shipping_postcode' ).val();
				city     = $( '#_shipping_city' ).val();
			}

			if ( 'billing' === woocommerce_admin_meta_boxes.tax_based_on || ! country ) {
				country  = $( '#_billing_country' ).val();
				state    = $( '#_billing_state' ).val();
				postcode = $( '#_billing_postcode' ).val();
				city     = $( '#_billing_city' ).val();
			}

			return {
				country:  country,
				state:    state,
				postcode: postcode,
				city:     city
			};
		},

        add_item: function() {
            wc_meta_boxes_bes_shipping.block();
            var $this = $(this);
            jQuery.ajax({
                type: "POST",
                url: "/wp-json/backendshipping/v1/options",
                dataType: "json",
                data: bes_get_order_data(),
                success: function(data) {
                    $this.WCBackboneModal({
                        template: 'shipping-rates',
                        variable: data
                    });
                    console.log(data);
                    return false;
                }
            });
        },

        delete_order_item_meta: function($el){

            if($el.parents('tr').hasClass('shipping')){

                var data = {
                    'action'   : 'remove_shipping_line_item', // missed this same as your action hook wp_ajax_{add_shipping_line_item}
                    'order_id' : woocommerce_admin_meta_boxes.post_id,
                    'security' : besAjax.remove_security, // We can access it this way
                    'data'     : $el.parents('tr.shipping').data('order_item_id'),
                    'dataType' : 'json'
                }

                $.ajax({
					url:     besAjax.ajax_url,
					data:    data,
					type:    'POST',
					success: function( response ) {
						if ( response.success ) {
                            wc_meta_boxes_bes_shipping.delete_order_item($el);
						} else {
							window.alert( response.data.error );
                            wc_meta_boxes_bes_shipping.unblock();
						}
                        jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
                        jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');						
					}
				});

            } else {

                wc_meta_boxes_bes_shipping.delete_order_item($el);

            }

        },

        delete_order_item: function($el){
            var $item         = $el.closest( 'tr.item, tr.fee, tr.shipping' );
            var order_item_id = $item.attr( 'data-order_item_id' );

            var data = $.extend( {}, wc_meta_boxes_bes_shipping.get_taxable_address(), {
                order_id      : woocommerce_admin_meta_boxes.post_id,
                order_item_ids: order_item_id,
                action        : 'woocommerce_remove_order_item',
                security      : woocommerce_admin_meta_boxes.order_item_nonce
            } );

            // Check if items have changed, if so pass them through so we can save them before deleting.
            if ( 'true' === $( 'button.cancel-action' ).attr( 'data-reload' ) ) {
                data.items = $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize();
            }

            $.ajax({
                url:     woocommerce_admin_meta_boxes.ajax_url,
                data:    data,
                type:    'POST',
                success: function( response ) {
                    if ( response.success ) {
                        $( '#woocommerce-order-items' ).find( '.inside' ).empty();
                        $( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );

                        // Update notes.
                        if ( response.data.notes_html ) {
                            $( 'ul.order_notes' ).empty();
                            $( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
                        }

                        wc_meta_boxes_bes_shipping.reloaded_items();
                        wc_meta_boxes_bes_shipping.unblock();

                    } else {

                        window.alert( response.data.error );

                    }

                    wc_meta_boxes_bes_shipping.unblock();

                },
                complete: function() {
                    window.wcTracks.recordEvent( 'order_edit_remove_item', {
                        order_id: data.post_id,
                        status: $( '#order_status' ).val()
                    } );
                    jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
                    jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
                }
            });
        },

        delete_item: function() {

            var answer = window.confirm( woocommerce_admin_meta_boxes.remove_item_notice );

            // We need to delete the meta attached to the line item then remove the item

			if ( answer ) {

                wc_meta_boxes_bes_shipping.block();

                wc_meta_boxes_bes_shipping.delete_order_item_meta($(this));

			}

			return false;

        },

        get_taxable_address: function() {
            var country          = '';
            var state            = '';
            var postcode         = '';
            var city             = '';

            if ( 'shipping' === woocommerce_admin_meta_boxes.tax_based_on ) {
                country  = $( '#_shipping_country' ).val();
                state    = $( '#_shipping_state' ).val();
                postcode = $( '#_shipping_postcode' ).val();
                city     = $( '#_shipping_city' ).val();
            }

            if ( 'billing' === woocommerce_admin_meta_boxes.tax_based_on || ! country ) {
                country  = $( '#_billing_country' ).val();
                state    = $( '#_billing_state' ).val();
                postcode = $( '#_billing_postcode' ).val();
                city     = $( '#_billing_city' ).val();
            }

            return {
                country:  country,
                state:    state,
                postcode: postcode,
                city:     city
            };
        },

        save_line_items: function() {
            var data = {
                order_id: woocommerce_admin_meta_boxes.post_id,
                items:    $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize(),
                action:   'woocommerce_save_order_items',
                security: woocommerce_admin_meta_boxes.order_item_nonce
            };

            $.ajax({
                url:  woocommerce_admin_meta_boxes.ajax_url,
                data: data,
                type: 'POST',
                success: function( response ) {
                    if ( response.success ) {
                        $( '#woocommerce-order-items' ).find( '.inside' ).empty();
                        $( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );

                        wc_meta_boxes_bes_shipping.reloaded_items();
                        wc_meta_boxes_bes_shipping.unblock();
                    } else {
                        wc_meta_boxes_bes_shipping.unblock();
                        window.alert( response.data.error );
                    }
                },
                complete: function() {

                    window.wcTracks.recordEvent( 'order_edit_save_line_items', {
                        order_id: data.post_id,
                        status: $( '#order_status' ).val()
                    } );
                    jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
                    jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
                }
            });

            $( this ).trigger( 'items_saved' );

            return false;
        },

        backbone: {

            init: function( e, target ) {
                if ( 'shipping-rates' === target ) {
                    // Do something here if need be
                }
            },

            response: function( e, target, data ) {
                if ( 'shipping-rates' === target ) {
                    // Build array of data.
                    var rows            = $( this ).find( '#shipping_method .shipping-package' ),
                        add_items       = [];

                    $(rows).each( function(key, value) {
                        //var shipping_data = $( this ).find('input:radio:checked').val();
						var shipping_data = $( this ).find('select').val();

                        add_items[key] = shipping_data;
                    } );

                    return wc_meta_boxes_bes_shipping.backbone.add_items( add_items );
                }
            },

            add_items: function( add_items ) {

                wc_meta_boxes_bes_shipping.block();

                var data = {
                    'action'   : 'add_shipping_line_item', // missed this same as your action hook wp_ajax_{add_shipping_line_item}
                    'order_id' : woocommerce_admin_meta_boxes.post_id,
                    'security' : besAjax.add_security, // We can access it this way
                    'data'     : add_items,
                    'dataType' : 'json'
                }

                jQuery.post(besAjax.ajax_url, data, function(response) {
                    var responseHtml = jQuery(response.data.html);
                    if (response.success) {
                        jQuery( 'table.woocommerce_order_items tbody#order_shipping_line_items' ).append(responseHtml);
                        window.wcTracks.recordEvent( 'order_edit_add_shipping', {
                            order_id: data.post_id,
                            status: jQuery( '#order_status' ).val()
                        });
                    } else {
                        window.alert( response.data.error );
                    }

                    wc_meta_boxes_bes_shipping.save_line_items();

                });

                return false;

            }

        },

        stupidtable: {
            init: function() {
                //$( '.woocommerce_order_items' ).stupidtable();
                $( '.woocommerce_order_items' ).on( 'aftertablesort', this.add_arrows );
            },

            add_arrows: function( event, data ) {
                var th    = $( this ).find( 'th' );
                var arrow = data.direction === 'asc' ? '&uarr;' : '&darr;';
                var index = data.column;
                th.find( '.wc-arrow' ).remove();
                th.eq( index ).append( '<span class="wc-arrow">' + arrow + '</span>' );
            }
        }

    };
	
    wc_meta_boxes_bes_shipping.init();

});

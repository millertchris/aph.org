/*global jQuery, Backbone, _ */
( function( $, Backbone, _ ) {
	'use strict';

	/**
	 * WooCommerce Backbone Modal plugin
	 *
	 * @param {object} options
	 */
	$.fn.LouisBackboneModal = function( options ) {
		return this.each( function() {
			( new $.LouisBackboneModal( $( this ), options ) );
		});
	};

	/**
	 * Initialize the Backbone Modal
	 *
	 * @param {object} element [description]
	 * @param {object} options [description]
	 */
	$.LouisBackboneModal = function( element, options ) {
		// Set settings
		var settings = $.extend( {}, $.LouisBackboneModal.defaultOptions, options );

		if ( settings.template ) {
			new $.LouisBackboneModal.View({
				target: settings.template,
				string: settings.variable
			});
		}
	};

	/**
	 * Set default options
	 *
	 * @type {object}
	 */
	$.LouisBackboneModal.defaultOptions = {
		template: '',
		variable: {}
	};

	/**
	 * Create the Backbone Modal
	 *
	 * @return {null}
	 */
	$.LouisBackboneModal.View = Backbone.View.extend({
		tagName: 'div',
		id: 'louis-backbone-modal-dialog',
		_target: undefined,
		_string: undefined,
		events: {
			'click .louis-modal-close': 'closeButton',
			'click #louis-btn-ok'     : 'addButton',
			'touchstart #louis-btn-ok': 'addButton',
			'keydown'           : 'keyboardActions'
		},
		resizeContent: function() {
			var $content  = $( '.wc-backbone-modal-content' ).find( 'article' );
			var max_h     = $( window ).height() * 0.75;

			$content.css({
				'max-height': max_h + 'px'
			});
		},
		initialize: function( data ) {
			var view     = this;
			this._target = data.target;
			this._string = data.string;
			_.bindAll( this, 'render' );
			this.render();

			$( window ).on( 'resize', function() {
				view.resizeContent();
			});
		},
		render: function() {
			var template = wp.template( this._target );

			this.$el.append(
				template( this._string )
			);

			$( document.body ).css({
				'overflow': 'hidden'
			}).append( this.$el );

			this.resizeContent();
			this.$( '.wc-backbone-modal-content' ).attr( 'tabindex' , '0' ).trigger( 'focus' );

			$( document.body ).trigger( 'init_tooltips' );

			$( document.body ).trigger( 'louis_backbone_modal_loaded', this._target );
		},
		closeButton: function( e ) {
			e.preventDefault();
			$( document.body ).trigger( 'louis_backbone_modal_before_remove', this._target );
			this.undelegateEvents();
			$( document ).off( 'focusin' );
			$( document.body ).css({
				'overflow': 'auto'
			});
			this.remove();
			$( document.body ).trigger( 'louis_backbone_modal_removed', this._target );
		},
		addButton: function( e ) {

			this.$el.find('#louis-admin-heading').text('Fetching products from Louis, please wait');
			this.$el.find('#louis-admin-footer button').attr('disabled', 'true');
			this.$el.find('#louis-admin-footer .inner').append( '<img alt="processing" src="images/wpspin_light.gif" class="waiting" style="margin-left:6px;margin-top:8px;" />' );

			var thisBackbone = this;

			setTimeout(function(){
				$( document.body ).trigger( 'louis_backbone_modal_response', [ thisBackbone._target, thisBackbone.getFormData() ] );
				thisBackbone.closeButton( e );
			}, 100);

		},
		getFormData: function() {
			var data = {};

			$( document.body ).trigger( 'wc_backbone_modal_before_update', this._target );

			$.each( $( 'form', this.$el ).serializeArray(), function( index, item ) {
				if ( item.name.indexOf( '[]' ) !== -1 ) {
					item.name = item.name.replace( '[]', '' );
					data[ item.name ] = $.makeArray( data[ item.name ] );
					data[ item.name ].push( item.value );
				} else {
					data[ item.name ] = item.value;
				}
			});

			return data;
		},
		keyboardActions: function( e ) {
			var button = e.keyCode || e.which;

			// Enter key
			if (
				13 === button &&
				! ( e.target.tagName && ( e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'textarea' ) )
			) {
				this.addButton( e );
			}

			// ESC key
			if ( 27 === button ) {
				this.closeButton( e );
			}
		}
	});

}( jQuery, Backbone, _ ));

( function( $ ) {

	function getEnhancedSelectFormatString() {
		return {
			'language': {
				errorLoading: function() {
					// Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
					return wc_enhanced_select_params.i18n_searching;
				},
				inputTooLong: function( args ) {
					var overChars = args.input.length - args.maximum;

					if ( 1 === overChars ) {
						return wc_enhanced_select_params.i18n_input_too_long_1;
					}

					return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
				},
				inputTooShort: function( args ) {
					var remainingChars = args.minimum - args.input.length;

					if ( 1 === remainingChars ) {
						return wc_enhanced_select_params.i18n_input_too_short_1;
					}

					return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
				},
				loadingMore: function() {
					return wc_enhanced_select_params.i18n_load_more;
				},
				maximumSelected: function( args ) {
					if ( args.maximum === 1 ) {
						return wc_enhanced_select_params.i18n_selection_too_long_1;
					}

					return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
				},
				noResults: function() {
					return wc_enhanced_select_params.i18n_no_matches;
				},
				searching: function() {
					return wc_enhanced_select_params.i18n_searching;
				}
			}
		};
	}

	try {
		$( document.body )

			.on( 'wc-enhanced-select-init', function() {

				function display_result( self, select2_args ) {
					select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

					$( self ).selectWoo( select2_args ).addClass( 'enhanced' );

					if ( $( self ).data( 'sortable' ) ) {
						var $select = $(self);
						var $list   = $( self ).next( '.select2-container' ).find( 'ul.select2-selection__rendered' );

						$list.sortable({
							placeholder : 'ui-state-highlight select2-selection__choice',
							forcePlaceholderSize: true,
							items       : 'li:not(.select2-search__field)',
							tolerance   : 'pointer',
							stop: function() {
								$( $list.find( '.select2-selection__choice' ).get().reverse() ).each( function() {
									var id     = $( this ).data( 'data' ).id;
									var option = $select.find( 'option[value="' + id + '"]' )[0];
									$select.prepend( option );
								} );
							}
						});
					// Keep multiselects ordered alphabetically if they are not sortable.
					} else if ( $( self ).prop( 'multiple' ) ) {
						$( self ).on( 'change', function(){
							var $children = $( self ).children();
							$children.sort(function(a, b){
								var atext = a.text.toLowerCase();
								var btext = b.text.toLowerCase();

								if ( atext > btext ) {
									return 1;
								}
								if ( atext < btext ) {
									return -1;
								}
								return 0;
							});
							$( self ).html( $children );
						});
					}
				}

				// Ajax product search box
				$( ':input.wc-louis-product-search' ).filter( ':not(.enhanced)' ).each( function() {

					var select2_args = {
						allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
						placeholder: $( this ).data( 'placeholder' ),
						minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
						escapeMarkup: function( m ) {
							return m;
						},
						ajax: {
							url:         woocommerce_louis.solrUrl,
							dataType:    'json',
							delay:       250,
							headers: {
								"Content-Type" : "application/json",
							},														
							type:		 'POST',
							data: function (params) {
								return JSON.stringify(
									{
										"query": "allsearch:"+params.term,
										"limit": 50,
										"filter": ["(childBooks.agency:\"APH American Printing House for the Blind\" AND childBooks.catlogNumber:* AND TMMSourceId:L AND childBooks.price:*)"]
									}
								);
							},
							processResults: function( data ) {
								
								// console.log(data.response.docs);

								var terms = [];
								if ( data && data.response && data.response.docs ) {
									$.each(data.response.docs, function (index, value) {
										terms.push({
											id: value.cart_link,
											text: value.title + " (" + value['childBooks.catlogNumber'] + ") &ndash; Format: " + value['childBooks.format'] + ", Price: $" + value['childBooks.price'] + " (FQ Eligible)",
											stock: 999,
											backorders_allowed: true,
											cart_link: value.cart_link
										});
									});
								}
								return {
									results: terms
								};
							},
							cache: true
						}
					};
					display_result( this, select2_args );
				});

			})

			// WooCommerce Backbone Modal
			.on( 'louis_backbone_modal_before_remove', function() {
				$( '.wc-enhanced-select, :input.wc-louis-product-search, :input.wc-customer-search' ).filter( '.select2-hidden-accessible' )
					.selectWoo( 'close' );
			})

			.trigger( 'wc-enhanced-select-init' );

		$( 'html' ).on( 'click', function( event ) {
			if ( this === event.target ) {
				$( '.wc-enhanced-select, :input.wc-louis-product-search, :input.wc-customer-search' ).filter( '.select2-hidden-accessible' )
					.selectWoo( 'close' );
			}
		} );
	} catch( err ) {
		// If select2 failed (conflict?) log the error but don't stop other scripts breaking.
		window.console.log( err );
	}
}( jQuery ));

( function( $ ) {

	var wc_meta_boxes_louis_order_items = {
		init: function() {

			this.stupidtable.init();

			$( '#woocommerce-order-items' )
				.on( 'click', 'button.add-louis-order-item', this.add_item )

			$( document.body )
				.on( 'louis_backbone_modal_loaded', this.backbone.init )
				.on( 'louis_backbone_modal_response', this.backbone.response )		
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

			wc_meta_boxes_louis_order_items.block();

			$.ajax({
				url:  woocommerce_admin_meta_boxes.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					$( '#woocommerce-order-items' ).find( '.inside' ).empty();
					$( '#woocommerce-order-items' ).find( '.inside' ).append( response );
					wc_meta_boxes_louis_order_items.reloaded_items();
					wc_meta_boxes_louis_order_items.unblock();
				}
			});
		},

		reloaded_items: function() {
			wc_meta_boxes_louis_order_items.init_tiptip();
			wc_meta_boxes_louis_order_items.stupidtable.init();
		},

		add_item: function() {
			$( this ).LouisBackboneModal({
				template: 'woocommerce-louis'
			});

			return false;
		},

		init_tiptip: function() {
			$( '#tiptip_holder' ).removeAttr( 'style' );
			$( '#tiptip_arrow' ).removeAttr( 'style' );
			$( '.tips' ).tipTip({
				'attribute': 'data-tip',
				'fadeIn': 50,
				'fadeOut': 50,
				'delay': 200,
				'keepAlive': true
			});
		},		

		backbone: {

			init: function( e, target ) {
				
				if ( 'woocommerce-louis' === target ) {
					$( document.body ).trigger( 'wc-enhanced-select-init' );

					$( this ).on( 'change', '.wc-louis-product-search', function() {
						if ( ! $( this ).closest( 'tr' ).is( ':last-child' ) ) {
							return;
						}
						var item_table      = $( this ).closest( 'table.widefat' ),
							item_table_body = item_table.find( 'tbody' ),
							index           = item_table_body.find( 'tr' ).length,
							row             = item_table_body.data( 'row' ).replace( /\[0\]/g, '[' + index + ']' );

						item_table_body.append( '<tr>' + row + '</tr>' );
						$( document.body ).trigger( 'wc-enhanced-select-init' );
					} );
				}
			},
			response: function( e, target, data ) {

				if ( 'woocommerce-louis' === target ) {
					// Build array of data.
					var item_table      = $( this ).find( 'table.widefat' ),
						item_table_body = item_table.find( 'tbody' ),
						item_footer 	= $( this ).find( 'footer' ),
						rows            = item_table_body.find( 'tr' ).not(':last'),
						add_items       = [];
					
					$( rows ).each( function() {
						// Loop through each selected product and call the cart link so the product is created
						var cart_link = $( this ).find( ':input[name="item_id"]' ).val(),
							item_qty = $( this ).find( ':input[name="item_qty"]' ).val();

						if(cart_link && cart_link != ''){

							$.ajax({
								async: false,
								type: 'GET',
								url: woocommerce_louis.addToCartUrl + cart_link + '&raw',
								success: function(data) {
									console.log(woocommerce_louis.addToCartUrl + cart_link);
									console.log(data);									
									add_items.push( {
										'id' : data,
										'qty': item_qty ? item_qty: 1
									});
								}
						   });
							
						}

					} );

					return wc_meta_boxes_louis_order_items.backbone.add_items( add_items );

					
				}
			},

			add_items: function( add_items ) {

				// runs after selecting products and clicking 'add' button within the modal
				// Need to create the product for each item
				wc_meta_boxes_louis_order_items.block();
				console.log(add_items);
				var data = {
					action   : 'woocommerce_add_order_item',
					order_id : woocommerce_admin_meta_boxes.post_id,
					security : woocommerce_admin_meta_boxes.order_item_nonce,
					data     : add_items
				};

				// Check if items have changed, if so pass them through so we can save them before adding a new item.
				if ( 'true' === $( 'button.cancel-action' ).attr( 'data-reload' ) ) {
					data.items = $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize();
				}

				$.ajax({
					type: 'POST',
					url: woocommerce_admin_meta_boxes.ajax_url,
					data: data,
					success: function( response ) {
						if ( response.success ) {
							$( '#woocommerce-order-items' ).find( '.inside' ).empty();
							$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );

							// Update notes.
							if ( response.data.notes_html ) {
								$( 'ul.order_notes' ).empty();
								$( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
							}

							wc_meta_boxes_louis_order_items.reloaded_items();
							wc_meta_boxes_louis_order_items.unblock();
						} else {
							wc_meta_boxes_louis_order_items.unblock();
							window.alert( response.data.error );
						}
					},
					complete: function() {
						window.wcTracks.recordEvent( 'order_edit_add_louis_products', {
							order_id: data.post_id,
							status: $( '#order_status' ).val()
						} );
					},
					dataType: 'json'
				});
			}

		},

		stupidtable: {
			init: function() {
				$( '.woocommerce_order_items' ).stupidtable();
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
	
    wc_meta_boxes_louis_order_items.init();    

}( jQuery ));

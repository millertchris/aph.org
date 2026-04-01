/* global sfl_enhanced_select_params */
jQuery( function ( $ ) {
	'use strict' ;
	try {
		$( document.body ).on( 'sfl-enhanced-init' , function () {
			if ( $( 'select.sfl_select2' ).length ) {
				//Select2 with customization
				$( 'select.sfl_select2' ).each( function () {
					var select2_args = {
						allowClear : $( this ).data( 'allow_clear' ) ? true : false ,
						placeholder : $( this ).data( 'placeholder' ) ,
						minimumResultsForSearch : 10 ,
					} ;
					$( this ).select2( select2_args ) ;
				} ) ;
			}
			if ( $( 'select.sfl_select2_search' ).length ) {
				//Multiple select with ajax search
				$( 'select.sfl_select2_search' ).each( function () {

					var select2_args = {
						allowClear : $( this ).data( 'allow_clear' ) ? true : false ,
						placeholder : $( this ).data( 'placeholder' ) ,
						minimumInputLength : $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : 3 ,
						escapeMarkup : function ( m ) {
							return m ;
						} ,
						ajax : {
							url : sfl_enhanced_select_params.ajaxurl ,
							dataType : 'json' ,
							delay : 250 ,
							data : function ( params ) {
								return {
									term : params.term ,
									action : $( this ).data( 'action' ) ? $( this ).data( 'action' ) : '' ,
									sfl_security : $( this ).data( 'nonce' ) ? $( this ).data( 'nonce' ) : sfl_enhanced_select_params.search_nonce ,
								} ;
							} ,
							processResults : function ( data ) {
								var terms = [ ] ;
								if ( data ) {
									$.each( data , function ( id , term ) {
										terms.push( {
											id : id ,
											text : term
										} ) ;
									} ) ;
								}
								return {
									results : terms
								} ;
							} ,
							cache : true
						}
					} ;

					$( this ).select2( select2_args ) ;
				} ) ;
			}

			if ( $( '.sfl_colorpicker' ).length ) {
				$( '.sfl_colorpicker' ).each( function ( ) {

					$( this ).iris( {
						change : function ( event , ui ) {
							$( this ).css( { backgroundColor : ui.color.toString( ) } ) ;
						} ,
						hide : true ,
						border : true
					} ) ;

					$( this ).css( 'background-color' , $( this ).val() ) ;
				} ) ;

				$( document ).on( 'click' , function ( e ) {
					if ( ! $( e.target ).is( ".sfl_colorpicker, .iris-picker, .iris-picker-inner" ) ) {
						$( '.sfl_colorpicker' ).iris( 'hide' ) ;
					}
				} ) ;

				$( '.sfl_colorpicker' ).on( 'click' , function ( e ) {
					$( '.sfl_colorpicker' ).iris( 'hide' ) ;
					$( this ).iris( 'show' ) ;
				} ) ;
			}
		} ) ;

		$( document.body ).trigger( 'sfl-enhanced-init' ) ;
	} catch ( err ) {
		window.console.log( err ) ;
	}

} ) ;

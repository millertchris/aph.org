/* SFL Settings */
jQuery ( function ( $ ) {

	var SFL_Settings = {
		init : function () {
			this.trigger_on_page_load () ;

			$ ( document ).on ( 'change' , '#sfl_general_enable_guest_sfl' , this.trigger_enable_guest_sfl ) ;
			//SFL Advance Settings
			$ ( document ).on ( 'change' , '#sfl_advanced_product_category' , this.trigger_product_category ) ;
			$ ( document ).on ( 'change' , '#sfl_advanced_user_roles_users' , this.trigger_user_roles_users ) ;
			$ ( document ).on ( 'change' , '#sfl_advanced_apply_styles_from' , this.trigger_styles_from ) ;
		} ,
		trigger_on_page_load : function () {
			this.toggle_enable_guest_sfl ( '#sfl_general_enable_guest_sfl' ) ;
			this.toggle_product_category ( '#sfl_advanced_product_category' ) ;
			this.toggle_user_roles_users ( '#sfl_advanced_user_roles_users' ) ;
			this.toggle_styles_from ( '#sfl_advanced_apply_styles_from' ) ;
		} ,
		trigger_product_category : function ( event ) {
			event.preventDefault () ;
			var $this = $ ( event.currentTarget ) ;
			SFL_Settings.toggle_product_category ( $this ) ;
		} ,
		toggle_product_category : function ( $this ) {
			$ ( '.sfl_product_cat_search_fields' ).closest ( 'tr' ).hide () ;
			if ( $ ( $this ).val () == 2 ) {
				$ ( '#sfl_advanced_included_product' ).closest ( 'tr' ).show () ;
			} else if ( $ ( $this ).val () == 3 ) {
				$ ( '#sfl_advanced_exclude_product' ).closest ( 'tr' ).show () ;
			} else if ( $ ( $this ).val () == 5 ) {
				$ ( '#sfl_advanced_included_category' ).closest ( 'tr' ).show () ;
			} else if ( $ ( $this ).val () == 6 ) {
				$ ( '#sfl_advanced_exclude_category' ).closest ( 'tr' ).show () ;
			}
		} ,
		trigger_user_roles_users : function ( event ) {
			event.preventDefault () ;
			var $this = $ ( event.currentTarget ) ;
			SFL_Settings.toggle_user_roles_users ( $this ) ;
		} ,
		toggle_user_roles_users : function ( $this ) {
			$ ( '.sfl_customers_roles_search' ).closest ( 'tr' ).hide () ;
			if ( $ ( $this ).val () == 2 ) {
				$ ( '#sfl_advanced_included_user' ).closest ( 'tr' ).show () ;
			} else if ( $ ( $this ).val () == 3 ) {
				$ ( '#sfl_advanced_exclude_user' ).closest ( 'tr' ).show () ;
			} else if ( $ ( $this ).val () == 4 ) {
				$ ( '#sfl_advanced_included_user_role' ).closest ( 'tr' ).show () ;
			} else if ( $ ( $this ).val () == 5 ) {
				$ ( '#sfl_advanced_excluded_user_role' ).closest ( 'tr' ).show () ;
			}
		} ,
		trigger_enable_guest_sfl : function ( event ) {
			event.preventDefault () ;
			var $this = $ ( event.currentTarget ) ;
			SFL_Settings.toggle_enable_guest_sfl ( $this ) ;
		} ,
		toggle_enable_guest_sfl : function ( $this ) {
			if ( $ ( $this ).is ( ':checked' ) == true ) {
				$ ( '.sfl_general_guest_fields' ).closest ( 'tr' ).show () ;
			} else {
				$ ( '.sfl_general_guest_fields' ).closest ( 'tr' ).hide () ;
			}
		} ,
		trigger_styles_from : function ( event ) {
			event.preventDefault () ;
			var $this = $ ( event.currentTarget ) ;
			SFL_Settings.toggle_styles_from ( $this ) ;
		} ,
		toggle_styles_from : function ( $this ) {
			if ( $ ( $this ).val () == 1 ) {
				$ ( '#sfl_advanced_custom_css' ).closest ( 'tr' ).show () ;
			} else if ( $ ( $this ).val () == 2 ) {
				$ ( '#sfl_advanced_custom_css' ).closest ( 'tr' ).hide () ;
			} 
		} ,

	} ;
	SFL_Settings.init () ;
} ) ;

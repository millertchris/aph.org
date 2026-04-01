<?php

class WC_Wishlist_Cloner {
	private static $instance;

	public static function register() {
		if ( empty( self::$instance ) ) {
			self::$instance = new WC_Wishlist_Cloner();
		}
	}

	public function __construct() {
		// Add a Clone action to the wishlist post type edit actions.
		add_filter( 'post_row_actions', array( $this, 'add_clone_action' ), 10, 2 );

		// Handle the cloning of a wishlist.
		add_action( 'admin_init', array( $this, 'clone_wishlist' ) );
	}

	public function add_clone_action( $actions, $post ) {
		if ( 'wishlist' === $post->post_type ) {
			$actions['clone'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'post'   => $post->ID,
							'action' => 'clone_wishlist',
						),
						admin_url( 'admin.php' )
					)
				),
				esc_attr__( 'Clone this wishlist', 'wc_wishlist' ),
				esc_html__( 'Clone', 'wc_wishlist' )
			);
		}

		return $actions;
	}

	/**
	 * Handles the cloning of a wishlist.
	 */
	public function clone_wishlist() {
		if ( ! isset( $_GET['post'] ) || ! isset( $_GET['action'] ) || 'clone_wishlist' !== $_GET['action'] ) {
			return;
		}

		$wishlist_id = absint( $_GET['post'] );
		$wishlist    = wc_get_wishlist( $wishlist_id );

		if ( ! $wishlist ) {
			return;
		}

		$new_wishlist_id = $this->clone_wishlist_data( $wishlist );

		if ( $new_wishlist_id ) {
			wp_safe_redirect( admin_url( 'post.php?post=' . $new_wishlist_id . '&action=edit' ) );
			exit;
		}
	}

	/**
	 * Clones the wishlist data.
	 *
	 * @param WC_Wishlists_Wishlist $wishlist Wishlist to clone.
	 *
	 * @return int New wishlist ID.
	 */
	private function clone_wishlist_data( WC_Wishlists_Wishlist $wishlist ) {

		$new_wishlist_id = wp_insert_post(
			array(
				'post_title'   => $wishlist->get_title() . ' - ' . __( 'Clone', 'wc_wishlist' ),
				'post_content' => $wishlist->get_description(),
				'post_status'  => 'publish',
				'post_type'    => 'wishlist',
			)
		);

		if ( $new_wishlist_id ) {
			// Clone wishlist meta.
			$meta = get_post_meta( $wishlist->get_id() );
			foreach ( $meta as $key => $value ) {
				update_post_meta( $new_wishlist_id, $key, maybe_unserialize( $value[0] ) );
			}

			/*
			 * For the future if we make list items its own object type
			$items = $wishlist->get_items();
			foreach ( $items as $item ) {
				$item->set_wishlist_id( $new_wishlist_id );
				$item->save();
			}
			*/
		}

		return $new_wishlist_id;
	}
}

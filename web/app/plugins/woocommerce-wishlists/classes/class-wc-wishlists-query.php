<?php

class WC_Wishlists_Query {

	public static function search_by_user( $key, $type = false ) {
		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args = array(
			'post_type'  => 'wishlist',
			'orderby'    => 'title post_date',
			'nopaging'   => true,
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'   => '_wishlist_owner',
					'value' => $key,
				)
			)
		);

		if ( $type ) {
			$args['meta_query'][] = array( 'key' => '_wishlist_sharing', 'value' => $type );
		}

		$posts = get_posts( $args );
		$lists = array();
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$lists[] = new WC_Wishlists_Wishlist( $post->ID );
			}
		}

		return $lists;
	}

	public static function search_by_first_last_email( $first, $last, $email ) {
		$args = array(
			'post_type'  => 'wishlist',
			'orderby'    => 'title post_date',
			'nopaging'   => true,
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => '_wishlist_first_name',
					'value'   => $first,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_wishlist_last_name',
					'value'   => $last,
					'compare' => 'LIKE'
				),
				array(
					'key'     => '_wishlist_owner_email',
					'value'   => $email,
					'compare' => 'LIKE',
				)
			)
		);

		$posts = get_posts( $args );
		$lists = array();
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$lists[] = new WC_Wishlists_Wishlist( $post->ID );
			}
		}

		return $lists;
	}

	public static function search_by_first( $first ) {
		$args = array(
			'post_type'  => 'wishlist',
			'orderby'    => 'title post_date',
			'nopaging'   => true,
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'     => '_wishlist_first_name',
					'value'   => $first,
					'compare' => 'LIKE',
				)
			)
		);

		$posts = get_posts( $args );
		$lists = array();
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$lists[] = new WC_Wishlists_Wishlist( $post->ID );
			}
		}

		return $lists;
	}

	public static function search_by_last( $last ) {
		$args = array(
			'post_type'  => 'wishlist',
			'orderby'    => 'title post_date',
			'nopaging'   => true,
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'     => '_wishlist_last_name',
					'value'   => $last,
					'compare' => 'LIKE',
				)
			)
		);

		$posts = get_posts( $args );
		$lists = array();
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$lists[] = new WC_Wishlists_Wishlist( $post->ID );
			}
		}

		return $lists;
	}

	public static function search_by_email( $email ) {
		$args = array(
			'post_type'  => 'wishlist',
			'orderby'    => 'title post_date',
			'nopaging'   => true,
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'     => '_wishlist_owner_email',
					'value'   => $email,
					'compare' => 'LIKE',
				)
			)
		);

		$posts = get_posts( $args );
		$lists = array();
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$lists[] = new WC_Wishlists_Wishlist( $post->ID );
			}
		}

		return $lists;
	}

}

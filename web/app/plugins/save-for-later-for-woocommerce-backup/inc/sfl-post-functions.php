<?php



if ( ! function_exists( 'sfl_create_entry' ) ) {

	/**
	 * Create New SFL
	 *
	 * @return integer/string
	 */
	function sfl_create_entry( $meta_args, $post_args = array() ) {

		$object = new SFL_List();
		$id     = $object->create( $meta_args, $post_args );

		return $id;
	}
}

if ( ! function_exists( 'sfl_get_entry' ) ) {

	/**
	 * Get SFL object
	 *
	 * @return object
	 */
	function sfl_get_entry( $id ) {
		$object = new SFL_List( $id );

		return $object;
	}
}

if ( ! function_exists( 'sfl_update_entry' ) ) {

	/**
	 * Update SFL
	 *
	 * @return object
	 */
	function sfl_update_entry( $id, $meta_args, $post_args = array() ) {

		$object = new SFL_List( $id );
		$object->update( $meta_args, $post_args );

		return $object;
	}
}

if ( ! function_exists( 'sfl_delete_entry' ) ) {

	/**
	 * Delete Rule
	 *
	 * @return bool
	 */
	function sfl_delete_entry( $id, $force = true ) {

		wp_delete_post( $id, $force );

		return true;
	}
}


if ( ! function_exists( 'sfl_create_log_entry' ) ) {

	/**
	 * Create New SFL
	 *
	 * @return integer/string
	 */
	function sfl_create_log_entry( $meta_args, $post_args = array() ) {

		$object = new SFL_Log_List();
		$id     = $object->create( $meta_args, $post_args );

		return $id;
	}
}

if ( ! function_exists( 'sfl_get_log_entry' ) ) {

	/**
	 * Get SFL object
	 *
	 * @return object
	 */
	function sfl_get_log_entry( $id ) {

		$object = new SFL_Log_List( $id );

		return $object;
	}
}

if ( ! function_exists( 'sfl_update_log_entry' ) ) {

	/**
	 * Update SFL
	 *
	 * @return object
	 */
	function sfl_update_log_entry( $id, $meta_args, $post_args = array() ) {

		$object = new SFL_Log_List( $id );
		$object->update( $meta_args, $post_args );

		return $object;
	}
}

if ( ! function_exists( 'sfl_delete_log_entry' ) ) {

	/**
	 * Delete Rule
	 *
	 * @return bool
	 */
	function sfl_delete_log_entry( $id, $force = true ) {

		wp_delete_post( $id, $force );

		return true;
	}
}

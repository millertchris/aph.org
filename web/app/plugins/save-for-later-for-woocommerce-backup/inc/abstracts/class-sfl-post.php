<?php
/**
 * Post
 *
 * @package Abstract
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Post' ) ) {

	/**
	 * SFL_Post Class.
	 */
	abstract class SFL_Post {

		/**
		 * ID
		 *
		 * @var Integer
		 */
		protected $id = '';

		/**
		 * Post Author
		 *
		 * @var String
		 */
		public $name = '';

		/**
		 * Name
		 *
		 * @var String
		 */
		public $post_author = '';

		/**
		 * Post
		 *
		 * @var Object
		 */
		protected $post;

		/**
		 * Post type
		 *
		 * @var String
		 */
		protected $post_type = '';

		/**
		 * Post status
		 *
		 * @var String
		 */
		protected $post_status = '';

		/**
		 * Meta data
		 *
		 * @var Array
		 */
		protected $meta_data = array();

		/**
		 * Meta data keys
		 *
		 * @var Array
		 */
		protected $meta_data_keys = array();

		/**
		 * Status
		 *
		 * @var Array
		 */
		protected $status;

		/**
		 * Class initialization.
		 *
		 * @since 1.0
		 * @param Integer $id
		 * @param Boolean $populate
		 */
		public function __construct( $id = '', $populate = true ) {
			$this->id = $id;

			if ( $populate && $id ) {
				$this->populate_data();
			}
		}

		/**
		 * Has Status
		 *
		 * @since 1.0
		 * @param Array|String $status Post Status.
		 */
		public function has_status( $status ) {
			$current_status = $this->get_status();

			if ( is_array( $status ) && in_array( $current_status, $status ) ) {
				return true;
			}

			if ( $current_status == $status ) {
				return true;
			}

			return false;
		}

		/**
		 * Update Status
		 *
		 * @since 1.0
		 * @param Array|String $status Post Status.
		 */
		public function update_status( $status ) {
			$post_args = array(
				'ID'          => $this->id,
				'post_type'   => $this->post_type,
				'post_status' => $status,
			);

			return wp_update_post( $post_args );
		}

		/**
		 * Id
		 *
		 * @since 1.0
		 * @return Integer
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Status
		 *
		 * @since 1.0
		 * @param Boolean $bool
		 * @return String
		 */
		public function get_status( $bool = true ) {
			if ( $this->status ) {
				return $this->status;
			}

			if ( $bool ) {
				return get_post_status( $this->id );
			} else {
				$status_object = get_post_status_object( $this->status );

				return $status_object->label;
			}
		}

		/**
		 * Post exists
		 *
		 * @since 1.0
		 * @return Boolean
		 */
		public function exists() {
			return isset( $this->post->post_type ) && $this->post->post_type == $this->post_type;
		}

		/**
		 * Populate Data for this post
		 *
		 * @since 1.0
		 * @return Array
		 */
		protected function populate_data() {
			if ( 'auto-draft' == $this->get_status() ) {
				return;
			}

			$this->load_postdata();
			$this->load_metadata();
		}

		/**
		 * Prepare Post data
		 *
		 * @since 1.0
		 * @return Array
		 */
		protected function load_postdata() {
			$this->post = get_post( $this->id );

			if ( ! $this->post ) {
				return;
			}

			$this->status = $this->post->post_status;

			$this->load_extra_postdata();
		}

		/**
		 * Prepare extra post data
		 */
		protected function load_extra_postdata() {
		}

		/**
		 * Prepare Post Meta data
		 *
		 * @since 1.0
		 * @return Array
		 */
		protected function load_metadata() {
			$meta_data_array = get_post_meta( $this->id );

			if ( ! sfl_check_is_array( $meta_data_array ) ) {
				return $meta_data_array;
			}

			$new_meta_array = array();

			foreach ( $this->meta_data_keys as $key => $value ) {
				$this->$key = $value;

				if ( ! isset( $meta_data_array[ $key ][0] ) ) {
					continue;
				}

				$meta_data              = ( is_serialized( $meta_data_array[ $key ][0] ) ) ? @unserialize( $meta_data_array[ $key ][0] ) : $meta_data_array[ $key ][0];
				$new_meta_array[ $key ] = $meta_data;
				$this->$key             = $meta_data;
			}

			$this->meta_data = (object) $new_meta_array;

			return $this->meta_data;
		}

		/**
		 * Create a post
		 *
		 * @since 1.0
		 * @param Array $meta_data.
		 * @param Array $post_args.
		 * @return Integer
		 */
		public function create( $meta_data, $post_args = array() ) {
			$default_post_args = array(
				'post_type'   => $this->post_type,
				'post_status' => $this->post_status,
			);

			$post_args = wp_parse_args( $post_args, $default_post_args );

			$this->id = wp_insert_post( $post_args );

			$this->update_metas( $meta_data );

			$this->populate_data();

			return $this->id;
		}

		/**
		 * Update a post
		 *
		 * @since 1.0
		 * @param Array $meta_data.
		 * @param Array $post_args.
		 * @return Integer
		 */
		public function update( $meta_data, $post_args = array() ) {
			if ( ! $this->id ) {
				return false;
			}

			$default_post_args = array(
				'ID'          => $this->id,
				'post_type'   => $this->post_type,
				'post_status' => $this->get_status(),
			);

			$post_args = wp_parse_args( $post_args, $default_post_args );

			wp_update_post( $post_args );

			$this->update_metas( $meta_data );

			$this->populate_data();

			return $this->id;
		}

		/**
		 * Update post metas
		 *
		 * @since 1.0
		 * @param Array $meta_data.
		 * @return Boolean
		 */
		public function update_metas( $meta_data ) {
			if ( ! $this->id ) {
				return false;
			}

			foreach ( $this->meta_data_keys as $meta_key => $default ) {
				if ( ! isset( $meta_data[ $meta_key ] ) ) {
					continue;
				}

				update_post_meta( $this->id, sanitize_key( $meta_key ), $meta_data[ $meta_key ] );
			}
		}

		/**
		 * Update post meta
		 *
		 * @since 1.0
		 * @param String $meta_key.
		 * @param String $value.
		 * @return Boolean
		 */
		public function update_meta( $meta_key, $value ) {
			if ( ! $this->id ) {
				return false;
			}

			update_post_meta( $this->id, sanitize_key( $meta_key ), $value );
		}
	}

}

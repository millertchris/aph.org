<?php
/**
 * Singleton class for all model classes.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Abstracts
 */

namespace WPMUDEV_Videos\Core\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class Model
 *
 * @package WPMUDEV_Videos\Core\Abstracts
 */
abstract class Model {

	/**
	 * Model ID.
	 *
	 * @var int $id
	 *
	 * @since 1.8.0
	 */
	public $id;

	/**
	 * Error class object.
	 *
	 * @var \WP_Error $error
	 *
	 * @since 1.8.0
	 */
	private $error;

	/**
	 * Singleton constructor for the model.
	 *
	 * Protect the class from being initiated multiple times.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	protected function __construct() {
		// Protect class from initiated multiple times.
		$this->error = new \WP_Error();
	}

	/**
	 * Instance obtaining method.
	 *
	 * @param int  $id Model object ID.
	 * @param bool $force Force skip cache.
	 *
	 * @since 1.8.0
	 *
	 * @return static Called class instance.
	 */
	public static function get( $id = 0, $force = false ) {
		static $instances = array();

		$id = (int) $id;

		// @codingStandardsIgnoreLine Plugin-backported
		$called_class_name = get_called_class();

		// Asking for a new object.
		if ( empty( $id ) ) {
			return new $called_class_name();
		} elseif ( ! isset( $instances[ $called_class_name ][ $id ] ) || $force ) {
			if ( ! isset( $instances[ $called_class_name ] ) ) {
				$instances[ $called_class_name ] = array();
			}

			$instances[ $called_class_name ][ $id ] = new $called_class_name();
			// Set the ID.
			$instances[ $called_class_name ][ $id ]->id = $id;

			// Optionally initialize the class.
			if ( method_exists( $instances[ $called_class_name ][ $id ], 'setup' ) ) {
				$instances[ $called_class_name ][ $id ]->setup();
			}
		}

		return $instances[ $called_class_name ][ $id ];
	}

	/**
	 * Set error if current data is not valid.
	 *
	 * @param \WP_Error|array $result Data to check.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function validate_result( $result ) {
		if ( is_wp_error( $result ) ) {
			$this->error = $result;
		}
	}

	/**
	 * Set a single error message to class.
	 *
	 * @param string|int $code    Error code.
	 * @param string     $message Error message.
	 * @param mixed      $data    Optional. Error data.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function set_error( $code, $message, $data = array() ) {
		$this->error->add( $code, $message, $data );
	}

	/**
	 * Check if current model is new.
	 *
	 * @since 1.8.0
	 *
	 * @return bool.
	 */
	public function is_new() {
		return empty( $this->id );
	}

	/**
	 * Check if current model object already exist in db.
	 *
	 * @since 1.8.0
	 *
	 * @return bool.
	 */
	public function is_existing() {
		return ! $this->is_new();
	}

	/**
	 * Check if current model doesn't have any error.
	 *
	 * @since 1.8.0
	 *
	 * @return bool.
	 */
	public function is_valid() {
		return ! $this->is_error();
	}

	/**
	 * Check if current model has errors.
	 *
	 * @since 1.8.0
	 *
	 * @return bool.
	 */
	public function is_error() {
		return $this->error->has_errors();
	}

	/**
	 * Get the error object.
	 *
	 * @since 1.8.0
	 *
	 * @return \WP_Error.
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Reset the error instance of the model.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function reset_error() {
		$this->error = new \WP_Error();
	}

	/**
	 * Sanitize the model data values.
	 *
	 * By default all values will be sanitized for text input
	 * unless if specified.
	 *
	 * @param mixed  $value Meta data.
	 * @param string $key   Field key.
	 *
	 * @since 1.8.0
	 *
	 * @return mixed
	 */
	public function sanitize( $value, $key = '' ) {
		switch ( $key ) {
			case 'id':
				$this->id = (int) $this->id;
				break;
			default:
				$value = sanitize_text_field( $value );
				break;
		}

		/**
		 * Filter hook to perform additional sanitization.
		 *
		 * @param mixed  $value Field value.
		 * @param string $key   Field key.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_model_sanitize', $value, $key );
	}

	/**
	 * Setter method.
	 *
	 * Set property and values to class.
	 *
	 * @param string $key   Property to set.
	 * @param mixed  $value Value to assign to the property.
	 *
	 * @since 1.8.0
	 */
	public function __set( $key, $value ) {
		$this->{$key} = $value;
	}

	/**
	 * Getter method.
	 *
	 * Allows access to extended site properties.
	 *
	 * @param string $key Property to get.
	 *
	 * @since 1.8.0
	 *
	 * @return mixed Value of the property. Null if not available.
	 */
	public function __get( $key ) {
		// If set, get it.
		if ( isset( $this->{$key} ) ) {
			return $this->{$key};
		}

		return null;
	}

	/**
	 * Setup modal class object.
	 *
	 * Every models should implement this method.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	abstract protected function setup();

	/**
	 * Save current model object.
	 *
	 * Every models should implement this method.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	abstract public function save();

	/**
	 * Delete current model object.
	 *
	 * Every models should implement this method.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	abstract public function delete();
}
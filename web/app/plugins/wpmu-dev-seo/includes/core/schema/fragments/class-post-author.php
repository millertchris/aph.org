<?php
/**
 * Post_Author class for handling post author schema fragments in SmartCrawl.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Models\User;
use SmartCrawl\Schema\Utils;
use SmartCrawl\Settings;

/**
 * Class Post_Author
 *
 * Handles post author schema fragments.
 */
class Post_Author extends Fragment {

	/**
	 * Schema utilities.
	 *
	 * @var Utils
	 */
	private $utils;

	/**
	 * User object.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Post_Author constructor.
	 *
	 * @param User $user The user object.
	 */
	public function __construct( $user ) {
		$this->user  = $user;
		$this->utils = Utils::get();
	}

	/**
	 * Retrieves the post author ID.
	 *
	 * @return string The post author ID.
	 */
	public function get_post_author_id() {
		$schema_enable_author_url = (bool) $this->utils->get_schema_option( 'schema_enable_author_url' );
		return $this->get_author_id( $this->user, $schema_enable_author_url );
	}

	/**
	 * Retrieves raw schema data.
	 *
	 * @return array The raw schema data.
	 */
	protected function get_raw() {
		$name = $this->utils->get_user_full_name( $this->user );

		$schema_enable_author_url = (bool) $this->utils->get_schema_option( 'schema_enable_author_url' );

		// Use author posts URL for base only if schema_enable_author_url is enabled
		$base_url = $schema_enable_author_url ? $this->get_user_url( $this->user ) : get_site_url();

		$schema = array(
			'@type' => 'Person',
			'@id'   => $this->get_author_id( $this->user, $schema_enable_author_url ),
			'name'  => $name,
		);

		$url = $this->get_user_url( $this->user );

		if ( $schema_enable_author_url ) {
			$schema['url'] = $url;
		}

		$description = $this->user->get_description();
		if ( $description ) {
			$schema['description'] = $description;
		}

		if ( $this->utils->is_author_gravatar_enabled() ) {
			$schema['image'] = $this->utils->get_image_schema(
				$this->utils->url_to_id( $base_url, '#schema-author-gravatar' ),
				$this->user->get_avatar_url( 100 ),
				100,
				100,
				$name
			);
		}

		$urls = $this->get_user_urls( $this->user );
		if ( $urls ) {
			$schema['sameAs'] = $urls;
		}

		return $this->utils->apply_filters( 'user-data', $schema, $this->user );
	}

	/**
	 * Retrieves the user URL.
	 *
	 * @param User $user The user object.
	 *
	 * @return string The user URL.
	 */
	private function get_user_url( $user ) {
		return $this->utils->apply_filters( 'user-url', $user->get_user_url(), $user );
	}

	/**
	 * Retrieves the user URLs.
	 *
	 * @param User $user The user object.
	 *
	 * @return array The user URLs.
	 */
	public function get_user_urls( $user ) {
		return $this->utils->apply_filters( 'user-urls', $user->get_user_urls(), $user );
	}

	/**
	 * Retrieves the author ID.
	 *
	 * @param User $user The user object.
	 * @param bool $schema_enable_author_url Whether author URL is enabled in schema settings.
	 *
	 * @return string The author ID (a valid URI).
	 */
	private function get_author_id( $user, bool $schema_enable_author_url = true ) {
		if ( $schema_enable_author_url ) {
			$url = get_author_posts_url( $user->get_id() );
			return $this->utils->url_to_id( $url, '#schema-author' );
		}

		// When schema_enable_author_url is disabled, use the site URL
		$site_url = get_site_url();
		$user_id  = $user->get_id();
		return $this->utils->url_to_id( $site_url, '#schema-author-' . $user_id );
	}
}
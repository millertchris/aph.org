<?php
/**
 * Controller class for handling WPML integration.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\WPML;

use SmartCrawl\Singleton;
use SmartCrawl\Controllers;

/**
 * Class Controller
 *
 * Manages WPML integration and related hooks.
 */
class Controller extends Controllers\Controller {

	use Singleton;

	/**
	 * WPML API instance.
	 *
	 * @var Api
	 */
	private $wpml_api;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct();

		$this->wpml_api = new Api();
	}

	/**
	 * Checks if WPML is active.
	 *
	 * @return bool True if WPML is active, false otherwise.
	 */
	public function should_run() {
		return class_exists( '\SitePress' );
	}

	/**
	 * Initializes the controller.
	 */
	protected function init() {
		$this->hook_with_wpml();
	}

	/**
	 * Hooks the controller with WPML.
	 */
	public function hook_with_wpml() {
		global $sitepress;
		if ( empty( $sitepress ) ) {
			return;
		}

		add_action( 'wds_post_readability_language', array( $this, 'change_post_analysis_language' ), 10, 2 );
		add_action( 'wds_post_seo_analysis_language', array( $this, 'change_post_analysis_language' ), 10, 2 );

		$strategy                 = $this->wpml_api->get_setting( 'language_negotiation_type' );
		$separate_domain_per_lang = 2 === $strategy;
		if ( $separate_domain_per_lang ) {
			$this->sitemap_for_each_domain();
		} else {
			$this->fix_duplicate_urls();
		}
	}

	/**
	 * If the user has a separate domain for each language we need to make sure that each domain serves a unique sitemap only containing URLs belonging to that domain
	 */
	private function sitemap_for_each_domain() {
		add_filter(
			'wds_posts_sitemap_include_post_ids',
			array(
				$this,
				'limit_sitemap_posts_by_language',
			),
			10,
			2
		);
		add_filter( 'wds_terms_sitemap_include_term_ids', array( $this, 'limit_sitemap_terms_by_language' ), 10, 2 );
		add_filter( 'wds_news_sitemap_include_post_ids', array( $this, 'limit_sitemap_posts_by_language' ), 10, 2 );
		add_filter( 'wds_sitemap_cache_file_name', array( $this, 'append_language_code_to_cache' ) );
		add_filter( 'wds_sitemap_ignored_page_ids', array( $this, 'exclude_homepage_translations' ) );
	}

	/**
	 * Limits sitemap terms by language.
	 *
	 * @param array $include_ids Term IDs to include.
	 * @param array $taxonomies  Taxonomies to query.
	 *
	 * @return array Filtered term IDs.
	 */
	public function limit_sitemap_terms_by_language( $include_ids, $taxonomies ) {
		$term_query = new \WP_Term_Query(
			array(
				'taxonomy' => $taxonomies,
				'fields'   => 'ids',
			)
		);

		$term_ids = $term_query->get_terms();
		if ( empty( $term_ids ) ) {
			$term_ids = array( - 1 );
		}
		$include_ids = empty( $include_ids ) || ! is_array( $include_ids )
			? array()
			: $include_ids;

		return array_merge( $include_ids, $term_ids );
	}

	/**
	 * Limits sitemap posts by language.
	 *
	 * @param array $include_ids Post IDs to include.
	 * @param array $post_types  Post types to query.
	 *
	 * @return array Filtered post IDs.
	 */
	public function limit_sitemap_posts_by_language( $include_ids, $post_types ) {
		$query = new \WP_Query(
			array(
				'post_type'        => $post_types,
				'posts_per_page'   => - 1,
				'post_status'      => 'publish',
				'fields'           => 'ids',
				'suppress_filters' => false,
			)
		);

		$post_ids = $query->get_posts();
		if ( empty( $post_ids ) ) {
			$post_ids = array( - 1 );
		}
		$include_ids = empty( $include_ids ) || ! is_array( $include_ids )
			? array()
			: $include_ids;

		return array_merge( $include_ids, $post_ids );
	}

	/**
	 * Appends language code to cache file name.
	 *
	 * @param string $file_name Original file name.
	 *
	 * @return string Modified file name with language code.
	 */
	public function append_language_code_to_cache( $file_name ) {
		$current_lang = apply_filters( 'wpml_current_language', null );

		return "$current_lang-$file_name";
	}

	/**
	 * WPML tries to 'translate' urls but in our context it leads to every URL getting converted to the default language.
	 *
	 * If the post ID of an Urdu post is passed to get_permalink, we expect to get the Urdu url in return but the conversion changes it to default language URL.
	 */
	private function fix_duplicate_urls() {
		add_filter( 'wds_before_sitemap_rebuild', array( $this, 'add_permalink_filters' ) );
		add_filter( 'wds_sitemap_created', array( $this, 'remove_permalink_filters' ) );
		add_filter( 'wds_full_sitemap_items', array( $this, 'add_homepage_versions' ) );
		add_filter( 'wds_partial_sitemap_items', array( $this, 'add_homepage_versions_to_partial' ), 10, 3 );
		add_filter( 'wds_sitemap_ignored_page_ids', array( $this, 'exclude_homepage_translations' ) );
	}

	/**
	 * Adds homepage versions to partial sitemap.
	 *
	 * @param array  $items       Sitemap items.
	 * @param string $type        Sitemap type.
	 * @param int    $page_number Page number.
	 *
	 * @return array Modified sitemap items.
	 */
	public function add_homepage_versions_to_partial( $items, $type, $page_number ) {
		$is_first_post_sitemap = ( 'post' === $type || 'page' === $type ) && 1 === $page_number;
		if ( ! $is_first_post_sitemap ) {
			return $items;
		}

		return $this->add_homepage_versions( $items );
	}

	/**
	 * Adds homepage versions to sitemap.
	 *
	 * @param array $items Sitemap items.
	 *
	 * @return array Modified sitemap items.
	 */
	public function add_homepage_versions( $items ) {
		// Remove the original home url.
		array_shift( $items );

		// Add all homepage versions.
		$languages = $this->wpml_api->get_active_languages( false, true );
		foreach ( $languages as $language_code => $language ) {
			if ( $this->wpml_api->get_default_language() === $language_code ) {
				continue;
			}

			$item_url = $this->wpml_api->convert_url( home_url(), $language_code );
			array_unshift(
				$items,
				$this->get_sitemap_homepage_item( $item_url )
			);
		}

		array_unshift(
			$items,
			$this->get_sitemap_homepage_item( home_url( '/' ) )
		);

		return $items;
	}

	/**
	 * Adds permalink filters.
	 */
	public function add_permalink_filters() {
		$callback = array( $this, 'translate_post_url' );

		add_filter( 'post_link', $callback, 10, 2 );
		add_filter( 'page_link', $callback, 10, 2 );
		add_filter( 'post_type_link', $callback, 10, 2 );
	}

	/**
	 * Removes permalink filters.
	 */
	public function remove_permalink_filters() {
		$callback = array( $this, 'translate_post_url' );

		remove_filter( 'post_link', $callback );
		remove_filter( 'page_link', $callback );
		remove_filter( 'post_type_link', $callback );
	}

	/**
	 * Translates post URL to the current language.
	 *
	 * @param string   $link       Original link.
	 * @param \WP_Post $post_or_id Post object or ID.
	 *
	 * @return string Translated link.
	 */
	public function translate_post_url( $link, $post_or_id ) {
		$post          = get_post( $post_or_id );
		$language      = $this->wpml_api->wpml_get_language_information( null, $post->ID );
		$language_code = \smartcrawl_get_array_value( $language, 'language_code' );
		if ( $this->wpml_api->get_current_language() === $language_code ) {
			return $link;
		}

		$this->remove_permalink_filters(); // To avoid infinite recursion.
		$language_url = apply_filters( 'wpml_permalink', get_permalink( $post->ID ), $language_code, true );
		$this->add_permalink_filters();

		return $language_url;
	}

	/**
	 * Gets sitemap homepage item.
	 *
	 * @param string $url URL of the homepage.
	 *
	 * @return \SmartCrawl\Sitemaps\General\Item Sitemap item.
	 */
	private function get_sitemap_homepage_item( $url ) {
		$item = new \SmartCrawl\Sitemaps\General\Item();

		return $item->set_location( $url );
	}

	/**
	 * Changes post analysis language.
	 *
	 * @param string $post_language Current post language.
	 * @param int    $post_id       Post ID.
	 *
	 * @return string Modified post language.
	 */
	public function change_post_analysis_language( $post_language, $post_id ) {
		$wpml_lang_code = $this->get_post_language_code( $post_id );

		return ! empty( $wpml_lang_code )
			? $wpml_lang_code
			: $post_language;
	}

	/**
	 * We would rather use wpml_get_language_information, but it has internal caching that doesn't get purged the first time a post is saved.
	 *
	 * Results are cached in a static variable to avoid repeated database queries during the same request.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string|null
	 */
	private function get_post_language_code( $post_id ) {
		static $cache = array();

		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return null;
		}

		if ( isset( $cache[ $post_id ] ) ) {
			return $cache[ $post_id ];
		}

		global $wpdb;

		$cache[ $post_id ] = $wpdb->get_var( $wpdb->prepare( "SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d", $post_id ) );

		return $cache[ $post_id ];
	}

	/**
	 * Gets all translation IDs for the homepage page.
	 *
	 * When a static page is set as homepage, this method retrieves all translation IDs
	 * (including the original homepage ID) by querying WPML's translations table directly.
	 *
	 * We use direct database queries instead of WPML filters to avoid caching issues
	 *
	 * @return array Array of homepage translation IDs, or empty array if not applicable.
	 */
	private function get_homepage_translation_ids() {
		static $cached_ids = null;

		if ( null !== $cached_ids ) {
			return $cached_ids;
		}

		// Only fetch if a static page is set as homepage.
		if ( 'page' !== get_option( 'show_on_front' ) ) {
			$cached_ids = array();
			return $cached_ids;
		}

		$homepage_id = (int) get_option( 'page_on_front' );
		if ( ! $homepage_id ) {
			$cached_ids = array();
			return $cached_ids;
		}

		// Get all translations of the homepage page by querying WPML translations table directly.
		$translation_ids = array( $homepage_id );
		global $wpdb;
		$trid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type = 'post_page'",
				$homepage_id
			)
		);

		if ( $trid ) {
			$all_translations = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid = %d AND element_type = 'post_page'",
					$trid
				)
			);

			if ( ! empty( $all_translations ) ) {
				$translation_ids = array_map( 'intval', $all_translations );
			}
		}

		$cached_ids = $translation_ids;

		return $cached_ids;
	}

	/**
	 * Excludes homepage page and all its translations from the page sitemap.
	 *
	 * When a static page is set as homepage and WPML is active, the homepage page
	 * and its translations are included in the page query results. This causes duplicates
	 * because WPML's add_homepage_versions already adds homepage URLs for each language.
	 *
	 * @param array $ignored_ids Array of page IDs to ignore.
	 *
	 * @return array Modified array with homepage page and its translations excluded.
	 */
	public function exclude_homepage_translations( $ignored_ids ) {
		$translation_ids = $this->get_homepage_translation_ids();

		if ( empty( $translation_ids ) ) {
			return $ignored_ids;
		}

		// Merge with existing ignored IDs.
		$ignored_ids = empty( $ignored_ids ) || ! is_array( $ignored_ids )
			? array()
			: $ignored_ids;

		return array_unique( array_merge( $ignored_ids, $translation_ids ) );
	}
}
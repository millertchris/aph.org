<?php
/**
 * The admin view functionality class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Modules
 */

namespace WPMUDEV_Videos\Core\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Base;
use WPMUDEV_Videos\Core\Controllers\Menu;
use WPMUDEV_Videos\Core\Controllers\Assets;

/**
 * Class Admin
 *
 * @package WPMUDEV_Videos\Core\Views
 */
class Admin extends Base {

	/**
	 * Initialize the admin view.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Add SUI class.
		add_filter( 'admin_body_class', array( $this, 'admin_body_classes' ) );

		// Script vars.
		add_filter( 'wpmudev_vids_assets_module_vars_wpmudev-videos-settings', array( $this, 'settings_vars' ) );
		add_filter( 'wpmudev_vids_assets_module_vars_wpmudev-videos-videos', array( $this, 'videos_vars' ) );
		add_filter( 'wpmudev_vids_assets_module_vars_wpmudev-videos-playlists', array( $this, 'playlists_vars' ) );
		add_filter( 'wpmudev_vids_assets_common_vars', array( $this, 'blocks_vars' ) );
		add_filter( 'wpmudev_vids_assets_common_vars', array( $this, 'common_vars' ) );

		// Add plugin action links.
		add_filter(
			'plugin_action_links_' . plugin_basename( WPMUDEV_VIDEOS_FILE ),
			array(
				$this,
				'action_links',
			)
		);

		// Add plugin action links to network admin.
		add_filter(
			'network_admin_plugin_action_links_' . plugin_basename( WPMUDEV_VIDEOS_FILE ),
			array(
				$this,
				'action_links',
			)
		);

		// Add links next to network admin plugin details.
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Add custom admin body class for SUI.
	 *
	 * @param string $classes Admin body class.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function admin_body_classes( $classes ) {
		// Set our custom body class.
		$classes .= ' sui-ivt-admin';

		// Only within our admin page.
		if ( Helpers\General::is_plugin_page() ) {
			// Shared UI.
			$classes .= ' sui-' . str_replace( '.', '-', WPMUDEV_VIDEOS_SUI_VERSION ) . ' ';
		}

		return $classes;
	}

	/**
	 * Admin settings page view.
	 *
	 * Render admin settings page template with all sections.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function video_tutorials() {
		// Video tutorials page.
		echo '<div id="wpmudev-videos-tutorials-app"></div>';

		Assets::get()->enqueue_style( 'wpmudev-videos-tutorials' );
		Assets::get()->enqueue_script( 'wpmudev-videos-tutorials' );
	}

	/**
	 * Admin settings page view.
	 *
	 * Render admin settings page template with all sections.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function dashboard() {
		// Dashboard page.
		echo '<div id="wpmudev-videos-dashboard-app"></div>';

		Assets::get()->enqueue_style( 'wpmudev-videos-dashboard' );
		Assets::get()->enqueue_script( 'wpmudev-videos-dashboard' );
	}

	/**
	 * Admin settings page view.
	 *
	 * Render admin settings page template with all sections.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function videos() {
		// Videos page.
		echo '<div id="wpmudev-videos-videos-app"></div>';

		Assets::get()->enqueue_style( 'wpmudev-videos-videos' );
		Assets::get()->enqueue_script( 'wpmudev-videos-videos' );

		// Setup media libraries.
		wp_enqueue_media();
	}

	/**
	 * Admin settings page view.
	 *
	 * Render admin settings page template with all sections.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function playlists() {
		// Playlist page.
		echo '<div id="wpmudev-videos-playlists-app"></div>';

		Assets::get()->enqueue_style( 'wpmudev-videos-playlists' );
		Assets::get()->enqueue_script( 'wpmudev-videos-playlists' );

		// Setup media libraries.
		wp_enqueue_media();
	}

	/**
	 * Admin settings page view.
	 *
	 * Render admin settings page template with all sections.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function settings() {
		// Settings page.
		echo '<div id="wpmudev-videos-settings-app"></div>';

		Assets::get()->enqueue_style( 'wpmudev-videos-settings' );
		Assets::get()->enqueue_script( 'wpmudev-videos-settings' );
	}

	/**
	 * Set localized script vars for the assets.
	 *
	 * This is the common vars available in all scripts.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function common_vars( $vars ) {
		/* translators: %s: heart icon */
		$footer_text  = sprintf( __( 'Made with %s by WPMU DEV', 'wpmudev_vids' ), '<i class="sui-icon-heart"></i>' );
		$custom_image = apply_filters( 'wpmudev_branding_hero_image', '' );
		$whitelabled  = apply_filters( 'wpmudev_branding_hide_branding', false );

		// Settings data.
		$vars['settings']  = Helpers\Settings::get_with_default();
		$vars['user_name'] = Helpers\General::get_user_name();

		// White labelling.
		$vars['whitelabel'] = array(
			'hide_branding' => apply_filters( 'wpmudev_branding_hide_branding', false ),
			'hide_doc_link' => apply_filters( 'wpmudev_branding_hide_doc_link', false ),
			'footer_text'   => apply_filters( 'wpmudev_branding_footer_text', $footer_text ),
			'custom_image'  => $custom_image,
			'is_unbranded'  => empty( $custom_image ) && $whitelabled,
			'is_rebranded'  => ! empty( $custom_image ) && $whitelabled,
		);

		// Rest data.
		$vars['rest'] = array(
			'base'  => rest_url( 'wpmudev-videos/v1/' ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		);

		// Urls.
		$vars['urls'] = array(
			'base'         => WPMUDEV_VIDEOS_URL,
			'dash_login'   => class_exists( 'WPMUDEV_Dashboard' ) ? \WPMUDEV_Dashboard::$ui->page_urls->dashboard_url : '',
			'dash_install' => 'https://wpmudev.com/project/wpmu-dev-dashboard/',
			'tutorials'    => Helpers\General::url( 'tutorials' ),
			'dashboard'    => Helpers\General::url( 'dashboard' ),
			'videos'       => Helpers\General::url( 'videos' ),
			'playlists'    => Helpers\General::url( 'playlists' ),
			'settings'     => Helpers\General::url( 'settings' ),
			'plugins'      => network_admin_url( 'plugins.php' ),
		);

		// Flags.
		$vars['flags'] = array(
			'network'   => (int) is_network_admin(),
			'multisite' => (int) is_multisite(),
		);

		// Get API key.
		$api_key = Helpers\General::api_key();

		// Membership data.
		$vars['membership'] = array(
			'status'         => Helpers\General::membership_status(),
			'valid'          => (int) Helpers\General::is_valid_member(),
			'dash_active'    => (int) class_exists( 'WPMUDEV_Dashboard' ),
			'dash_connected' => (int) ( class_exists( 'WPMUDEV_Dashboard' ) && ! empty( $api_key ) ),
			'dash_installed' => (int) file_exists( trailingslashit( WP_PLUGIN_DIR ) . 'wpmudev-updates/update-notifications.php' ),
		);

		return $vars;
	}

	/**
	 * Set localized script vars for the settings script.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function settings_vars( $vars ) {
		$vars['roles'] = Helpers\Data::get_roles( false );
		// Menu locations.
		$vars['menu_locations'] = array(
			'dashboard' => __( 'Dashboard', 'wpmudev_vids' ),
			'top'       => __( 'Top Level', 'wpmudev_vids' ),
		);

		// Export nonce.
		$vars['export_nonce'] = wp_create_nonce( 'ivt-export' );

		// Include support system.
		if ( function_exists( 'incsub_support' ) ) {
			$vars['menu_locations']['support_system'] = __( 'Support System Plugin', 'wpmudev_vids' );
		}

		return $vars;
	}

	/**
	 * Set localized script vars for the videos script.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function videos_vars( $vars ) {
		$vars['hosts'] = Helpers\Data::custom_hosts();

		return $vars;
	}

	/**
	 * Set localized script vars for the playlists script.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function playlists_vars( $vars ) {
		$vars['roles']     = Helpers\Data::get_roles( false );
		$vars['locations'] = Helpers\Data::video_pages();

		return $vars;
	}

	/**
	 * Set localized script vars for the playlists script.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function blocks_vars( $vars ) {
		$vars['videos_menu_title'] = Menu::get()->videos_menu_title();

		return $vars;
	}

	/**
	 * Action links for plugins listing page.
	 *
	 * Add quick links to plugin settings page, docs page, upgrade page
	 * from the plugins listing page.
	 *
	 * @param array $links Links array.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function action_links( $links ) {
		// Added a fix for weird warning in multisite, "array_unshift() expects parameter 1 to be array, null given".
		$links = empty( $links ) ? array() : $links;

		// Common links.
		$custom = array(
			'settings' => '<a href="' . Helpers\General::url( 'dashboard' ) . '" aria-label="' . esc_html__( 'Dashboard', 'wpmudev_vids' ) . '">' . esc_html__( 'Dashboard', 'wpmudev_vids' ) . '</a>',
			'docs'     => '<a href="https://wpmudev.com/docs/wpmu-dev-plugins/integrated-video-tutorials/?utm_source=integrated_video_tutorials&utm_medium=plugin&utm_campaign=integrated_video_tutorials_pluginlist_docs" aria-label="' . esc_html__( 'Documentation', 'wpmudev_vids' ) . '" target="_blank">' . esc_html__( 'Docs', 'wpmudev_vids' ) . '</a>',
		);

		// Get the membership status.
		$valid  = Helpers\General::is_valid_member();
		$status = Helpers\General::membership_status();

		// If expired or membership is free.
		if ( in_array( $status, array( 'expired', '' ), true ) && ! $valid ) {
			// Show renew link.
			$custom['renew'] = '<a href="https://wpmudev.com/project/unbranded-video-tutorials/?utm_source=integrated_video_tutorials&utm_medium=plugin&utm_campaign=integrated_video_tutorials_pluginlist_renew" aria-label="' . esc_html__( 'Renew Your Membership', 'wpmudev_vids' ) . '" target="_blank" style="color: #8D00B1;">' . esc_html__( 'Renew Membership', 'wpmudev_vids' ) . '</a>';
		} elseif ( ! $valid ) {
			// Show upgrade link.
			$custom['upgrade'] = '<a href="https://wpmudev.com/project/unbranded-video-tutorials/?utm_source=beehive&utm_medium=plugin&utm_campaign=integrated_video_tutorials_pluginlist_upgrade" aria-label="' . esc_attr( __( 'Upgrade', 'wpmudev_vids' ) ) . '" target="_blank" style="color: #8D00B1;">' . esc_html__( 'Upgrade', 'wpmudev_vids' ) . '</a>';
		}

		// Merge custom links to first.
		return array_merge( $custom, $links );
	}

	/**
	 * Add custom links to support and roadmap next to plugin meta.
	 *
	 * @param array  $links Current links.
	 * @param string $file  Plugin base name.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		// Make sure the links are array.
		$links = empty( $links ) ? array() : $links;

		// Only for our plugin.
		if ( plugin_basename( WPMUDEV_VIDEOS_FILE ) === $file ) {
			// Replace view plugin site link.
			if ( isset( $links[2] ) ) {
				$links[2] = '<a href="https://wpmudev.com/project/unbranded-video-tutorials/" target="_blank">' . esc_html__( 'View Details', 'wpmudev_vids' ) . '</a>';
			}

			$custom = array(
				'support' => '<a href="https://wpmudev.com/get-support/" aria-label="' . esc_html__( 'Get Premium Support', 'wpmudev_vids' ) . '" target="_blank">' . esc_html__( 'Premium Support', 'wpmudev_vids' ) . '</a>',
				'roadmap' => '<a href="https://wpmudev.com/roadmap/" aria-label="' . esc_html__( 'View our Public Roadmap', 'wpmudev_vids' ) . '" target="_blank">' . esc_html__( 'Roadmap', 'wpmudev_vids' ) . '</a>',
			);

			// Add our custom links.
			$links = array_merge( $links, $custom );
		}

		return $links;
	}

	/**
	 * Get plugin menu icon data.
	 *
	 * Get svg image instead of an image url.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		ob_start();
		?>
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M20 10C20 11.8069 19.5327 13.4891 18.5981 15.0467C17.7259 16.5421 16.5109 17.757 14.9533 18.6916C13.4579 19.5639 11.8069 20 10 20C8.19315 20 6.5109 19.5639 4.95327 18.6916C3.45794 17.757 2.24299 16.5421 1.30841 15.0467C0.436137 13.4891 0 11.8069 0 10C0 8.19315 0.436137 6.54206 1.30841 5.04673C2.24299 3.4891 3.45794 2.27414 4.95327 1.40187C6.5109 0.46729 8.19315 0 10 0C11.8069 0 13.4579 0.46729 14.9533 1.40187C16.5109 2.27414 17.7259 3.4891 18.5981 5.04673C19.5327 6.54206 20 8.19315 20 10ZM17.9439 10C17.9439 8.50467 17.5389 7.13396 16.729 5.88785C15.9813 4.57944 14.9221 3.58255 13.5514 2.8972C12.243 2.21184 10.8411 1.93146 9.34579 2.05607C7.85047 2.18069 6.47975 2.67913 5.23364 3.5514L17.9439 10.9346V10ZM10 17.9439C11.6199 17.9439 13.1153 17.5078 14.486 16.6355C15.8567 15.7009 16.8536 14.4548 17.4766 12.8972L14.6729 11.3084L5.51402 16.5421C6.82243 17.4766 8.31776 17.9439 10 17.9439ZM3.92523 15.1402L7.00935 13.3645V6.91589L3.73832 5.04673C2.61682 6.47975 2.05607 8.13084 2.05607 10C2.05607 10.9346 2.21184 11.8692 2.52336 12.8037C2.83489 13.676 3.30218 14.4548 3.92523 15.1402ZM8.97196 12.243L12.7103 10.1869L8.97196 8.03738V12.243Z" fill="#A7AAAD"/>
		</svg>
		<?php
		$svg = ob_get_clean();

		// phpcs:ignore
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}
}
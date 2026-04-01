<?php
/**
 * Class that handles creation of custom posts and taxonomies.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Modules
 */

namespace WPMUDEV_Videos\Core\Modules;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Abstracts\Base;
use WPMUDEV_Videos\Core\Helpers\Settings;

/**
 * Class Content
 *
 * @package WPMUDEV_Videos\Core\Modules
 */
class Content extends Base {

	/**
	 * Property to hold temporary terms.
	 *
	 * @var array $relations
	 *
	 * @since 1.8.0
	 */
	private $relations = array();

	/**
	 * Temporary data that holds details of completed items.
	 *
	 * @var array $finished
	 *
	 * @since 1.8.0
	 */
	private $finished = array();

	/**
	 * Initialize the class.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Process the content creation.
		add_action( 'init', array( $this, 'setup_content' ), 999 );
	}

	/**
	 * Setup videos and playlist if required.
	 *
	 * Do not create duplicated by running the loop always.
	 * Check if the completion flag is already set.
	 *
	 * @since 1.8.0
	 */
	public function setup_content() {
		// Only in main site if multisite.
		if ( is_main_site() ) {
			// If inbuilt videos are not yet created, do now.
			if ( false === (bool) Settings::get( 'default_contents', false ) ) {
				$this->setup_videos();
			}
		}
	}

	/**
	 * Process the video and playlist creation.
	 *
	 * @since 1.8.0
	 * @todo  See if we can do better batch processing.
	 *
	 * @return void
	 */
	public function setup_videos() {
		// Only in main site if multisite.
		if ( is_main_site() ) {
			// Create playlists first.
			if ( $this->create_playlists() ) {
				// If success, create videos.
				if ( $this->create_videos() ) {
					// Now finish the process.
					$this->finish();
				}
			}
		}
	}

	/**
	 * Get the list of playlists to create terms.
	 *
	 * These are the categories we had in previous version
	 * of the plugin. We are merging that with custom videos
	 * to manage in one place.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	private function get_playlists() {
		return array(
			'dashboard'  => array(
				__( 'The Dashboard', 'wpmudev_vids' ),
				array( 'dashboard', 'user', 'profile' ),
				__( 'Learn how to navigate, configure and customize the WordPress dashboard.', 'wpmudev_vids' ),
			),
			'posts'      => array(
				__( 'Posts', 'wpmudev_vids' ),
				array( 'edit-post', 'edit-page', 'post', 'page' ),
				__( 'Learn how to create and manage posts, from Add New to archives and everything in between.', 'wpmudev_vids' ),
			),
			'pages'      => array(
				__( 'Pages', 'wpmudev_vids' ),
				array( 'edit-page', 'edit-post' ),
				__( 'Learn how to create and manage pages, from Add New to archives and everything in between.', 'wpmudev_vids' ),
			),
			'gutenberg'  => array(
				__( 'The Gutenberg Editor', 'wpmudev_vids' ),
				array( 'gutenberg-editor' ),
				__( 'Learn how to use the WordPress Gutenberg editor to design high-quality pages and posts.', 'wpmudev_vids' ),
			),
			'editor'     => array(
				__( 'The Classic Editor', 'wpmudev_vids' ),
				array( 'post', 'page' ),
				__( 'Learn how to use the classic editor to design high-quality pages and posts.', 'wpmudev_vids' ),
			),
			'images'     => array(
				__( 'Working With Images', 'wpmudev_vids' ),
				array( 'post', 'page' ),
				__( 'Learn how to work with images in WordPress, including multiple ways to upload and add images to pages or posts, creating image galleries and addressing common image-related issues.', 'wpmudev_vids' ),
			),
			'media'      => array(
				__( 'Media Library', 'wpmudev_vids' ),
				array( 'upload', 'media', 'post', 'page' ),
				__( 'Learn how to add, remove, configure, customize, secure and manage all the media uploaded to your site or network of sites.', 'wpmudev_vids' ),
			),
			'appearance' => array(
				__( 'Appearance', 'wpmudev_vids' ),
				array( 'themes', 'widgets', 'nav-menus' ),
				__( 'Learn how to configure and customize a site\'s design, including working with themes, widgets and menus.', 'wpmudev_vids' ),
			),
			'organizing' => array(
				__( 'Organizing Content', 'wpmudev_vids' ),
				array( 'edit-category', 'edit-post_tag' ),
				__( 'Learn how to use tags and categories to organize site content.', 'wpmudev_vids' ),
			),
			'comments'   => array(
				__( 'Managing Comments', 'wpmudev_vids' ),
				array( 'edit-comments' ),
				__( 'Learn how to organize user comments, including creating and moderating comments, addressing spam and archiving old comments.', 'wpmudev_vids' ),
			),
			'other'      => array(
				__( 'Users, Tools, and Settings', 'wpmudev_vids' ),
				array(
					'users',
					'user',
					'profile',
					'user-edit',
					'tools',
					'import',
					'export',
					'options-general',
					'options-writing',
					'options-reading',
					'options-discussion',
					'options-media',
					'options-permalink',
					'update-core',
					'theme-install',
					'plugin-install',
				),
				__( 'Learn how to, among other things, manage users, configure admin settings, back up a site or restore a site from backup.', 'wpmudev_vids' ),
			),
		);
	}

	/**
	 * Get the list of videos to create custom posts.
	 *
	 * These are the videos used in a hard-coded array in previous
	 * versions of the plugin.
	 * We will create custom post for inbuilt videos also, so that
	 * we can search, view and manage videos along with custom videos
	 * in one place.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	private function get_videos() {
		return array(
			'add-heading'                  => array( __( 'Heading Styles', 'wpmudev_vids' ), 54 ),
			'add-image-from-media-library' => array( __( 'Adding Images From Media Library', 'wpmudev_vids' ), 37 ),
			'add-image-from-pc'            => array( __( 'Uploading Images', 'wpmudev_vids' ), 37 ),
			'add-image-from-url'           => array( __( 'Add Image From URL', 'wpmudev_vids' ), 71 ),
			'image-gallery'                => array( __( 'Image Galleries', 'wpmudev_vids' ), 80 ),
			'add-media'                    => array( __( 'Adding Media', 'wpmudev_vids' ), 45 ),
			'add-new-page'                 => array( __( 'Adding New Pages', 'wpmudev_vids' ), 47 ),
			'add-new-post'                 => array( __( 'Adding New Posts', 'wpmudev_vids' ), 59 ),
			'add-paragraph'                => array( __( 'Using Paragraphs', 'wpmudev_vids' ), 38 ),
			'admin-bar'                    => array( __( 'The Admin Bar', 'wpmudev_vids' ), 81 ),
			'categories'                   => array( __( 'Categories', 'wpmudev_vids' ), 49 ),
			'change-password'              => array( __( 'Changing Your Password', 'wpmudev_vids' ), 27 ),
			'comments'                     => array( __( 'Managing Comments', 'wpmudev_vids' ), 77 ),
			'dashboard'                    => array( __( 'The Dashboard', 'wpmudev_vids' ), 160 ),
			'delete-image'                 => array( __( 'Deleting Images', 'wpmudev_vids' ), 23 ),
			'edit-image'                   => array( __( 'Editing Images', 'wpmudev_vids' ), 41 ),
			'edit-text'                    => array( __( 'Editing Text', 'wpmudev_vids' ), 71 ),
			'excerpt'                      => array( __( 'Post Excerpts', 'wpmudev_vids' ), 102 ),
			'featured-image'               => array( __( 'Set Featured Image', 'wpmudev_vids' ), 41 ),
			'hyperlinks'                   => array( __( 'Hyperlinks', 'wpmudev_vids' ), 60 ),
			'image-editor'                 => array( __( 'The Image Editor', 'wpmudev_vids' ), 84 ),
			'lists'                        => array( __( 'Lists', 'wpmudev_vids' ), 47 ),
			'media-library'                => array( __( 'The Media Library', 'wpmudev_vids' ), 87 ),
			'oEmbed'                       => array( __( 'Embed Videos', 'wpmudev_vids' ), 73 ),
			'quickpress'                   => array( __( 'Quick Draft', 'wpmudev_vids' ), 35 ),
			'replace-image'                => array( __( 'Replace an Image', 'wpmudev_vids' ), 23 ),
			'restore-page'                 => array( __( 'Restoring Pages', 'wpmudev_vids' ), 40 ),
			'restore-post'                 => array( __( 'Restoring Posts', 'wpmudev_vids' ), 40 ),
			'revisions'                    => array( __( 'Revisions', 'wpmudev_vids' ), 118 ),
			'pages-v-posts'                => array( __( 'Pages vs. Posts', 'wpmudev_vids' ), 127 ),
			'tags'                         => array( __( 'Using Tags', 'wpmudev_vids' ), 49 ),
			'the-toolbar'                  => array( __( 'The Toolbar', 'wpmudev_vids' ), 151 ),
			'trash-post'                   => array( __( 'Using Trash', 'wpmudev_vids' ), 60 ),
			'widgets'                      => array( __( 'Managing Widgets', 'wpmudev_vids' ), 82 ),
			'menus'                        => array( __( 'Navigation Menus', 'wpmudev_vids' ), 111 ),
			'change-theme'                 => array( __( 'Change Theme', 'wpmudev_vids' ), 43 ),
			'customize'                    => array( __( 'The Customizer', 'wpmudev_vids' ), 83 ),
			'create-edit-user'             => array( __( 'Create and Edit Users', 'wpmudev_vids' ), 148 ),
			'tools'                        => array( __( 'Tools', 'wpmudev_vids' ), 57 ),
			'settings'                     => array( __( 'Settings', 'wpmudev_vids' ), 124 ),
			'playlists'                    => array( __( 'Creating Playlists', 'wpmudev_vids' ), 65 ),
			'gutenberg-editor-overview'    => array( __( 'Editor Overview', 'wpmudev_vids' ), 122 ),
			'gutenberg-reusable-blocks'    => array( __( 'Reusable Blocks', 'wpmudev_vids' ), 86 ),
			'gutenberg-add-page'           => array( __( 'Adding New Pages (Gutenberg)', 'wpmudev_vids' ), 59 ),
			'gutenberg-add-post'           => array( __( 'Adding New Posts (Gutenberg)', 'wpmudev_vids' ), 59 ),
			'running-updates'              => array( __( 'Running Updates', 'wpmudev_vids' ), 67 ),
			'install-themes'               => array( __( 'Install a Theme', 'wpmudev_vids' ), 96 ),
			'install-plugin'               => array( __( 'Install and Configure a Plugin', 'wpmudev_vids' ), 83 ),
			'import-export'                => array( __( 'Import and Export', 'wpmudev_vids' ), 138 ),
			'site-health'                  => array( __( 'Site Health', 'wpmudev_vids' ), 85 ),
			'export-erase-data'            => array( __( 'Export and Erase Personal Data', 'wpmudev_vids' ), 81 ),
			'paragraphs'                   => array( __( 'Paragraphs', 'wpmudev_vids' ), 40 ),
			'file-blocks'                  => array( __( 'WordPress File Block', 'wpmudev_vids' ), 95 ),
			'google-calendar'              => array( __( 'Embedding a Google Calendar', 'wpmudev_vids' ), 77 ),
		);
	}

	/**
	 * Create taxonomy terms for each playlist.
	 *
	 * We will store the term id in temporary property so that
	 * we don't have to make extra db calls to get the terms after
	 * we create videos.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	private function create_playlists() {
		// Get playlists.
		$playlists = $this->get_playlists();

		// Start with 0.
		$count = 0;

		// Loop through each playlist.
		foreach ( $playlists as $slug => $data ) {
			// Insert term.
			$result = wp_insert_term(
				$data[0],
				Playlists\Models\Playlist::TAXONOMY,
				array(
					'slug'        => $slug,
					'description' => $data[2],
				)
			);

			// If term created.
			if ( ! is_wp_error( $result ) && isset( $result['term_id'] ) ) {
				// Add locations data.
				add_term_meta( $result['term_id'], 'playlist_locations', $data[1] );
				// Add roles data.
				add_term_meta(
					$result['term_id'],
					'playlist_roles',
					array(
						'editor',
						'author',
						'contributor',
					)
				);

				// Add position.
				add_term_meta( $result['term_id'], 'playlist_order', $count );

				$this->relations[ $slug ] = $result['term_id'];

				// Set order.
				$count = ++$count;
			}
		}

		return true;
	}

	/**
	 * Get the connection between playlists and videos.
	 *
	 * Array of playlist for each videos.
	 * key - Video slug.
	 * value - Array of playlist slugs.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	private function get_relationships() {
		return array(
			'add-heading'                  => array( 'editor' ),
			'add-image-from-media-library' => array( 'images' ),
			'add-image-from-pc'            => array( 'images' ),
			'add-image-from-url'           => array( 'images' ),
			'image-gallery'                => array( 'images' ),
			'add-media'                    => array( 'media' ),
			'add-new-page'                 => array( 'pages' ),
			'add-new-post'                 => array( 'posts' ),
			'add-paragraph'                => array( 'editor' ),
			'admin-bar'                    => array( 'dashboard' ),
			'categories'                   => array( 'organizing' ),
			'change-password'              => array( 'dashboard' ),
			'comments'                     => array( 'comments' ),
			'dashboard'                    => array( 'dashboard' ),
			'delete-image'                 => array( 'images' ),
			'edit-image'                   => array( 'images' ),
			'edit-text'                    => array( 'editor' ),
			'excerpt'                      => array( 'editor' ),
			'featured-image'               => array( 'images' ),
			'hyperlinks'                   => array( 'editor' ),
			'image-editor'                 => array( 'media' ),
			'lists'                        => array( 'editor' ),
			'media-library'                => array( 'media' ),
			'oEmbed'                       => array( 'editor' ),
			'quickpress'                   => array( 'dashboard' ),
			'replace-image'                => array( 'images' ),
			'restore-page'                 => array( 'pages' ),
			'restore-post'                 => array( 'posts' ),
			'revisions'                    => array( 'posts' ),
			'pages-v-posts'                => array( 'pages' ),
			'tags'                         => array( 'organizing' ),
			'the-toolbar'                  => array( 'editor' ),
			'trash-post'                   => array( 'posts', 'pages' ),
			'widgets'                      => array( 'appearance' ),
			'menus'                        => array( 'appearance' ),
			'change-theme'                 => array( 'appearance' ),
			'customize'                    => array( 'appearance' ),
			'create-edit-user'             => array( 'other' ),
			'tools'                        => array( 'other' ),
			'settings'                     => array( 'other' ),
			'playlists'                    => array( 'editor' ),
			'gutenberg-editor-overview'    => array( 'gutenberg' ),
			'gutenberg-reusable-blocks'    => array( 'gutenberg' ),
			'gutenberg-add-page'           => array( 'pages' ),
			'gutenberg-add-post'           => array( 'posts' ),
			'running-updates'              => array( 'other' ),
			'install-themes'               => array( 'other' ),
			'install-plugin'               => array( 'other' ),
			'import-export'                => array( 'other' ),
			'site-health'                  => array( 'other' ),
			'export-erase-data'            => array( 'other' ),
			'paragraphs'                   => array( 'editor', 'gutenberg' ),
			'file-blocks'                  => array( 'gutenberg' ),
			'google-calendar'              => array( 'gutenberg' ),
		);
	}

	/**
	 * Create custom posts for the inbuilt videos.
	 *
	 * Link taxonomy terms created for the playlist with the video.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	private function create_videos() {
		// Get videos.
		$videos = $this->get_videos();

		// Loop through each video.
		foreach ( $videos as $slug => $data ) {
			// Check if video already exist.
			$video = $this->video_by_slug( $slug );

			// Insert video post.
			if ( empty( $video ) ) {
				$video = wp_insert_post(
					array(
						'post_title'     => $data[0], // Title.
						'post_status'    => 'publish',
						'post_type'      => Videos\Models\Video::POST_TYPE,
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
						'post_name'      => $slug,
						'meta_input'     => array(
							'video_type'     => 'default',
							'video_duration' => $data[1], // Duration.
						),
					)
				);
			}

			// If video is created, link the terms.
			if ( $video ) {
				$this->link_playlists( $video, $slug );

				// Add to finished list.
				$this->finished[] = $video;
			}
		}

		return true;
	}

	/**
	 * Link the playlist terms for the video with post.
	 *
	 * @param int    $video Video ID.
	 * @param string $slug  Video slug.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	private function link_playlists( $video, $slug ) {
		// Get the relationships.
		$relations = $this->get_relationships();

		// Continue only if relation found.
		if ( empty( $relations[ $slug ] ) ) {
			return false;
		}

		$terms = array();

		// Include terms if found in temporary cache.
		foreach ( $relations[ $slug ] as $term_slug ) {
			if ( isset( $this->relations[ $term_slug ] ) ) {
				$terms[] = $this->relations[ $term_slug ];
			}
		}

		// Setup playlist for the video.
		return Playlists\Controller::get()->link_playlists_to_video( $terms, $video );
	}

	/**
	 * Link the playlist terms for the video with post.
	 *
	 * @since 1.8.0
	 */
	private function finish() {
		$videos = $this->get_videos();

		// If all videos are finished, set the flag.
		if ( count( $this->finished ) === count( $videos ) ) {
			Settings::set( 'default_contents', 1 );
		}

		/**
		 * Action hook to run after default content is created.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_default_content_finished' );
	}

	/**
	 * Get existing video ID using slug.
	 *
	 * We should not create duplicate video posts
	 * if one already exist.
	 *
	 * @param string $slug Video post slug.
	 *
	 * @since 1.8.0
	 *
	 * @return bool|int
	 */
	public function video_by_slug( $slug ) {
		global $wpdb;

		// Run query and get video ID.
		// phpcs:ignore
		$video = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type='%s' AND post_name='%s'", // phpcs:ignore
				Videos\Models\Video::POST_TYPE,
				$slug
			)
		);

		return empty( $video ) ? false : $video;
	}

	/**
	 * Delete all videos and playlists.
	 *
	 * DO NOT USE IT unless you want to start from scratch.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function clean_all() {
		// Get all videos.
		$videos = Videos\Models\Query::get()->videos(
			array(
				'numberposts' => - 1,
			)
		);

		if ( ! empty( $videos ) ) {
			foreach ( $videos as $key => $id ) {
				// Force delete video and meta.
				wp_delete_post( $id, true );
			}
		}

		// Get all playlist.
		$playlists = Playlists\Models\Query::get()->playlists(
			array(
				'show_all' => true,
				'orderby'  => 'term_id',
				// phpcs:ignore
				'meta_key' => '',
			)
		);

		if ( ! empty( $playlists ) ) {
			foreach ( $playlists as $key => $id ) {
				// Force delete playlist and meta.
				wp_delete_term( $id, Playlists\Models\Playlist::TAXONOMY );
			}
		}
	}
}
<?php
/**
 * Template: Indexing Settings.
 *
 * @package Smartcrwal
 */

namespace SmartCrawl;

use SmartCrawl\Services\Service;

$active_tab               = empty( $active_tab ) ? '' : $active_tab;
$instant_indexing_enabled = Settings::get_setting( 'instant_indexing' );

$service   = Service::get( Service::SERVICE_SITE );
$is_member = $service->is_member();

?>
<?php $this->render_view( 'before-page-container' ); ?>
<div id="container" class="<?php \smartcrawl_wrap_class( 'wds-page-instant-indexing' ); ?>">
	<?php
	$this->render_view(
		'page-header',
		array(
			'title'                 => esc_html__( 'Instant Indexing', 'wds' ),
			'documentation_chapter' => 'instant-indexing',
			'utm_campaign'          => 'smartcrawl_instant-indexing_docs',
		)
	);
	$this->render_view( 'floating-notices', array(
		'keys' => array(
			'wds-url-manually-updated',
			'wds-success-message'
		)
	) );
	?>
	<?php if ( ! $is_member || ! $instant_indexing_enabled ) : ?>
		<?php $this->render_view(
			'instant-indexing/instant-indexing-disabled',
			array(
				'is_member' => $is_member,
			)
		); ?>
	<?php else : ?>
		<form action='<?php echo esc_attr( $_view['action_url'] ); ?>' method='post' class="wds-form">
			<?php $this->settings_fields( $_view['option_name'] ); ?>
			<div class="wds-vertical-tabs-container sui-row-with-sidenav" id="page-title-meta-tabs">
				<?php
				$this->render_view(
					'instant-indexing/instant-indexing-sidenav',
					array(
						'active_tab' => $active_tab,
					)
				);

				$this->render_view(
					'vertical-tab',
					array(
						'tab_id'       => 'tab_submit_url',
						'tab_name'     => esc_html__( 'Manual URL Submission', 'wds' ),
						'is_active'    => 'tab_submit_url' === $active_tab,
						'tab_sections' => array(
							array(
								'section_template' => 'instant-indexing/instant-indexing-submit-url',
								'section_args'     => array(),
							),
						),
						'button_text'  => '',
					)
				);
				$this->render_view(
					'vertical-tab',
					array(
						'tab_id'       => 'tab_submission_history',
						'tab_name'     => esc_html__( 'Submission History', 'wds' ),
						'is_active'    => 'tab_submission_history' === $active_tab,
						'tab_sections' => array(
							array(
								'section_template' => 'instant-indexing/instant-indexing-submission-history',
								'section_args'     => array(),
							),
						),
						'button_text'  => '',
					)
				);
				$this->render_view(
					'vertical-tab',
					array(
						'tab_id'       => 'tab_settings',
						'tab_name'     => esc_html__( 'Settings', 'wds' ),
						'is_active'    => 'tab_settings' === $active_tab,
						'tab_sections' => array(
							array(
								'section_template' => 'instant-indexing/instant-indexing-settings',
								'section_args'     => array(),
							),
						),
					)
				);
				?>
			</div>
		</form>
	<?php endif; ?>
	<?php $this->render_view( 'footer' ); ?>
</div>
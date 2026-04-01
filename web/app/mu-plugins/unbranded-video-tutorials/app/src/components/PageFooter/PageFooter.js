import { __ } from '@wordpress/i18n'
import { Footer } from '../../lib/components'

export function PageFooter() {
	// Global Variables
	const {
		whitelabel: { footer_text, hide_branding },
	} = window.ivtVars ?? {}
	return (
		<Footer
			footer_text={footer_text}
			hide_branding={hide_branding}
			social_links={[
				{
					name: 'facebook',
					text: __('Facebook', 'wpmudev_vids'),
					href: 'https://www.facebook.com/wpmudev',
					icon: 'social-facebook',
				},
				{
					name: 'twitter',
					text: __('Twitter', 'wpmudev_vids'),
					href: 'https://twitter.com/wpmudev',
					icon: 'social-twitter',
				},
				{
					name: 'instagram',
					text: __('Instagram', 'wpmudev_vids'),
					href: 'https://www.instagram.com/wpmu_dev',
					icon: 'instagram',
				},
			]}
			links={[
				{
					name: 'hub',
					text: __('The Hub', 'wpmudev_vids'),
					href: 'https://wpmudev.com/hub2/',
				},
				{
					name: 'plugins',
					text: __('Plugins', 'wpmudev_vids'),
					href: 'https://wpmudev.com/projects/category/plugins/',
				},
				{
					name: 'roadmap',
					text: __('Roadmap', 'wpmudev_vids'),
					href: 'https://wpmudev.com/roadmap/',
				},
				{
					name: 'support',
					text: __('Support', 'wpmudev_vids'),
					href: 'https://wpmudev.com/hub/support',
				},
				{
					name: 'docs',
					text: __('Docs', 'wpmudev_vids'),
					href: 'https://wpmudev.com/docs/',
				},
				{
					name: 'community',
					text: __('Community', 'wpmudev_vids'),
					href: 'https://wpmudev.com/hub2/community',
				},
				{
					name: 'terms_of_service',
					text: __('Terms of Service', 'wpmudev_vids'),
					href: 'https://wpmudev.com/terms-of-service/',
				},
				{
					name: 'privacy_policy',
					text: __('Privacy Policy', 'wpmudev_vids'),
					href: 'https://incsub.com/privacy-policy/',
				},
			]}
		/>
	)
}

export default PageFooter

import PropTypes from 'prop-types'
import { Icon } from '../'
import { social_links, defaultLinks } from './defaults'
import { Interweave } from 'interweave'

export function Footer({ footer_text, links, hide_branding, social_links }) {
	return (
		<>
			<div className="sui-footer">
				<Interweave content={footer_text} />
			</div>
			{!hide_branding && (
				<>
					<ul className="sui-footer-nav">
						{links.map((link) => (
							<li key={link.name}>
								<a href={link.href} target="_blank">
									{link.text}
								</a>
							</li>
						))}
					</ul>
					<ul className="sui-footer-social">
						{social_links.map((link) => (
							<li key={link.name}>
								<a href={link.href} target="_blank">
									<Icon icon={link.icon} />
									<span className="sui-screen-reader-text">
										{link.text}
									</span>
								</a>
							</li>
						))}
					</ul>
				</>
			)}
		</>
	)
}

Footer.defaultProps = {
	footer_text: '$ Here Goes the footer html text',
	hide_branding: false,
	links: defaultLinks,
	social_links: social_links,
}

Footer.propTypes = {
	footer_text: PropTypes.string,
	hide_branding: PropTypes.bool,
	links: PropTypes.array,
	social_Links: PropTypes.array,
}

export default Footer

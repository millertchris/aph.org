/**
 * Library dependencies
 */
import { Box } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { ImageTag } from '../../../../components'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { __, sprintf } from '@wordpress/i18n'
import { Interweave } from 'interweave'

export function EmptyBox({ search }) {
	const { hide_branding } = window.ivtVars?.whitelabel ?? {}

	return (
		<Box className={classnames('sui-message', 'sui-message-lg')}>
			{!hide_branding && (
				<ImageTag
					src="message/no-results.png"
					className="sui-image"
					alt={__('Empty results', 'wpmudev_vids')}
				/>
			)}
			<div className="sui-message-content">
				<p>
					<Interweave
						content={sprintf(
							__(
								"We couldn't find any videos matching your search <strong>“%s”</strong>. Please try again"
							),
							search
						)}
					/>
				</p>
			</div>
		</Box>
	)
}

export default EmptyBox

import React from 'react';
import Button from '../button';
import { __ } from '@wordpress/i18n';
import ConfigValues from '../../es6/config-values';

export default class Header extends React.Component {
	static defaultProps = {
		title: '',
		docChapter: '',
		utmCampaign: '',
	};

	render() {
		const { title, docChapter, utmCampaign } = this.props;
		const hideDocLink = ConfigValues.get_bool('hide_doc_link', 'admin');

		return (
			<div className="sui-header">
				<h1 className="sui-header-title">{title}</h1>

				{!!docChapter && !hideDocLink && (
					<div className="sui-actions-right">
						<Button
							href={`https://wpmudev.com/docs/wpmu-dev-plugins/smartcrawl/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=${utmCampaign}#${docChapter}`}
							ghost
							icon="sui-icon-academy"
							target="_blank"
							rel="noreferrer"
							className="wds-docs-button"
							text={__('View Documentation', 'wds')}
						></Button>
					</div>
				)}
			</div>
		);
	}
}

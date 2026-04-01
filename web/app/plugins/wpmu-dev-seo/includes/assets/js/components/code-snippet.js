import React from 'react';
import { __ } from '@wordpress/i18n';
import Button from './button';

export default class CodeSnippet extends React.Component {
	static defaultProps = {
		snippet: '',
		description: null,
		onCopy: () => {},
	};

	render() {
		const { snippet, description, onCopy } = this.props;

		const uuid = `code-snippet-${crypto.randomUUID()}`;
		const codeSnippetId = `#${uuid}`;

		return (
			<div>
				<div className="sui-code-snippet-wrapper wds-code-snippet">
					<pre id={uuid} className="sui-code-snippet">
						{snippet}
					</pre>
					<Button
						type="button"
						icon="sui-icon-copy"
						text={__('Copy', 'wds')}
						onClick={onCopy}
						data-clipboard-target={codeSnippetId}
					/>
				</div>
				{description && (
					<p className="sui-description">{description}</p>
				)}
			</div>
		);
	}
}

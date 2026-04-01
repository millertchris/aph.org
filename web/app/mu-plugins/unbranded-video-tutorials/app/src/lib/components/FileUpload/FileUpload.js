import { Icon } from '../'
import PropTypes from 'prop-types'
import { useRef, useState } from 'react'
import classnames from 'classnames'

export function FileUpload({ label, accept, onChange, removeLabel }) {
	const [file, setFile] = useState(null)

	const inputRef = useRef()

	/**
	 * Handles Button Click
	 */
	const handleClick = () => {
		inputRef?.current?.click()
	}

	/**
	 * Handles File Selection
	 *
	 * @param {Event} event
	 */
	const onSelect = (e) => {
		if (e.target.files.length) {
			// Set the first file.
			setFile(inputRef?.current?.files[0])

			onChange(inputRef?.current?.files[0])
		}
	}

	/**
	 * Removes Currently Selected File
	 */
	const onDelete = () => {
		setFile(null)
		onChange(null)
		if (!!inputRef?.current) {
			inputRef.current.value = null
		}
	}

	const wrapperClasses = classnames({
		'sui-upload': true,
		'sui-has_file': !!file,
	})

	return (
		<div className={wrapperClasses}>
			<input
				type="file"
				readOnly="readonly"
				ref={inputRef}
				onChange={onSelect}
				accept={accept}
			/>
			<button className="sui-upload-button" onClick={handleClick}>
				<Icon icon="upload-cloud" />
				{label}
			</button>

			{/** Remove file */}
			{!!file && (
				<div className="sui-upload-file">
					<span>{file.name}</span>
					<button aria-label={removeLabel} onClick={onDelete}>
						<Icon icon="close" />
					</button>
				</div>
			)}
		</div>
	)
}

FileUpload.defaultProps = {
	label: 'Upload file',
	accept: '*/*',
	onChange: () => null,
	removeLabel: 'Remove',
}

FileUpload.propTypes = {
	label: PropTypes.string,
	accept: PropTypes.string,
	onChange: PropTypes.func,
	removeLabel: PropTypes.string,
}

export default FileUpload

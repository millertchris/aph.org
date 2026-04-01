import classnames from 'classnames'
import PropTypes from 'prop-types'
import { useRef, useEffect, useState } from 'react'
import { defaultOptions } from './defaultValues'

export function Select({
	id,
	value,
	options,
	isSmall,
	onChange,
	className,
	multiple,
	disabled,
	placeholder,
	dropDownClass,
	displaySearchForSingle,
	labelID,
	descriptionID,
	parentElement,
	dataWidth
}) {
	const [selectInstance, setSelectInstance] = useState(null)

	// Select DOM element
	const selectEl = useRef()

	// classnames
	const classes = classnames({
		'sui-select': true,
		[className]: true,
		'sui-select-sm': isSmall,
	})

	useEffect(() => {
		if (selectEl.current) {
			// Initialize select.
			setSelectInstance(jQuery(selectEl?.current))
		}
	}, [selectEl.current])

	useEffect(() => {
		if (selectInstance) {
			const settings = getSettings()

			// Set the settings
			selectInstance.SUIselect2({
				...settings,
				data: options,
			})

			// Handle change event.
			selectInstance.on('select2:select select2:unselect', () =>
				onChange(selectInstance.val())
			)

			// Update Select value with the passed prop
			setValue(value)
		}

		// Remove change Event Listener
		return () => {
			destroySelect2()
		}

		// The event listener needs to reinitialize when onChange prop changes
	}, [selectEl.current, onChange])

	// set value when it changes
	useEffect(() => {
		setValue(value)
	}, [value])

	// // Set options when they change
	// useEffect(() => {
	// 	setOptions(options)
	// }, [options])

	// /**
	//  * Sets the select options data.
	//  *
	//  * @returns {void}
	//  */
	// const setOptions = (options) => {
	// 	if (selectInstance) {
	// 		const settings = getSettings()

	// 		// Empty existing options.
	// 		selectInstance.empty()

	// 		// Re-init with new options.
	// 		selectInstance.SUIselect2({
	// 			...settings,
	// 			data: options,
	// 		})
	// 	}

	// 	// Update the value again.
	// 	setValue(value)
	// }

	/**
	 * Updates the value of the component.
	 *
	 * @returns {void}
	 */
	const setValue = (value) => {
		if (selectInstance) {
			// If value is array.
			if (multiple) {
				if (value instanceof Array) {
					selectInstance.val([...value])
				} else {
					// If empty.
					selectInstance.val([value])
				}
			} else {
				// If single.
				selectInstance.val(value)
			}

			selectInstance.trigger('change')
		}
	}

	/**
	 * Get the settings object.
	 *
	 * @returns {*}
	 */
	const getSettings = () => {
		const settings = {
			multiple: multiple,
			dropDownClass,
			placeholder,
		}

		// Display search field ( works only when multiple is set to false )
		!displaySearchForSingle &&
			(settings['minimumResultsForSearch'] = Infinity)

		!!parentElement &&
			(settings['dropdownParent'] = jQuery(`#${parentElement}`))

		return settings
	}

	/**
	 * Destroy current select2 instance.
	 *
	 * @returns {void}
	 */
	const destroySelect2 = () => {
		if (selectInstance) {
			selectInstance.off().SUIselect2('destroy')
		}
	}

	return (
		<select
			ref={selectEl}
			id={id}
			className={classes}
			multiple={multiple}
			disabled={disabled}
			placeholder={placeholder}
			aria-labelledby={labelID}
			aria-describedby={descriptionID}
			data-width={dataWidth}
		>
			{!!placeholder && <option value="">{placeholder}</option>}
			{Object.keys(options).map((key) => (
				<option value={key} key={key}>
					{options[key]}
				</option>
			))}
		</select>
	)
}

Select.defaultProps = {
	isSmall: false,
	options: defaultOptions,
	className: '',
	onChange: () => null,
	id: '',
	value: '',
	multiple: false,
	disabled: false,
	placeholder: '$ placeholder',
	dropDownClass: '',
	displaySearchForSingle: false,
	labelID: '',
	descriptionID: '',
	parentElement: '',
}

Select.propTypes = {
	isSmall: PropTypes.bool,
	options: PropTypes.object,
	className: PropTypes.string,
	onChange: PropTypes.func,
	id: PropTypes.string,
	value: PropTypes.oneOfType([PropTypes.string, PropTypes.array]),
	multiple: PropTypes.bool,
	disabled: PropTypes.bool,
	placeholder: PropTypes.string,
	dropDownClass: PropTypes.string,
	displaySearchForSingle: PropTypes.bool,
	labelID: PropTypes.string,
	descriptionID: PropTypes.string,
	parentElement: PropTypes.string,
}

export default Select

import { useState } from 'react'
import { restGet } from '../../helpers/api/'
import { useDispatch } from 'react-redux'
import { addNotice } from '../../store/slices/notice'

/**
 * Reset the membership data using API.
 *
 * Reloads the current page after refreshing the
 * status.
 *
 */
export function useResetData({ afterReset }) {
	const [isProcessing, setProcessing] = useState(false)

	const dispatch = useDispatch()

	async function resetData() {
		// Start loading
		setProcessing(true)

		restGet({
			path: 'actions',
			params: {
				action: 'reset_settings',
			},
		}).then((response) => {
			if (response.success) {
				// callback function
				afterReset()

				// Show notice
				dispatch(
					addNotice({
						message: response?.data?.message,
					})
				)

				// Reload after diplaying the notice
				setTimeout(() => window.location.reload(), 1000)
			}

			setProcessing(false)
		})
	}

	return { resetData, isProcessing }
}

export default useResetData

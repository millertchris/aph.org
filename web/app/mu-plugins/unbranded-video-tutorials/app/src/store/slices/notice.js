import { createSlice } from '@reduxjs/toolkit'
import { __ } from '@wordpress/i18n'
import { createSelector } from 'reselect'

/***************************************************
 *
 * Notice Slice
 *
 * *************************************************/
const noticeSlice = createSlice({
	name: 'notice',

	initialState: {
		id: `notice-${Date.now().toString()}`,
		message: '',
		dismiss: true,
		type: 'success',
	},

	/***************************************************
	 *
	 * Reducers
	 *
	 * *************************************************/

	reducers: {
		/**
		 * Adds a notice
		 *
		 * @param {object} action Action.
		 */
		addNotice: (_, action) => {
			// Only if message is provided
			if (action?.payload.hasOwnProperty('message')) {
				let { message, dismiss, type } = action.payload

				if (dismiss === undefined || dismiss === null) dismiss = false
				if (type === undefined || dismiss === null) type = 'success'

				return {
					id: `notice-${Date.now().toString()}`,
					message,
					dismiss,
					type,
				}
			}
		},
	},
})

/***************************************************
 *
 * Selectors
 *
 * *************************************************/

const noticeState = (state) => state.notice

/**
 * Get Notice State object
 *
 * @param {object} noticeState the notice object.
 *
 * @return {object}
 */
export const getCurrentNotice = createSelector(
	noticeState,
	(noticeState) => noticeState
)

// Export actions
export const { addNotice } = noticeSlice.actions

export default noticeSlice.reducer

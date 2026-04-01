import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import { restPost } from '../../helpers/api'
import { __ } from '@wordpress/i18n'
import { createSelector } from 'reselect'

/***************************************************
 *
 * Global Variables
 *
 * *************************************************/
const { settings } = window.ivtVars ?? {}

/***************************************************
 *
 * Asynchronous Requests
 *
 * *************************************************/
/**
 * Save Settings to the db
 *
 * @return {Promise}
 */
export const saveOptions = createAsyncThunk(
	'settings/saveOptions', // Redux action type
	(_, { getState }) => {
		const state = getState()
		return restPost({
			path: 'settings',
			data: {
				value: state.settings.data,
			},
		})
	}
)

/***************************************************
 *
 * Settings Slice
 *
 * *************************************************/
const settingsSlice = createSlice({
	name: 'settings',

	/***************************************************
	 *
	 * Initial State
	 *
	 * *************************************************/
	initialState: { data: settings, status: '' },

	/***************************************************
	 *
	 * Reducers
	 *
	 * *************************************************/
	reducers: {
		/**
		 * Set a single option value after validation.
		 *
		 * Make sure all required items are provided
		 *
		 * @param state current state.
		 * @param {object} action Action.
		 */
		setOption: (state, action) => {
			// Only if all required items are found.
			if (
				action?.payload.hasOwnProperty('key') &&
				action?.payload.hasOwnProperty('value')
			) {
				const { key, value } = action.payload
				return {
					...state,
					data: {
						...state.data,
						[key]: value,
					},
				}
			}
		},
	},

	/***************************************************
	 *
	 * Extra Reducers
	 *
	 * Manage of asynchronous requests states
	 *
	 * *************************************************/
	extraReducers(builder) {
		builder
			.addCase(saveOptions.pending, (state) => {
				state.isLoading = true
			})
			.addCase(saveOptions.fulfilled, (state) => {
				state.isLoading = false
				state.status = 'success'
			})
			.addCase(saveOptions.rejected, (state) => {
				state.isLoading = false
				state.status = 'error'
			})
	},
})

/***************************************************
 *
 * Selectors
 *
 * *************************************************/

const settingsState = (state) => state.settings

/**
 * Get a single option value.
 *
 * @param {object} settingsState the settings object.
 * @param {string} key Option key.
 * @param {string|boolean|array|integer|object} value Default value.
 *
 * @return {string|boolean}
 */
export const getOption = createSelector(
	settingsState,
	(_, key) => key, //
	(_, __, value) => value,
	(settingsState, key, value = false) => {
		// Only if set.
		if (settingsState.data[key]) {
			value = settingsState.data[key]
		}
		return value
	}
)

/**
 * Get loading status
 *
 * @param {object} settingsState the settings state object.
 *
 * @return {boolean}
 */
export const getLoadingStatus = createSelector(
	settingsState,
	(settingsState) => settingsState.isLoading
)

// Export actions
export const { setOption } = settingsSlice.actions

export default settingsSlice.reducer

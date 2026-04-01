import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import { restPost, restGet, restDelete } from '../../helpers/api'
import { __ } from '@wordpress/i18n'
import { createSelector } from 'reselect'

/***************************************************
 *
 * Asynchronous Requests
 *
 * *************************************************/
/**
 * Initialize the playlist list
 *
 * @return {Promise}
 */
export const initPlaylists = createAsyncThunk(
	'playlists/init', // Redux action type
	(params) => {
		return restGet({
			path: 'playlists',
			params: params,
		})
	}
)

/**
 * Save a single playlist changes to database
 *
 * @return {Promise}
 */
export const savePlaylist = createAsyncThunk(
	'playlists/updateSinglePlaylist', // Redux action type
	(data) => {
		const { playlist, id } = data
		return restPost({
			path: `playlists/${id}`,
			data: playlist,
		})
	}
)

/**
 * Create a new playlist
 *
 * @return {Promise}
 */
export const createPlaylist = createAsyncThunk(
	'playlists/createPlaylist', // Redux action type
	async (data, { rejectWithValue }) => {
		const { playlist } = data
		try {
			return await restPost({
				path: `playlists`,
				data: playlist,
			})
		} catch (err) {
			return rejectWithValue(err)
		}
	}
)

/**
 * Change Playlist Order
 *
 * @return {void}
 */
export const orderChange = createAsyncThunk(
	'playlists/changePlaylistOrder', // Redux action type

	(data) => {
		const { playlistID, oldIndex, newIndex } = data

		if (playlistID > 0 && oldIndex !== newIndex) {
			restGet({
				path: 'playlists/reorder',
				params: {
					playlist: playlistID,
					from: oldIndex,
					to: newIndex,
				},
			})
		}
	}
)

/**
 * Delete a playlist
 *
 * @return {Promise}
 */
export const deletePlaylist = createAsyncThunk(
	'playlists/deletePlaylist', // Redux action type
	(data) => {
		const { id } = data

		return restDelete({
			path: `playlists/${id}`,
		})
	}
)

/**
 * Bulk delete playlists & videos
 *
 * @return {Promise}
 */
export const bulkDelete = createAsyncThunk(
	'playlists/bulkDelete', // Redux action type
	(data) => {
		const { selected } = data
		return restPost({
			path: `playlists/bulk-actions`,
			data: {
				action: 'delete',
				items: selected,
			},
		})
	}
)

/***************************************************
 *
 * Playlists Slice
 *
 * *************************************************/
const playlistsSlice = createSlice({
	name: 'playlists',

	/***************************************************
	 *
	 * Initial State
	 *
	 * *************************************************/

	initialState: {
		playlists: {},
		filtered: [],
		selected: {},
		isLoading: false,
	},

	/***************************************************
	 *
	 * Reducers
	 *
	 * *************************************************/
	reducers: {
		/**
		 * Update a single playlist in the playlists list
		 *
		 * If playlist exist, it will replace the playlist object,
		 * if not, it will add the playlist object.
		 *
		 * @param {object} state Current state.
		 * @param {object} action action data.
		 */
		setPlaylist: (state, action) => {
			const { playlist } = action.payload
			const { playlists } = state

			const newPlaylists = {
				...playlists,
				[playlist.id]: playlist,
			}

			return {
				...state,
				playlists: newPlaylists,
			}
		},

		/**
		 * Select or deselect a playlist ( checkbox )
		 *
		 * @return {void}
		 */
		togglePlaylist: (state, action) => {
			const { checked, id } = action.payload

			const allVideos = state.playlists?.[id]?.videos

			// The new selected object
			const selected = {
				...state.selected,
				[id]: {
					selected: checked,
					videos: (checked && allVideos) || [],
				},
			}

			// If playlist is not selected and no videos are selected,
			// remove the playlist
			if (selected?.[id]?.videos?.length === 0 && !checked) {
				delete selected[id]
			}

			return {
				...state,
				selected,
			}
		},

		/**
		 * Select or de-select all playlists and videos ( checkbox )
		 *
		 * @return {void}
		 */
		toggleAll: (state, action) => {
			const { checked } = action.payload

			const { playlists } = state

			// The new selected object
			const newSelected = {}
			if (checked === true) {
				Object.keys(playlists).forEach((id) => {
					newSelected[id] = {
						selected: true,
						videos: playlists?.[id].videos || [],
					}
				})
			}

			return {
				...state,
				selected: newSelected,
			}
		},

		/**
		 * Update selected videos list ( checkbox )
		 *
		 * @return {void}
		 */
		toggleVideo: (state, action) => {
			const { videoID, playlistID } = action.payload

			const { selected } = state

			const videos = selected?.[playlistID]?.videos || []

			// The new selected object
			let newSelected = {}

			// The new videos array
			let newVideos = []

			// Toggle video
			if (videos.includes(videoID)) {
				// Remove video if it exists
				newVideos = videos.filter((el) => el !== videoID)
			} else {
				// Add video if it doesn't exist
				newVideos = [...videos, videoID]
			}

			const isPlaylistSelected = selected?.[playlistID]?.selected || false

			// Update selected object
			newSelected = {
				...selected,
				[playlistID]: {
					selected: isPlaylistSelected,
					videos: newVideos,
				},
			}

			/* If playlist is not selected and no video is selected,
			remove the playlist */
			if (newVideos.length === 0 && !isPlaylistSelected) {
				delete newSelected[playlistID]
			}

			// Set the state
			return {
				...state,
				selected: newSelected,
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
			// Playlists Init
			.addCase(initPlaylists.pending, (state) => {
				state.isLoading = true
			})
			.addCase(initPlaylists.fulfilled, (state, action) => {
				state.isLoading = false

				const { payload } = action

				if (payload.success && payload.data) {
					let playlists = {}
					let filtered = []

					// Setup the list data.
					payload.data.forEach((playlist) => {
						playlists[playlist.id] = playlist
						filtered.push(playlist.id)
					})

					// update the state
					state.playlists = playlists
					state.filtered = filtered
				}
			})
			.addCase(initPlaylists.rejected, (state) => {
				state.isLoading = false
			})
			// Save Playlist
			.addCase(savePlaylist.pending, (state) => {
				state.isLoading = true
			})
			.addCase(savePlaylist.fulfilled, (state, action) => {
				const { data } = action.payload
				const { playlists } = state
				const newPlaylists = {
					...playlists,
					[data.id]: data,
				}

				state.isLoading = false

				state.playlists = newPlaylists
			})
			.addCase(savePlaylist.rejected, (state) => {
				state.isLoading = false
			})
			// Create Playlist
			.addCase(createPlaylist.pending, (state) => {
				state.isLoading = true
			})
			.addCase(createPlaylist.fulfilled, (state, action) => {
				const {
					payload: { data, success },
				} = action

				if (success && data) {
					const { playlists, filtered } = state
					// update the list in the store
					state.playlists = { ...playlists, [data.id]: data }
					state.filtered = [...filtered, data.id]
				}

				state.isLoading = false
			})
			.addCase(createPlaylist.rejected, (state) => {
				state.isLoading = false
			})
			// Delete Playlist
			.addCase(deletePlaylist.pending, (state) => {
				state.isLoading = true
			})
			.addCase(deletePlaylist.fulfilled, (state, action) => {
				const {
					meta: {
						arg: { id },
					},
					payload: { success },
				} = action

				// Remove the playlist if success
				if (success === true) {
					const { playlists, filtered } = state
					const newPlaylists = { ...playlists }
					delete newPlaylists[id]
					// update the list in the store
					state.playlists = { ...newPlaylists }
					state.filtered = filtered.filter((item) => item !== id)
				}

				state.isLoading = false
			})
			.addCase(deletePlaylist.rejected, (state) => {
				state.isLoading = false
			})
			// Bulk Delete
			.addCase(bulkDelete.pending, (state) => {
				state.isLoading = true
			})
			.addCase(bulkDelete.fulfilled, (state) => {
				state.isLoading = false

				// Empty the selected list
				state.selected = []
			})
			.addCase(bulkDelete.rejected, (state) => {
				state.isLoading = false

				// Empty the selected list
				state.selected = []
			})
	},
})

/***************************************************
 *
 * Selectors
 *
 * *************************************************/

const playlistsState = (state) => state.playlists

/**
 * Get playlists object
 *
 * @param {object} playlistsState the playlists object.
 *
 * @return {object}
 */
export const getPlaylists = createSelector(playlistsState, (playlistsState) => {
	return playlistsState.playlists
})

/**
 * Get filtered array
 *
 * @param {object} playlistsState the playlists object.
 *
 * @return {array}
 */
export const getFiltered = createSelector(playlistsState, (playlistsState) => {
	return playlistsState.filtered
})

/**
 * Checks if the playlist is selected
 *
 * @param {object} playlistsState the playlists object.
 * @param {number} id playlist id
 *
 * @return {boolean}
 */
export const isPlaylistSelected = createSelector(
	playlistsState,
	(_, id) => id,
	(playlistsState, id) => {
		return playlistsState.selected?.[id]?.selected
	}
)

/**
 * Checks if video is selected
 *
 * @param {object} playlistsState the playlists object.
 * @param {object} ids playlistID & videoID
 *
 * @return {boolean}
 */
export const isVideoSelected = createSelector(
	playlistsState,
	(_, ids) => ids,
	(playlistsState, ids) => {
		const { playlistID, videoID } = ids ?? {}

		const videos = playlistsState.selected?.[playlistID]?.videos || []

		return videos.includes(videoID)
	}
)

/**
 * Check of all videos and playlists are selected
 *
 * @param {object} playlistsState the playlists object.
 * @return {boolean}
 *
 */
export const areAllSelected = createSelector(
	playlistsState,
	(playlistsState) => {
		const { playlists, selected } = playlistsState

		// return false if at least one playlist is not selected
		for (const playlistId in playlists) {
			const isSelected = selected?.[playlistId]?.selected || false

			if (!isSelected) {
				return false
			}

			// return false if at least one video is not selected
			const allVideos = playlists?.[playlistId]?.videos || []

			const selectedVideos = selected?.[playlistId]?.videos || []

			// It suffices to compare lengths only
			if (allVideos.length !== selectedVideos.length) {
				return false
			}
		}

		return Object.keys(playlists).length > 0
	}
)

/**
 * Get selected Playlists & videos
 *
 * @param {object} playlistsState the playlists object.
 *
 * @return {object}
 */
export const getSelectedPlaylists = createSelector(
	playlistsState,
	(playlistsState) => {
		return playlistsState.selected
	}
)

/**
 * Get Loading status
 *
 * @param {object} playlistsState the playlists object.
 *
 * @return {*}
 */
export const getLoadingStatus = createSelector(
	playlistsState,
	(playlistsState) => {
		return playlistsState.isLoading
	}
)

// Export actions
export const {
	setPlaylist,
	updatePlaylistKey,
	togglePlaylist,
	toggleVideo,
	toggleAll,
} = playlistsSlice.actions

export default playlistsSlice.reducer

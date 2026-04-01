import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import { restPost, restDelete, restGet } from '../../helpers/api'
import { __ } from '@wordpress/i18n'
import { createSelector } from 'reselect'

/**
 * Re-initialize the videos list
 *
 * @return {Promise}
 */
export const initVideos = createAsyncThunk(
	'videos/init', // Redux action type
	(params) => {
		return restGet({
			path: 'videos',
			params: params,
		})
	}
)

/**
 * Deletes a video
 *
 * @return {Promise}
 */
export const deleteVideo = createAsyncThunk(
	'videos/delete', // Redux action type
	(data) => {
		const { id } = data
		return restDelete({
			path: `videos/${id}`,
		})
	}
)

/**
 * Delete a video from playlist
 *
 * @return {Promise}
 */
export const unAssignVideo = createAsyncThunk(
	'videos/delete', // Redux action type
	({ playlistID, videoID }) => {
		return restDelete({
			path: `playlists/${playlistID}/videos`,
			data: {
				videos: [videoID],
			},
		})
	}
)

/**
 * Fetch a single embed object using API.
 *
 * Use this only if the embed is not already
 * found in state.
 *
 * @param {object} data Video ID and other args.
 *
 * @return {Promise}
 */
export const fetchEmbed = createAsyncThunk(
	'videos/fetchEmbed',

	async (data) => {
		if (data.id && data.id > 0) {
			const response = await restGet({
				path: `videos/${data.id}/embed`,
				params: data,
			})

			// Include the id in response
			response.id = data.id

			return response
		}
	}
)

/**
 * Create a video
 *
 * @param {object} video Video object
 *
 * @return {Promise}
 */
export const createVideo = createAsyncThunk(
	'videos/create',

	(data) => {
		const { video } = data
		return restPost({
			path: 'videos',
			data: video,
		})
	}
)

/**
 * Update a video
 *
 * @param {object} video Video object
 *
 * @return {Promise}
 */
export const updateVideo = createAsyncThunk('videos/update', (data) => {
	const { video, id } = data
	return restPost({
		path: `videos/${id}`,
		data: video,
	})
})

/**
 * Search Videos
 *
 * @param {object} search search string
 *
 * @return {Promise}
 */
export const searchVideos = createAsyncThunk('videos/search', (data) => {
	const { search } = data
	return restGet({
		path: `videos`,
		params: {
			search,
			field: 'ids',
		},
	})
})

const videosSlice = createSlice({
	name: 'videos',

	initialState: { videos: {}, embeds: {}, filtered: [], isLoading: false },

	reducers: {
		/**
		 * Set a filtered video ids in state.
		 *
		 * It replaces the filtered list with the provided one
		 *
		 * @param {state} state object
		 * @param {object} action object
		 *
		 * @return {object} New State Object
		 */
		setFiltered: (state, action) => {
			const { ids } = action.payload
			return {
				...state,
				filtered: ids,
			}
		},
	},

	extraReducers(builder) {
		builder
			// Handling initVideos status
			.addCase(initVideos.pending, (state) => {
				// Set Loading
				state.isLoading = true
			})
			.addCase(initVideos.fulfilled, (state, action) => {
				state.isLoading = false

				const { payload } = action

				if (payload.success && payload.data) {
					const videos = {}
					const filtered = []

					// Setup the list data.
					payload.data.forEach((video) => {
						videos[video.id] = video
						filtered.push(video.id)
					})

					// update the state
					state.videos = videos
					state.filtered = filtered
				}

				state.status = 'success'
			})
			.addCase(initVideos.rejected, (state) => {
				// Update Status
				state.status = 'error'
			})

			// Handling fetchEmbed
			.addCase(fetchEmbed.fulfilled, (state, action) => {
				let embed = {}

				// Update state
				const { payload } = action

				if (payload?.success && payload?.data && payload?.data.html) {
					embed = payload.data

					const newData = {
						...payload.data,
					}

					newData.id = payload.id

					// Update the state
					state.embeds[payload.id] = newData
				}
			})

			// Handling Video Create
			.addCase(createVideo.fulfilled, (state, action) => {
				// Update state
				const { payload } = action

				if (payload?.success) {
					const video = payload.data

					// Add video to state
					if (video.id && video.id > 0) {
						state.videos = { ...state.videos, [video.id]: video }

						// Add the id to the filtered list so the video will be displayed
						state.filtered = [...state.filtered, video.id]
					}
				}
			})

			// Handeling Video Update
			.addCase(updateVideo.fulfilled, (state, action) => {
				const { payload } = action

				const newVideo = payload.data

				if (payload?.success) {
					// Update videos state
					state.videos = { ...state.videos, [newVideo.id]: newVideo }
				}
			})

			// Handeling Video Delete
			.addCase(deleteVideo.fulfilled, (state, action) => {
				const {
					meta: {
						arg: { id },
					},
					payload: { success },
				} = action

				if (success === true) {
					const { videos, filtered } = state

					// Remove from filtered list
					state.filtered = filtered.filter((item) => id !== item)

					// Delete the video from the videos object
					const newVideos = { ...videos }
					delete newVideos[id]

					state.videos = newVideos
				}
			})

			// Handeling Video Search
			.addCase(searchVideos.pending, (state, action) => {
				// Set Loading
				state.isLoading = true
			})
			.addCase(searchVideos.fulfilled, (state, action) => {
				const ids = action?.payload?.data || []

				state.filtered = ids

				// Set Loading
				state.isLoading = false
			})
			.addCase(searchVideos.rejected, (state, action) => {
				state.isLoading = false
			})
	},
})

// Selectors
const videosState = (state) => state.videos

/**
 * Get video(s) object(s) by id(s)
 *
 * @param {object} videosState
 * @param {array|string} ids videos ids
 *
 * @return {array|object|false}
 */
export const getVideos = createSelector(
	videosState,
	(_, ids) => ids,
	(videosState, ids) => {
		const videos = []

		if (Array.isArray(ids)) {
			ids.forEach((id) => {
				if (videosState.videos[id]) {
					videos.push(videosState.videos[id])
				}
			})

			return videos
		} else if (typeof ids === 'string' || typeof ids === 'number') {
			return videosState.videos[ids]
		} else {
			// If ids is not provided get all videos
			return videosState.videos
		}
	}
)

/**
 * Get Filtered IDs
 *
 * @param {object} videosState
 *
 * @return {array}
 */
export const getFilteredIds = createSelector(
	videosState,
	(videosState) => videosState.filtered
)

/**
 * Get a single embed object
 *
 * @param {object} videosState
 * @param {number} videoId
 *
 * @return {array}
 */
export const getEmbed = createSelector(
	videosState,
	(_, id) => id,
	(videosState, id) => {
		return videosState.embeds[id] || false
	}
)

/**
 * Get loading status
 *
 * @param {object} videosState the videos state object.
 *
 * @return {boolean}
 */
export const getLoadingStatus = createSelector(
	videosState,
	(videosState) => videosState.isLoading
)

// Export actions
export const { setFiltered } = videosSlice.actions

export default videosSlice.reducer

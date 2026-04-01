import { configureStore } from '@reduxjs/toolkit'
import settingsReducer from './slices/settings'
import playlistsReducer from './slices/playlists'
import videosReducer from './slices/videos'
import noticeReducer from './slices/notice'

const store = configureStore({
	reducer: {
		settings: settingsReducer,
		playlists: playlistsReducer,
		videos: videosReducer,
		notice: noticeReducer,
	},
})

export default store

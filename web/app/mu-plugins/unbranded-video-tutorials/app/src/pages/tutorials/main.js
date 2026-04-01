import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import { Provider } from 'react-redux'
import store from '../../store/store'
import { HashRouter } from 'react-router-dom'

import App from './App'

const root = ReactDOM.createRoot(
	document.getElementById('wpmudev-videos-tutorials-app')
)
root.render(
	<StrictMode>
		<Provider store={store}>
			<HashRouter>
				<App />
			</HashRouter>
		</Provider>
	</StrictMode>
)

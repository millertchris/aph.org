import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import { Provider } from 'react-redux'
import store from '../../store/store'
import App from './App'

const root = ReactDOM.createRoot(
	document.getElementById('wpmudev-videos-dashboard-app')
)
root.render(
	<StrictMode>
		<Provider store={store}>
			<App />
		</Provider>
	</StrictMode>
)

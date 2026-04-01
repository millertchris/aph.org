import { useState, useCallback } from 'react'
export const useDebouncedValue = ({ value, delay = 500 }) => {
	const [debouncedValue, setDebouncedValue] = useState(value)
	let timeout

	const setDebounce = useCallback((newValue) => {
		clearTimeout(timeout)
		timeout = setTimeout(() => setDebouncedValue(newValue), delay)
	}, [])

	return [debouncedValue, setDebounce]
}

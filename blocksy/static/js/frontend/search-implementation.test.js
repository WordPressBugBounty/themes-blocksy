/** @jest-environment jsdom */

jest.mock('../helpers', () => ({ loadStyle: jest.fn(() => Promise.resolve()) }))
jest.mock('./helpers/set-loader-state', () => ({ setLoaderState: jest.fn() }))
jest.mock('./helpers/is-ios-device', () => ({ isIosDevice: () => false }))
jest.mock('./helpers/when-transition-ends', () => ({
	whenTransitionEnds: (element, callback) => callback()
}))
jest.mock('./search/get-request-url', () => ({
	getRequestUrl: ({ inputValue }) => `/wp/v2/search?search=${inputValue}`
}))

const response = (data, ok = true) => ({
	ok,
	status: ok ? 200 : 500,
	headers: { get: () => String(data.length) },
	json: async () => JSON.parse(JSON.stringify(data)),
	clone: () => response(data, ok)
})

const product = (fields = {}) => ({
	id: 42,
	title: 'Test product',
	url: '/product/test',
	subtype: 'product',
	...fields
})

let form
let input

beforeEach(() => {
	jest.resetModules()
	jest.useFakeTimers()
	window.requestAnimationFrame = (callback) => callback()
	global.fetch = jest.fn()
	global.ct_localizations = {
		rest_url: '/wp-json/',
		dynamic_styles: { search_lazy: '/search.css' },
		search_live_stock_status_texts: {
			instock: 'Available now',
			outofstock: 'Sold out'
		},
		search_live_no_result: 'No results',
		search_live_one_result: '%s result',
		search_live_many_results: '%s results'
	}

	document.body.innerHTML = `<form data-live-results="product_price product_status">
		<input type="search" aria-controls="results"><span aria-live="polite"></span>
	</form>`
	form = document.querySelector('form')
	input = form.querySelector('input')
	require('./search-implementation').mount(form)
})

afterEach(() => {
	jest.useRealTimers()
	jest.restoreAllMocks()
})

const search = async (value) => {
	input.value = value
	input.dispatchEvent(new Event('input'))
	await jest.advanceTimersByTimeAsync(300)
}

const deferred = () => {
	let resolve
	let reject
	const promise = new Promise((resolvePromise, rejectPromise) => {
		resolve = resolvePromise
		reject = rejectPromise
	})
	return { promise, resolve, reject }
}

test('a response aborted after headers is fetched again when the query is retried', async () => {
	jest.spyOn(console, 'error').mockImplementation(() => {})
	const body = deferred()
	const beanie = product({
		title: 'Beanie',
		product_price: '$12',
		product_status: true
	})
	const shirt = product({
		title: 'Shirt',
		product_price: '$15',
		product_status: false
	})
	fetch
		.mockImplementationOnce((url, { signal }) => {
			signal.addEventListener('abort', () =>
				body.reject(new DOMException('Aborted', 'AbortError'))
			)
			return Promise.resolve({
				...response([beanie]),
				json: () => body.promise,
				clone: () => ({
					...response([beanie]),
					json: () =>
						Promise.reject(
							new DOMException('Aborted', 'AbortError')
						)
				})
			})
		})
		.mockResolvedValueOnce(response([shirt]))
		.mockResolvedValueOnce(response([beanie]))
	await search('beanie')
	await search('shirt')
	expect(form.querySelector('.ct-search-item').textContent).toContain('Shirt')
	await search('beanie')
	expect(fetch).toHaveBeenCalledTimes(3)
	expect(form.querySelector('.ct-search-item').textContent).toContain(
		'Beanie'
	)
	expect(form.classList.contains('ct-searching')).toBe(false)
})

test('successful cached searches retain their stock state after rendering', async () => {
	fetch
		.mockResolvedValueOnce(
			response([
				product({
					title: 'Beanie',
					product_price: '',
					product_status: true
				})
			])
		)
		.mockResolvedValueOnce(
			response([
				product({
					title: 'Shirt',
					product_price: '',
					product_status: false
				})
			])
		)
	await search('beanie')
	await search('shirt')
	await search('beanie')
	expect(fetch).toHaveBeenCalledTimes(2)
	expect(form.querySelector('.ct-search-item').textContent).toContain(
		'Beanie'
	)
	expect(form.querySelector('.stock-status').textContent).toBe(
		'Available now'
	)
})

test('a failed first search restores the button and can be retried', async () => {
	jest.spyOn(console, 'error').mockImplementation(() => {})
	fetch.mockRejectedValueOnce(new TypeError('Failed to fetch'))
	await search('product')
	expect(form.classList.contains('ct-searching')).toBe(false)
	expect(
		require('./helpers/set-loader-state').setLoaderState
	).toHaveBeenLastCalledWith(form, { enabled: false })
	fetch.mockResolvedValueOnce(response([]))
	await search('product')
	expect(fetch).toHaveBeenCalledTimes(2)
	expect(form.classList.contains('ct-searching')).toBe(false)
})

test('HTTP failures restore the button and are not cached', async () => {
	jest.spyOn(console, 'error').mockImplementation(() => {})
	fetch
		.mockResolvedValueOnce(response([], false))
		.mockResolvedValueOnce(response([]))
	await search('product')
	expect(form.classList.contains('ct-searching')).toBe(false)
	expect(console.error).toHaveBeenCalledTimes(1)
	await search('product')
	expect(fetch).toHaveBeenCalledTimes(2)
})

test('a cancelled older request cannot clear the active spinner', async () => {
	const first = deferred()
	const second = deferred()
	fetch.mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise)
	await search('first')
	const firstSignal = fetch.mock.calls[0][1].signal
	await search('second')
	expect(firstSignal.aborted).toBe(true)
	first.reject(new DOMException('Aborted', 'AbortError'))
	await jest.advanceTimersByTimeAsync(0)
	expect(form.classList.contains('ct-searching')).toBe(true)
	second.resolve(response([]))
	await jest.advanceTimersByTimeAsync(0)
	expect(form.classList.contains('ct-searching')).toBe(false)
})

test('a response that arrives after the next keystroke is still shown', async () => {
	const pending = deferred()
	fetch.mockReturnValueOnce(pending.promise)
	await search('first')
	input.value = 'firsts'
	input.dispatchEvent(new Event('input'))
	pending.resolve(
		response([
			product({ title: 'First', product_price: '', product_status: true })
		])
	)
	await jest.advanceTimersByTimeAsync(0)
	expect(fetch.mock.calls[0][1].signal.aborted).toBe(false)
	expect(form.querySelector('.ct-search-item').textContent).toContain('First')
})

test('an older response waiting for styles cannot overwrite a newer search', async () => {
	const styles = deferred()
	require('../helpers').loadStyle.mockReturnValueOnce(styles.promise)
	fetch.mockResolvedValueOnce(
		response([
			product({ title: 'Old', product_price: '', product_status: true })
		])
	)
	fetch.mockResolvedValueOnce(
		response([
			product({ title: 'New', product_price: '', product_status: false })
		])
	)
	await search('first')
	await search('second')
	styles.resolve()
	await jest.advanceTimersByTimeAsync(0)
	expect(form.querySelector('.ct-search-item').textContent).toContain('New')
	expect(form.querySelector('.ct-search-item').textContent).not.toContain(
		'Old'
	)
})

test.each(['', 'a'])(
	'clearing or shortening the input cancels loading (%s)',
	async (value) => {
		input.dataset.minLength = '2'
		const pending = deferred()
		fetch.mockReturnValueOnce(pending.promise)
		await search('first')
		const signal = fetch.mock.calls[0][1].signal
		await search(value)
		expect(signal.aborted).toBe(true)
		expect(form.classList.contains('ct-searching')).toBe(false)
		pending.resolve(response([product({ product_status: true })]))
		await jest.advanceTimersByTimeAsync(0)
		expect(form.querySelector('.ct-search-results')).toBeNull()
	}
)

test('Escape cancels a pending search and restores the button', async () => {
	const pending = deferred()
	fetch.mockReturnValueOnce(pending.promise)
	await search('first')
	input.dispatchEvent(new KeyboardEvent('keyup', { key: 'Escape' }))
	expect(fetch.mock.calls[0][1].signal.aborted).toBe(true)
	expect(form.classList.contains('ct-searching')).toBe(false)
	pending.resolve(response([]))
	await jest.advanceTimersByTimeAsync(0)
})

test.each([true, false])(
	'search stock %s uses the custom label without a second request',
	async (stock) => {
		fetch.mockResolvedValue(
			response([product({ product_price: '$12', product_status: stock })])
		)
		await search('product')
		expect(fetch).toHaveBeenCalledTimes(1)
		expect(form.querySelector('.stock-status').textContent).toBe(
			stock ? 'Available now' : 'Sold out'
		)
		expect(form.querySelector('.price').textContent).toBe('$12')
	}
)

test.each([true, false])(
	'fallback stock %s uses the same custom label',
	async (stock) => {
		fetch
			.mockResolvedValueOnce(response([product()]))
			.mockResolvedValueOnce(
				response([{ id: 42, price_html: '$12', is_in_stock: stock }])
			)
		await search('product')
		expect(fetch).toHaveBeenCalledTimes(2)
		expect(form.querySelector('.stock-status').textContent).toBe(
			stock ? 'Available now' : 'Sold out'
		)
	}
)

test('a stock-only fallback preserves the price supplied by search', async () => {
	fetch
		.mockResolvedValueOnce(response([product({ product_price: '$12' })]))
		.mockResolvedValueOnce(
			response([{ id: 42, price_html: '$99', is_in_stock: false }])
		)
	await search('product')
	expect(form.querySelector('.price').textContent).toBe('$12')
	expect(form.querySelector('.stock-status').textContent).toBe('Sold out')
})

test('a product omitted by the stock fallback renders without a stock label', async () => {
	fetch
		.mockResolvedValueOnce(response([product({ product_price: '$12' })]))
		.mockResolvedValueOnce(response([]))
	await search('product')
	expect(fetch).toHaveBeenCalledTimes(2)
	expect(form.querySelector('.ct-search-item').textContent).toContain(
		'Test product'
	)
	expect(form.querySelector('.price').textContent).toBe('$12')
	expect(form.querySelector('.stock-status')).toBeNull()
})

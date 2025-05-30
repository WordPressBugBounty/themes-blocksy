import './public-path'
import $ from 'jquery'
import { initAllPanels } from './options/initPanels'
import { initWidget } from './backend/widgets'

import { initAllWooVariations } from './backend/woo-variation'
import { initTaxonomies } from './backend/taxonomies'
import { initAllWooAttributesOptions } from './backend/woo-attributes'

import { mountCoreBlocksFix } from './editor/utils/fix-core-blocks-registration'

mountCoreBlocksFix()

if ($ && $.fn) {
	$(document).on('widget-added', (event, widget) => {
		initWidget(widget[0])
	})

	initAllWooVariations()

	setTimeout(() => {
		initAllWooAttributesOptions()

		$(document.body).on(
			'woocommerce_variations_added woocommerce_variations_loaded',
			function () {
				initAllWooVariations()
			}
		)

		$(document.body).on('woocommerce_attributes_saved', function () {
			initAllWooAttributesOptions()
		})
	}, 1000)
}

document.addEventListener('DOMContentLoaded', () => {
	initAllPanels()
	initTaxonomies()
	;[
		...document.querySelectorAll('.notice-blocksy-plugin'),
		...document.querySelectorAll('.notice-blocksy-blocks-move'),
		...document.querySelectorAll('[data-dismiss]'),
	].map((el) => import('./notification/main').then(({ mount }) => mount(el)))

	if ($) {
		$(document).on(
			'click',
			'[href*="technical_support"][href*="ct-dashboard"]',
			(e) => {
				e.preventDefault()
				location.href = 'https://creativethemes.com/blocksy/support'
			}
		)
	}
})

export { default as Overlay } from './customizer/components/Overlay'
export {
	getValueFromInput,
	getFirstLevelOptions,
} from './options/helpers/get-value-from-input'
export { default as OptionsPanel } from './options/OptionsPanel'
export { default as Panel, PanelMetaWrapper } from './options/options/ct-panel'
export { DeviceManagerProvider } from './customizer/components/useDeviceManager'
export { default as PanelLevel } from './options/components/PanelLevel'
export { default as Switch } from './options/options/ct-switch'
export { default as Checkboxes } from './options/options/ct-checkboxes'
export { default as ImageUploader } from './options/options/ct-image-uploader'
export { default as Select } from './options/options/ct-select'
export { default as OutsideClickHandler } from './options/options/react-outside-click-handler'
export { default as DateTimePicker } from './options/options/date-time-picker'
export { default as EntityIdPicker } from './options/options/ct-entity-picker'

export { Transition, animated } from 'react-spring'
export { default as bezierEasing } from 'bezier-easing'
export { default as usePopoverMaker } from './options/helpers/usePopoverMaker'

export { default as ToolsWithOptionsPanel } from './editor/components/ToolsWithOptionsPanel'

// gutenberg blocks
export { default as ColorsPanel } from './editor/components/ColorsPanel'

export * as syncHelpers from 'customizer-sync-helpers'

export {
	getAttributesFromOptions,
	getDefaultsFromOptions,
	getOptionsForBlock,
} from './editor/utils'

export { getColorsDefaults } from './editor/utils/colors'

export const onDocumentLoaded = (cb) => {
	if (/comp|inter|loaded/.test(document.readyState)) {
		cb()
	} else {
		document.addEventListener('DOMContentLoaded', cb, false)
	}
}

export const mountFlexy = (sliderEl, args) => {
	return import('./frontend/flexy')
}

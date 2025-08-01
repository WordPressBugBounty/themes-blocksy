import { createElement } from '@wordpress/element'
import _ from 'underscore'
import classnames from 'classnames'

import { clamp, round } from '../ct-slider'
import { getNumericKeyboardEvents } from '../../helpers/getNumericKeyboardEvents'

const BlocksyNumberOption = ({
	value,
	option,
	option: {
		attr,
		step = 1,
		blockDecimal = true,
		decimalPlaces = 1,
		markAsAutoFor,
	},
	device,
	onChange,
	liftedOptionStateDescriptor,
}) => {
	const { liftedOptionState, setLiftedOptionState } =
		liftedOptionStateDescriptor

	const parsedValue =
		markAsAutoFor && markAsAutoFor.indexOf(device) > -1 ? 'auto' : value

	const min = !option.min && option.min !== 0 ? -Infinity : option.min
	const max = !option.max && option.max !== 0 ? Infinity : option.max

	return (
		<div
			className={classnames('ct-option-number', {
				[`ct-reached-limits`]:
					parseFloat(parsedValue) === parseInt(min) ||
					parseFloat(parsedValue) === parseInt(max),
			})}
			{...(attr || {})}>
			<a
				className={classnames('ct-minus', {
					['ct-disabled']:
						parseFloat(parsedValue) === parseFloat(min) ||
						parsedValue === '',
				})}
				onClick={() =>
					onChange(
						round(
							clamp(
								min,
								max,
								parseFloat(parsedValue || 0) - parseFloat(step)
							),
							decimalPlaces
						)
					)
				}
			/>

			<a
				className={classnames('ct-plus', {
					['ct-disabled']:
						parseFloat(parsedValue) === parseFloat(max),
				})}
				onClick={() =>
					onChange(
						round(
							clamp(
								min,
								max,
								parseFloat(parsedValue || 0) + parseFloat(step)
							),
							decimalPlaces
						)
					)
				}
			/>

			<input
				type="number"
				// step={1}
				value={
					liftedOptionState && liftedOptionState.isEmptyInput
						? ''
						: parsedValue
				}
				onBlur={(e) => {
					if (e?.nativeEvent?.relatedTarget?.matches('.ct-revert')) {
						return
					}

					setLiftedOptionState({
						isEmptyInput: false,
					})

					onChange(round(clamp(min, max, parsedValue), decimalPlaces))
				}}
				onChange={({ target: { value } }) => {
					if (value.toString().trim() === '') {
						setLiftedOptionState({
							isEmptyInput: true,
						})
						return
					}

					setLiftedOptionState({
						isEmptyInput: false,
					})

					_.isNumber(parseFloat(value))
						? onChange(round(value, decimalPlaces))
						: parseFloat(value)
						? onChange(
								round(
									Math.min(parseFloat(value), max),
									decimalPlaces
								)
						  )
						: onChange(round(value, decimalPlaces))
				}}
				{...getNumericKeyboardEvents({
					blockDecimal,
					value: parsedValue,
					onChange: (value) => {
						onChange(round(clamp(min, max, value), decimalPlaces))
					},
				})}
			/>
		</div>
	)
}

export default BlocksyNumberOption

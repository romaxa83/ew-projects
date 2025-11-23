import { DateService } from '@/services/date';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect';

/**
 * @param {{
 *   controlElement: import('jquery').JQuery,
 *   dateElement: import('jquery').JQuery | undefined,
 *   initialDate: DateService | null | undefined,
 *   minDate: DateService | null | undefined,
 *   maxDate: DateService | null | undefined,
 *   withSelectingYears: boolean | undefined,
 *   onChange: undefined | ((selectedDates: Date[], dateStr: string, fp: import('flatpickr/types/instance').Instance, asYearValue: boolean) => void),
 * }} options
 */
export function filterByMonth({
	controlElement,
	dateElement,
	minDate = null,
	maxDate = null,
	initialDate = null,
	withSelectingYears = false,
	onChange,
}) {
	if (initialDate) {
		setControlValue(initialDate);
		setDateValue(initialDate);
	}

	window.flatpickr(controlElement, {
		minDate: minDate?.format({
			preset: 'dateRangeHiddenInput',
			convert: false,
		}),
		maxDate: maxDate?.format({
			preset: 'dateRangeHiddenInput',
			convert: false,
		}),
		onChange(selectedDates, dateStr, fp) {
			const asYearValue = hasYearMetaFlag(fp);
			setDateValue(new DateService(selectedDates[0]), asYearValue);
			if (fp.rContainer) {
				const method = asYearValue ? 'add' : 'remove';
				fp.rContainer.classList[method](
					'flatpickr-custom-all-selected'
				);
			}
			onChange?.(selectedDates, dateStr, fp, asYearValue);
			removeYearMetaFlag(fp);
		},
		plugins: [
			new monthSelectPlugin({
				shorthand: true,
				dateFormat: 'M Y',
			}),
			withSelectingYears
				? function () {
						return {
							onReady: [buildYearButton],
						};
				  }
				: null,
		].filter(Boolean),
	});

	/**
	 * @param {DateService} dateService
	 * @param {boolean} [asYearValue]
	 */
	function setControlValue(dateService, asYearValue) {
		controlElement.val(
			dateService.format({
				preset: asYearValue ? 'yearFilter' : 'monthsForDatepicker',
				convert: false,
			})
		);
	}

	/**
	 * @param {DateService} dateService
	 * @param {boolean} [asYearValue]
	 */
	function setDateValue(dateService, asYearValue = false) {
		if (!dateElement) return;
		dateElement.val(
			dateService.format({
				preset: asYearValue ? 'yearFilter' : 'monthFilter',
				convert: false,
			})
		);
	}

	/**
	 * @param _
	 * @param __
	 * @param {import('flatpickr/types/instance').Instance} fp
	 */
	function buildYearButton(_, __, fp) {
		const button = document.createElement('button');
		button.type = 'button';
		button.textContent = 'Select all year';
		button.classList.add('btn', 'btn-xs', 'btn-outline-secondary', 'ml-2');
		button.addEventListener('click', () => {
			const date = new Date(fp.currentYear, 0);
			setYearMetaFlag(fp);
			fp.setDate(date, true, 'Y');
			setControlValue(new DateService(date), true);
			fp.close();
		});
		fp.currentYearElement
			.closest('.flatpickr-current-month')
			.append(button);
	}
}

/** @param {Object} fp */
function setYearMetaFlag(fp) {
	fp.__YEAR_VALUE = true;
}

/**
 * @param {Object} fp
 * @returns {boolean}
 */
function hasYearMetaFlag(fp) {
	return fp.__YEAR_VALUE === true;
}

/** @param {Object} fp */
function removeYearMetaFlag(fp) {
	fp.__YEAR_VALUE = false;
}

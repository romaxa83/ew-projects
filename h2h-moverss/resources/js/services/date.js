import dayjs, { Dayjs } from 'dayjs';
import advancedFormat from 'dayjs/plugin/advancedFormat';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(advancedFormat);

/**
 * Provides centralized date control and formatting
 */
export class DateService {
	/** @type {import('dayjs').Dayjs} */
	#date;

	/** @param {DateValue} date */
	constructor(date) {
		this.#date = dayjs(date);
	}

	/** @return {import('dayjs').Dayjs} */
	#convertUS() {
		return this.#date.tz('US/Central');
	}

	/** @return {boolean} */
	get #dateIsInvalid() {
		return !this.#date.isValid();
	}

	/**
	 * @param {{
	 *     preset:
	 *      | 'dateRangeHiddenInput'
	 *      | 'monthsForDatepicker'
	 *      | 'daysForDatepicker'
	 *      | 'monthFilter'
	 *      | 'yearFilter'
	 *      | 'changelog'
	 *      | 'attachment'
	 *      | 'payments',
	 *      | 'payroll',
	 *     convert?: boolean
	 * }} options
	 * @return {string}
	 */
	format(options) {
		const { preset, convert = true } = options;

		if (this.#dateIsInvalid) {
			return '';
		}

		const date = convert ? this.#convertUS() : this.#date;
		return date.format(getFormatter());

		function getFormatter() {
			switch (preset) {
				case 'dateRangeHiddenInput':
					return 'YYYY-MM-DD';
				case 'monthsForDatepicker':
					return 'MMM YYYY';
				case 'monthFilter':
					return 'YYYY-MM';
				case 'yearFilter':
					return 'YYYY';
				case 'changelog':
					return 'MM/DD/YY (hh:mm A)';
				case 'attachment':
					return 'MM.DD.YYYY, hh:mm A';
				case 'payments':
					return 'MMMM Do YYYY, hh:mm A';
				case 'payroll':
					return 'MMM DD, YYYY hh:mm A';
				default:
					return 'MMM YYYY';
			}
		}
	}

	/**
	 * @param {'start' | 'end'} day
	 * @param {'month'} unit
	 * @returns {DateService}
	 */
	shiftTo(day, unit) {
		if (day === 'start') this.#date = this.#date.startOf(unit);
		if (day === 'end') this.#date = this.#date.endOf(unit);
		return this;
	}

	/**
	 * Creates self instance from the current date
	 * @returns {DateService}
	 */
	static fromToday() {
		return new DateService(new Date());
	}

	/**
	 * Creates self instance from a number
	 * @param {number} timestamp time in seconds
	 * @returns {DateService}
	 */
	static fromTimestamp(timestamp) {
		return new DateService(timestamp * 1000);
	}
}

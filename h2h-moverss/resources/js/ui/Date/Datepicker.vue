<template>
	<input type="text" class="form-control" :id="id" :value="dateValue" />
</template>

<script>
import peaksDatesCalendarMixin from '@/mixins/peaksDatesCalendarMixin';
import { DateService } from '@/services/date';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect';
// import weekSelectPlugin from 'flatpickr/dist/plugins/weekSelect/weekSelect';

export default {
	name: 'UiDatepicker',
	mixins: [peaksDatesCalendarMixin],
	props: {
		id: {
			type: String,
			required: true,
		},
		initDate: {
			type: [String, Date],
		},
		/** @type {'days' | 'months'} */
		format: {
			type: String,
			default: 'days',
			validate(value) {
				return ['days', 'months'].includes(value);
			},
		},
        convertUS: {
            type: Boolean,
        },
		minDate: {
			type: String,
			required: false,
		},
	},
	data() {
		return {
			instance: null,
		};
	},
	computed: {
		dateValue: {
			get() {
				const date = new DateService(this.initDate || new Date());
				switch (this.format) {
					case 'days':
						return date.format({
							preset: 'daysForDatepicker',
							convert: this.convertUS,
						});
					case 'months':
						return date.format({
							preset: 'monthsForDatepicker',
                            convert: this.convertUS,
						});
				}
			},
			set() {},
		},
	},
	mounted() {
		this.instance = window.flatpickr(`#${this.id}`, {
			minDate: this.minDate,
			onChange: (selectedDate, formattedValue) => {
				this.$emit('date-change', selectedDate, formattedValue);
			},
			plugins: [
				this.format === 'months' &&
					new monthSelectPlugin({
						shorthand: true,
						dateFormat: 'M Y',
					}),
			].filter(Boolean),
		});
	},
};
</script>

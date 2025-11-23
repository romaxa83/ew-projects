<template>
	<div class="ml-auto nav-tabs-clean">
		<div class="form-group mb-0">
			<div class="row">
				<button
					type="button"
					class="btn btn-outline-primary waves-effect waves-themed mr-4"
					@click="createOrder()"
				>
					Create Order
				</button>
				<div class="col">
					<div
						class="input-group"
						v-show="current_tab === 'calendar'"
					>
						<div class="input-group-prepend">
							<button
								@click="datePrev"
								class="btn btn-info waves-effect waves-themed"
								type="button"
							>
								<i class="fal fa-arrow-left"></i>
							</button>
							<label
								for="calendar_date"
								class="input-group-text fs-xl"
							>
								<i class="fal fa-calendar"></i>
							</label>
						</div>
						<input
							id="calendar_date"
							type="text"
							class="form-control"
							v-model="currentCalendarDate"
						/>
						<div class="input-group-append">
							<button
								@click="dateNext"
								class="btn btn-info waves-effect waves-themed"
								type="button"
							>
								<i class="fal fa-arrow-right"></i>
							</button>
						</div>
					</div>
					<div
						class="input-group"
						v-show="current_tab === 'calendar-week'"
					>
						<div class="input-group-prepend">
							<button
								@click="datePrev"
								class="btn btn-info waves-effect waves-themed"
								type="button"
							>
								<i class="fal fa-arrow-left"></i>
							</button>
							<label
								for="calendar_date_week"
								class="input-group-text fs-xl"
							>
								<i class="fal fa-calendar"></i>
							</label>
						</div>
						<input
							id="calendar_date_week"
							type="text"
							class="form-control"
							v-model="currentWeekDate"
						/>
						<div class="input-group-append">
							<button
								@click="dateNext"
								class="btn btn-info waves-effect waves-themed"
								type="button"
							>
								<i class="fal fa-arrow-right"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import peaksDatesCalendarMixin from '@/mixins/peaksDatesCalendarMixin';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect';
import weekSelectPlugin from 'flatpickr/dist/plugins/weekSelect/weekSelect';

let fp, fp_date_week;

export default {
	name: 'CalendarHeader',
	mixins: [peaksDatesCalendarMixin],
	props: {
		initCalendarDate: {
			type: String,
			required: true,
		},
		initWeekDate: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			updated_at: null,
			current_tab: 'calendar',
		};
	},
	computed: {
		currentCalendarDate: {
			get() {
				return moment(this.initCalendarDate).format('MMM YYYY');
			},
			set(value) {},
		},
		currentWeekDate: {
			get() {
				return moment(this.initWeekDate).format('YYYY-MM-DD');
			},
			set(value) {},
		},
	},
	mounted() {
		this.tabWatch();

		let vm = this;

		fp_date_week = flatpickr('#calendar_date_week', {
			minDate: '2021-01',
			altInput: true,
			altFormat: 'W \\w\\e\\e\\k (M, Y)',
			dateFormat: 'Y-m-d',
			plugins: [new weekSelectPlugin({})],
			weekNumbers: true,
			// locale: {
			//     firstDayOfWeek: 1
			// },
			onChange(selectedDates, dateStr) {
				let calendarDate =
					moment(vm.currentCalendarDate, 'MMM YYYY').format('Y-MM') +
					'-01';

				window.location =
					'/calendar?week_date=' +
					dateStr +
					'&calendar_date=' +
					calendarDate +
					window.location.hash;
			},
		});

		fp = flatpickr('#calendar_date', {
			minDate: '2021-01',
			onChange: function (selectedDates, dateStr) {
				let calendarDate =
					moment(dateStr, 'MMM YYYY').format('Y-MM') + '-01';

				window.location =
					'/calendar?calendar_date=' +
					calendarDate +
					window.location.hash;
			},
			plugins: [
				new monthSelectPlugin({
					shorthand: true, //defaults to false
					dateFormat: 'M Y',
					// altFormat: "F Y", // Бага https://github.com/flatpickr/flatpickr/issues/2303
				}),
			],
		});

		this.dayDetails();
	},
	methods: {
		createOrder(date = '') {
			let params = {};
			if (date) {
				params = {
					work: {
						date,
						types: [1],
						in_dispatch: true,
					},
				};
			}

			App.Orders.createOrder(params);
		},
		dateNext() {
			if (this.current_tab === 'calendar-week') {
				let date = moment(this.initWeekDate)
					.add(1, 'week')
					.format('YYYY-MM-DD');
				fp_date_week.setDate(date, true);
			} else if (this.current_tab === 'calendar') {
				let date = moment(this.initCalendarDate)
					.add(1, 'month')
					.format('MMM YYYY');
				fp.setDate(date, true);
			}
		},
		datePrev() {
			if (this.current_tab === 'calendar-week') {
				let date = moment(this.initWeekDate)
					.subtract(1, 'week')
					.format('YYYY-MM-DD');
				fp_date_week.setDate(date, true);
			} else if (this.current_tab === 'calendar') {
				let date = moment(this.initCalendarDate)
					.subtract(1, 'month')
					.format('MMM YYYY');
				fp.setDate(date, true);
			}
		},
		dayDetails() {
			$('.day-detail').tooltipster({
				theme: 'tooltipster-shadow',
				interactive: true,
				content: 'Loading...',
				contentAsHTML: true,
				side: ['bottom'],
				functionBefore: function (instance, helper) {
					let $origin = $(helper.origin),
						div = $origin.closest('div.calendar-cell');

					// we set a variable so the data is only loaded once via Ajax, not every time the tooltip opens
					if ($origin.data('loaded') !== true) {
						axios
							.post('/calendar/cellInfo', {
								date: div.data('date'),
							})
							.then((resp) => {
								if (resp.data.success == true) {
									instance.content(resp.data.html);
									$origin.data('loaded', true);
								} else {
									throw {
										response: {
											data: resp.data,
										},
									};
								}
							})
							.catch((error) => {
								console.log(error);
								App.Forms.simpleErrors(error.response.data);
							});
					}
				},
			});
		},
		tabHashChanged() {
			let hash = window.location.hash;
			if (hash && hash === '#tab-calendar-week') {
				this.current_tab = 'calendar-week';
			} else {
				this.current_tab = 'calendar';
			}
		},
		tabWatch() {
			// Следим за табом и обновляем current_tab
			window.addEventListener(
				'hashchange',
				() => {
					this.tabHashChanged();
				},
				false
			);
			this.tabHashChanged();
		},
	},
};
</script>

<template>
	<div class="ml-auto nav-tabs-clean">
		<div class="form-group mb-0">
			<div class="row">
				<div class="col">
					<div class="input-group">
						<div class="input-group-prepend">
							<button
								@click="datePrev"
								class="btn btn-info waves-effect waves-themed"
								id="dispatch-prev-date"
								type="button"
							>
								<i class="fal fa-arrow-left"></i>
							</button>
							<label
								for="dispatch_date"
								class="input-group-text fs-xl"
							>
								<i class="fal fa-calendar"></i>
							</label>
						</div>
						<input
							id="dispatch_date"
							type="text"
							class="form-control"
							v-model="currentDate"
						/>
						<div class="input-group-append">
							<button
								@click="dateNext"
								class="btn btn-info waves-effect waves-themed"
								id="dispatch-next-date"
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

let fp;

export default {
	name: 'DispatchWorksHeader',
	mixins: [peaksDatesCalendarMixin],
	props: {
		initDate: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			updated_at: null,
		};
	},
	computed: {
		currentDate: {
			get() {
				return moment(this.initDate).format('YYYY-MM-DD');
			},
			set(value) {},
		},
	},
	mounted() {
		this.initWorks();

		fp = flatpickr('#dispatch_date', {
			minDate: '2021-01',
			altInput: true,
			altFormat: 'M d, Y',
			dateFormat: 'Y-m-d',
			// locale: {
			//     firstDayOfWeek: 1
			// },
			onChange: function (selectedDates, dateStr) {
				window.location =
					'/dispatch?start_date=' + dateStr + window.location.hash;
			},
		});

		let loader = $('.flatpickr-rContainer .dayContainer');
		this.initPeaksDatesCalendarMixin(fp, this, loader);

		// Затягиваем PeaksDates, после открываем модалку
		window.VueApp.$store.dispatch('initPeaksDates').then(() => {
			// Тригерим обновление календаря
			fp.redraw();
		});
	},
	methods: {
		dateNext() {
			let date = moment(this.initDate).add(1, 'day').format('YYYY-MM-DD');
			fp.setDate(date, true);
		},
		datePrev() {
			let date = moment(this.initDate)
				.subtract(1, 'day')
				.format('YYYY-MM-DD');
			fp.setDate(date, true);
		},
		initWorks() {
			this.$store.commit('dispatch/setDispatchDay', this.currentDate);
			this.$store
				.dispatch('dispatch/initDispatchWorks', {
					currentDate: this.currentDate,
				})
				.then((data) => {
					this.updated_at = data.updated_at;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});
		},
		submit() {
			$('.d-loader').removeClass('d-none');
			return new Promise((resolve, reject) => {
				// console.log(this.$store.getters['dispatch/getWorksToSave']);
				axios
					.post('/dispatch/save', {
						start_date: this.currentDate,
						updated_at: this.updated_at,
						works: this.$store.getters['dispatch/getWorksToSave'],
					})
					.then((resp) => {
						if (resp.data?.success === true) {
							this.updated_at = resp.data.updated_at;
							this.$store.commit(
								'dispatch/updateEntityIds',
								false
							);
							this.$store.commit('dispatch/setChanged', false);
							$('.has-tooltip').tooltip();
							$('body').tooltip({
								selector: '.has-tooltip',
							});
							resolve();
							// tooltip?
						} else {
							const data = resp.data || resp;
							App.Forms.simpleErrors(data);
							reject(data);
						}
					})
					.then(() => {
						this.$store.dispatch(
							'dispatch/refetchChangelog',
							'update'
						);
					})
					.catch((error) => {
						const data = error.response
							? error.response.data
							: error;
						App.Forms.simpleErrors(data);
						reject(data);
					})
					.finally(() => {
						$('.d-loader').addClass('d-none');
					});
			});
		},
	},
};
</script>

<template>
	<div>
		<div class="d-flex">
			<div class="flex-grow-1">
				<ul role="tablist" class="nav nav-tabs nav-tabs-clean">
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-information"
							role="tab"
							aria-selected="true"
							class="nav-link active"
							>Information</a
						>
					</li>
				</ul>
			</div>
			<div class="ml-auto nav-tabs-clean">
				<div class="form-group mb-0">
					<div class="row">
						<div class="col text-right">
							<button
								@click="submit()"
								type="button"
								class="text-nowrap btn waves-effect waves-themed"
								:class="{
									'btn-danger': is_changed,
									'btn-default': !is_changed,
								}"
								:disabled="!is_changed"
							>
								<span
									v-show="updating"
									class="spinner-border spinner-border-sm"
									role="status"
									aria-hidden="true"
								></span>
								<i class="fal fa-download mr-1"></i>
								{{
									updating ? 'Saving changes' : 'Save changes'
								}}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-content mt-md-3 mt-6">
			<div v-if="loading" class="d-flex justify-content-center">
				<div class="spinner-border" role="status">
					<span class="sr-only">Loading...</span>
				</div>
			</div>
			<div
				v-else
				role="tabpanel"
				id="tab-calendar"
				aria-labelledby="tab-information"
				class="tab-pane fade active show"
			>
				<holidays-truck-employee
					title="Weekly Peak"
					:records.sync="peak_week_days"
					@submit="submit"
				></holidays-truck-employee>
				<peak-date-calendar
					v-if="!loading"
					:dataSource.sync="records"
					@submit="submit"
				></peak-date-calendar>
			</div>
		</div>

		<div
			class="modal fade"
			id="calendar-modal"
			role="dialog"
			aria-hidden="true"
		>
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Add busy time</h4>
						<button
							type="button"
							class="close"
							data-dismiss="modal"
							aria-label="Close"
						>
							<span aria-hidden="true"
								><i class="fal fa-times"></i
							></span>
						</button>
					</div>
					<form>
						<div class="modal-body">
							<input type="hidden" name="id" value="" />
							<input type="hidden" name="randomRef" value="" />
							<div class="form-group">
								<label for="f_type" class="form-label"
									>Type</label
								>
								<select
									id="f_type"
									class="form-control"
									name="type_id"
								>
									<option
										v-for="v in types"
										:key="v.id"
										v-bind:value="v.id"
									>
										{{ v.title }}
									</option>
								</select>
							</div>
							<div class="form-group">
								<label for="m_description" class="form-label"
									>Description</label
								>
								<input
									id="m_description"
									type="text"
									class="form-control"
									name="description"
									placeholder="Reason description"
								/>
							</div>
							<div class="form-group">
								<label for="f_startDate" class="form-label"
									>Dates</label
								>
								<div class="input-group">
									<input
										id="f_startDate"
										type="text"
										class="form-control dateInput"
										name="startDate"
									/>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button
								type="button"
								class="btn btn-secondary"
								data-dismiss="modal"
							>
								Close
							</button>
							<button
								type="submit"
								class="btn btn-primary"
								id="saveModal"
							>
								Save
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import PeakDateCalendar from './PeakDateCalendar';
import HolidaysTruckEmployee from './HolidaysTruckEmployee';
import ModalNotes from '../Order/TabOverview/ClientModal/Notes';
import { AxiosHelper } from '@/helpers/axiosHelper';

export default {
	name: 'PeakDate',
	components: {
		HolidaysTruckEmployee,
		PeakDateCalendar,
		ModalNotes,
	},
	data() {
		return {
			loading: true,
			updating: false,
			is_changed: false,
			records_orig: [],
			records: {},
			peak_week_days: {},
			types: {},
		};
	},
	watch: {
		records: {
			handler: function (val, oldVal) {
				if (!this.is_changed && Object.keys(oldVal).length) {
					this.is_changed = true;
				}
			},
			deep: true,
		},
		peak_week_days(new_v, old_v) {
			if (old_v.length) {
				this.is_changed = true;
				this.formatCalendar();
			}
		},
	},
	mounted() {
		AxiosHelper({
			url: window.location.href,
		})
			.then(({ records, peak_week_days, types }) => {
				if (records) {
					this.types = types;
					this.peak_week_days = peak_week_days;
					this.records_orig = records;

					this.formatCalendar();
				}
			})
			.finally(() => (this.loading = false));
	},
	methods: {
		formatCalendar() {
			let records = [];

			// Генерим PeakDays на текущий год +1
			let fromDate = moment().startOf('year');
			let toDate = fromDate.clone().add(1, 'year').endOf('year');
			let diff = toDate.diff(fromDate, 'days');
			for (let i = 0; i < diff; i++) {
				let day = moment(fromDate).add(i, 'days');
				if (this.peak_week_days.includes(day.day())) {
					records.push({
						id: null,
						type_id: 2,
						name: this.types[2].title,
						title: this.types[2].title,
						description: null,
						date: day.format('YYYY-MM-DD'),
						startDate: day,
						startTime: '00:00',
						endDate: day,
						endTime: '23:59',
						color: '#b56ce2',
						is_virtual: true,
						randomRef: App.Miscs.generateToken(),
					});
				}
			}

			this.records_orig.forEach((item) => {
				let startDate = moment(item.date, 'YYYY-MM-DD');
				let color = '#f5bb00';
				if (item.type_id === 3) {
					color = '#0ed6b9';
				} else if (item.type_id === 2) {
					color = '#b56ce2';
				}

				let date = startDate.format('YYYY-MM-DD');

				let index = records.findIndex((v) => v.date === date);
				// Удаляем виртуальные вых.
				if (index !== -1) {
					records.splice(index, 1);
				}

				records.push({
					id: item.id,
					type_id: item.type_id,
					name: this.types[item.type_id].title,
					title: this.types[item.type_id].title,
					description: item.description,
					startDate: startDate,
					endDate: startDate,
					startTime: '00:00',
					endTime: '23:59',
					randomRef: App.Miscs.generateToken(),
					color,
				});
			});

			this.records = records;
		},
		submit() {
			this.updating = true;

			let records = this.records
				.slice()
				.filter((item) => !item.is_virtual)
				.map((item) => {
					item.startDate = item.startDate.format('YYYY-MM-DD');

					return item;
				});

			AxiosHelper({
				url: window.location.href + '/save',
				data: {
					records,
					peakWeekDays: this.peak_week_days,
				},
			})
				.then(({ records }) => {
					if (records) {
						this.records_orig = records;

						this.formatCalendar();

						// Костыль для рефреша данных календаря. Это печаль
						this.$nextTick(() =>
							$('.calendar-header .next').trigger('click')
						);
						this.$nextTick(() =>
							$('.calendar-header .prev').trigger('click')
						);
					}
				})
				.finally(() => {
					this.updating = false;
					this.is_changed = false;
				});
		},
	},
};
</script>

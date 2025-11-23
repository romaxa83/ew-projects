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
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-calendar"
							role="tab"
							aria-selected="false"
							class="nav-link"
						>
							Truck Busy
						</a>
					</li>
				</ul>
			</div>
			<div class="ml-auto nav-tabs-clean">
				<div class="form-group mb-0">
					<div class="row">
						<div class="col text-right">
							<a
								href="/company/trucks"
								class="btn btn-outline-default mr-3 waves-effect waves-themed"
							>
								<i class="fal fa-home"></i> All Trucks
							</a>

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
									record.id
										? updating
											? 'Saving changes'
											: 'Save changes'
										: 'Create new truck'
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
				id="tab-information"
				aria-labelledby="tab-information"
				class="tab-pane fade active show"
			>
				<div class="row">
					<div class="col-lg-6">
						<div class="panel">
							<div class="panel-hdr">
								<h2>Truck Information</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label
											for="division_ids"
											class="form-label"
											><sup>*</sup> Branches</label
										>
										<select
											id="division_ids"
											class="form-control select2"
											multiple
											data-placeholder="Choose Branches"
											v-model="record.division_ids"
										>
											<option :value="null">
												-- select an option --
											</option>
											<option
												v-for="v in divisions"
												:key="v.id"
												v-bind:value="v.id"
											>
												{{ v.title }}
											</option>
										</select>
									</div>
									<div class="form-group">
										<label class="form-label" for="year"
											>Production Year</label
										>
										<select
											v-model="record.year"
											id="year"
											type="text"
											class="form-control"
										>
											<option
												v-for="year in trucksYears"
												:value="year"
											>
												{{ year }}
											</option>
										</select>
									</div>
									<div class="form-group">
										<label class="form-label">Vendor</label>
										<input
											type="text"
											class="form-control"
											v-model="record.vendor"
											placeholder="Vendor"
										/>
									</div>
									<div class="form-group">
										<label class="form-label">Model</label>
										<input
											type="text"
											class="form-control"
											v-model="record.model"
											placeholder="Model"
										/>
									</div>
									<div class="form-group">
										<label class="form-label">Color</label>
										<input
											type="text"
											class="form-control"
											v-model="record.color"
											placeholder="Color"
										/>
									</div>
									<div class="form-group">
										<label class="form-label"
											>License plate</label
										>
										<input
											type="text"
											class="form-control"
											v-model="record.l_plate"
											placeholder="License plate"
										/>
									</div>
									<div class="form-group">
										<label class="form-label">VIN</label>
										<input
											type="text"
											class="form-control"
											v-model="record.vin"
											placeholder="VIN"
										/>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="panel">
							<div class="panel-hdr">
								<h2>Dispatch</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label class="form-label" for="active"
											>Status</label
										>
										<select
											class="form-control"
											v-model.number="record.active"
											id="active"
										>
											<option value="1">In Work</option>
											<option value="0">Sold</option>
										</select>
									</div>
									<div class="form-group">
										<label class="form-label">Title</label>
										<input
											v-model="record.title"
											type="text"
											class="form-control"
											placeholder="Title"
										/>
									</div>
									<div class="form-group">
										<label class="form-label"
											>Nickname</label
										>
										<input
											v-model="record.nickname"
											type="text"
											class="form-control"
											placeholder="Truck Nickname"
										/>
										<!--                                        <span class="help-block">For Dispatch page</span>-->
									</div>
									<div class="form-group">
										<label class="form-label"
											>Dispatch Color</label
										>
										<input
											v-model="record.p_color"
											type="color"
											class="form-control"
											placeholder="Truck Color"
										/>
										<!--                                        <span class="help-block">For Dispatch page</span>-->
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-hdr">
								<h2>Partner</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label
											for="partner_id"
											class="form-label"
											>Partner</label
										>
										<select
											id="partner_id"
											class="form-control"
											data-placeholder="Choose Partner"
											v-model="record.partner_id"
										>
											<option :value="null">
												-- select an option --
											</option>
											<option
												v-for="v in partners"
												:key="v.id"
												v-bind:value="v.id"
											>
												{{ v.name }}
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-hdr">
								<h2>Notes</h2>
								<div class="panel-toolbar">
									<button
										class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed createNote"
									>
										Add Note
									</button>
								</div>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<modal-notes
										:records="record.notes"
										:in-line="false"
										@addRecord="addNoteRecord"
										@deleteRecord="deleteNoteRecord"
									></modal-notes>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div
				role="tabpanel"
				id="tab-calendar"
				aria-labelledby="tab-calendar"
				class="tab-pane fade"
			>
				<holidays-truck-employee
					v-if="!loading"
					:records.sync="record.busy_weeks_days.miscs"
					@submit="submit"
				></holidays-truck-employee>
				<year-calendar
					v-if="!loading"
					:dataSource.sync="record.busy_dates"
					@submit="submit"
				></year-calendar>
			</div>
		</div>
	</div>
</template>

<script>
import { AxiosHelper } from '@/helpers/axiosHelper';

const YearCalendar = () =>
	import(/* webpackChunkName: "YearCalendar" */ '../Settings/YearCalendar');
import HolidaysTruckEmployee from './HolidaysTruckEmployee';
import ModalNotes from '../Order/TabOverview/ClientModal/Notes';

export default {
	name: 'CompanyTrucks',
	components: {
		HolidaysTruckEmployee,
		YearCalendar,
		ModalNotes,
	},
	data() {
		return {
			loading: true,
			updating: false,
			is_changed: false,
			record: {},
			divisions: {},
			partners: {},
			records_orig: [],
			partner_id: null,
		};
	},
	computed: {
		trucksYears() {
			let take = 15;
			let currentYear = new Date().getFullYear(),
				years = [],
				startYear = currentYear - take;
			for (let i = startYear; i <= currentYear; i++) {
				years.push(startYear++);
			}

			if (years.indexOf(+this.record.year) === -1) {
				years.push(this.record.year);
			}

			return years;
		},
	},
	watch: {
		'record.busy_weeks_days.miscs': function (val, oldVal) {
			if (oldVal) {
				this.formatCalendar();
			}
		},
		record: {
			handler: function (val, oldVal) {
				if (!this.is_changed && Object.keys(oldVal).length) {
					this.is_changed = true;
				}
			},
			deep: true,
		},
	},
	mounted() {
		AxiosHelper({
			url: window.location.href,
		})
			.then(({ record, divisions, partners }) => {
				if (record) {
					this.record = record;
					this.records_orig = record.busy_dates;
					this.divisions = divisions;
					this.partners = partners;

					if (!this.record.busy_weeks_days) {
						this.record.busy_weeks_days = {
							miscs: [],
						};
					}

					this.formatCalendar();
				}
			})
			.finally(() => {
				this.loading = false;
				this.initMasks();
			});
	},
	methods: {
		addNoteRecord(payload) {
			this.record.notes.push(payload);
		},
		deleteNoteRecord(index) {
			this.$delete(this.record.notes, index);
		},
		formatCalendar() {
			let records = [];

			// Генерим PeakDays на текущий год +1
			let fromDate = moment().startOf('year');
			let toDate = fromDate.clone().add(1, 'year').endOf('year');
			let diff = toDate.diff(fromDate, 'days');
			for (let i = 0; i < diff; i++) {
				let day = moment(fromDate).add(i, 'days');
				if (this.record.busy_weeks_days.miscs.includes(day.day())) {
					records.push({
						id: null,
						name: 'Holidays periodic',
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
				let startDate = moment(item.start_date),
					endDate = moment(item.end_date),
					endTime = endDate.format('HH:mm');

				if (endTime === '00:00') {
					endTime = '23:59';
				}

				records.push({
					id: item.id,
					name: item.reason,
					details:
						startDate.format('hh:mm A') +
						' - ' +
						endDate.format('hh:mm A'),
					startDate: startDate,
					startTime: startDate.format('HH:mm'),
					endDate: endDate,
					endTime,
					randomRef: App.Miscs.generateToken(),
				});
			});

			this.record.busy_dates = records;
		},
		initMasks() {
			this.$nextTick(() => {
				let select2 = $('.select2');
				select2.select2();
				select2.on('select2:close', function (e) {
					this.dispatchEvent(
						new Event('change', { target: e.target })
					);
				});
			});
		},
		submit() {
			this.updating = true;

			let busy_dates = this.record.busy_dates
				.slice()
				.filter((item) => !item.is_virtual)
				.map((item) => {
					item.startDate = moment(item.startDate).format(
						'YYYY-MM-DD'
					);
					item.endDate = moment(item.endDate).format('YYYY-MM-DD');

					return item;
				});
			this.record.busy_dates = busy_dates;

			AxiosHelper({
				url: window.location.href + '/save',
				data: this.record,
			})
				.then(({ record, msg }) => {
					if (record) {
						this.record = record;
						this.records_orig = record.busy_dates;

						this.formatCalendar();

						App.Forms.showAlert('success', msg);

						// Костыль для рефреша данных календаря. Это печаль
						this.$nextTick(() =>
							$('.calendar-header .next').trigger('click')
						);
						this.$nextTick(() => {
							$('.calendar-header .prev').trigger('click');
							this.is_changed = false;
						});
					}
				})
				.finally(() => {
					this.updating = false;
				});
		},
	},
};
</script>

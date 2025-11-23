<template>
	<div class="modal-content">
		<div class="modal-header bg-fusion-100">
			<h5 class="modal-title">
				Services
				<span
					v-if="record.trucks || record.employees"
					:class="colorDispatch"
					class="badge"
				>
					{{
						record.dispatch_trucks_count === 0 &&
						record.dispatch_employees_count === 0
							? 'Unscheduled'
							: 'Scheduled'
					}}
				</span>
			</h5>
			<button
				type="button"
				class="close"
				data-dismiss="modal"
				aria-label="Close"
			>
				<span aria-hidden="true"><i class="fal fa-times"></i></span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row mb-3">
				<div class="col-sm-5 mb-3">
					<h3 class="mb-3">Service types:<sup>*</sup></h3>
					<div
						v-for="v in types"
						:key="v.id"
						class="custom-control custom-checkbox mb-2"
					>
						<input
							type="checkbox"
							class="custom-control-input"
							:value="v.id"
							:id="'type_' + v.id"
							v-model="record.work_types_checked"
						/>
						<label
							class="custom-control-label"
							:for="'type_' + v.id"
							>{{ v.title }}</label
						>
					</div>

					<div class="btn-group mt-4 mb-2">
						<button
							class="btn btn-xs btn-outline-danger dropdown-toggle"
							type="button"
							data-toggle="dropdown"
							aria-haspopup="true"
							aria-expanded="false"
							:disabled="hasDispatchTasks"
						>
							Start time : {{ startTimeType }}
						</button>
						<div class="dropdown-menu">
							<a
								class="dropdown-item"
								:class="{ active: !record.start_time_to }"
								href="#"
								@click.prevent="setTimeType('normal')"
								>Normal</a
							>
							<a
								class="dropdown-item"
								:class="{ active: record.start_time_to }"
								href="#"
								@click.prevent="setTimeType('range')"
								>Range</a
							>
						</div>
					</div>

					<div class="form-group mb-2">
						<label class="form-label">Start time:</label>
						<div class="d-flex">
							<time-picker
								size="large"
								:disabled="hasDispatchTasks"
								v-model="record.start_time"
								popupClassName="flex-fill"
								:placeholder="
									record.start_time_to
										? 'Start time from'
										: 'Start time'
								"
								use12-hours
								format="h:mm A"
								valueFormat="HH:mm:ss"
								:minuteStep="5"
								inputReadOnly
								showNow
								:getPopupContainer="getPopupContainer"
								:disabledHours="timepickerDisabledHours"
							/>
							<time-picker
								v-if="record.start_time_to"
								size="large"
								:disabled="hasDispatchTasks"
								v-model="record.start_time_to"
								popupClassName="flex-fill"
								:placeholder="
									record.start_time_to
										? 'Start time from'
										: 'Start time'
								"
								use12-hours
								format="h:mm A"
								valueFormat="HH:mm:ss"
								:minuteStep="5"
								inputReadOnly
								showNow
								:disabledHours="timepickerDisabledHoursRangeTo"
								:getPopupContainer="getPopupContainer"
							/>

							<!--                                    <time-picker :getPopupContainer="getPopupContainer"/>-->
						</div>
						<!--                                <div class="input-group">-->
						<!--                                    <input v-model="record.start_time" :disabled="hasDispatchTasks"-->
						<!--                                           id="add-job-timepicker" class="form-control flatpickr"-->
						<!--                                           :placeholder="record.start_time_to ? 'Start time from' : 'Start time'"-->
						<!--                                           type="text"/>-->
						<!--                                    <input v-model="record.start_time_to" :disabled="hasDispatchTasks"-->
						<!--                                           id="add-job-timepicker-to" class="form-control flatpickr"-->
						<!--                                           :class="{'content-hidden': !record.start_time_to}"-->
						<!--                                           placeholder="Start time to"-->
						<!--                                           type="text"/>-->
						<!--                                </div>-->
					</div>
					<div class="form-group mb-2">
						<label class="form-label">Service duration:</label>
						<span class="help-block" v-show="hasDispatchTasks">
							Can be eq or greater than:
							{{ record.init_duration }}
						</span>
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									<i class="fas fa-hourglass-end"></i>
								</div>
							</div>
							<input
								v-model="Duration"
								class="form-control"
								type="text"
							/>
						</div>
					</div>
					<div class="form-group mb-2">
						<label for="work_trucks" class="form-label"
							>Trucks qty:</label
						>
						<span
							class="help-block"
							v-show="record.dispatch_trucks_count"
						>
							Can be eq or greater than:
							{{ record.dispatch_trucks_count }}
						</span>
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									<i class="fas fa-truck"></i>
								</div>
							</div>
							<select
								v-model="record.trucks"
								class="form-control"
								id="work_trucks"
							>
								<option
									:value="null"
									v-if="!record.dispatch_trucks_count"
								>
									-- select an option --
								</option>
								<option
									value="0"
									v-if="!record.dispatch_trucks_count"
								>
									0
								</option>
								<option
									v-for="index in trucksRange"
									:key="index"
									v-bind:value="index"
								>
									{{ index }}
								</option>
							</select>
						</div>
					</div>
					<div class="form-group mb-2">
						<label for="works_employees" class="form-label"
							>Crew qty:</label
						>
						<span
							class="help-block"
							v-show="record.dispatch_employees_count"
						>
							Can be eq or greater than:
							{{ record.dispatch_employees_count }}
						</span>
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									<i class="fas fa-user-friends"></i>
								</div>
							</div>
							<select
								v-model="record.employees"
								class="form-control"
								id="works_employees"
							>
								<option
									:value="null"
									v-if="!record.dispatch_employees_count"
								>
									-- select an option --
								</option>
								<option
									v-for="index in employeesRange"
									:key="index"
									v-bind:value="index"
								>
									{{ index }}
								</option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-sm-7">
					<h3 class="mb-3">Date:</h3>
					<div class="flatpickr-inline">
						<input
							id="start_date"
							type="hidden"
							:disabled="hasDispatchTasks"
							v-model="record.start_date"
						/>
					</div>
					<div class="form-group mt-4">
						<label class="form-label" for="modal_notes"
							>Note:</label
						>
						<textarea
							class="form-control"
							rows="5"
							id="modal_notes"
							v-model="record.notes"
						></textarea>
					</div>
					<div
						class="form-group mt-4"
						v-if="
							record.dispatch_trucks_count ||
							record.dispatch_employees_count
						"
					>
						<div v-if="record.dispatch_trucks_count" class="mb-2">
							<span class="fw-700">Truck(s):</span>
							{{ assignedTrucks }}
						</div>
						<div v-if="record.dispatch_employees_count">
							<span class="fw-700">Crew:</span>
							{{ assignedEmployees }}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<div class="flex-grow-1">
				<button
					type="button"
					class="btn btn-secondary"
					data-dismiss="modal"
				>
					Close
				</button>
			</div>
			<div>
				<div v-if="!hasDispatchTasks" class="btn-group dropup">
					<button
						type="button"
						class="btn btn-danger dropdown-toggle waves-effect waves-themed"
						data-toggle="dropdown"
						aria-haspopup="true"
						aria-expanded="false"
					>
						Clear
					</button>
					<div class="dropdown-menu" style="">
						<a
							class="dropdown-item"
							href="#"
							@click.prevent="clearDate()"
							>Clear Date</a
						>
						<a
							class="dropdown-item"
							href="#"
							@click.prevent="clearTime()"
							>Clear Time</a
						>
						<div class="dropdown-divider"></div>
						<a
							class="dropdown-item"
							href="#"
							@click.prevent="clearAll()"
							>Clear All</a
						>
					</div>
				</div>
				<button
					v-else
					@click="removeFromDispatch()"
					type="button"
					class="btn btn-danger"
				>
					<span
						v-show="loading_removing"
						class="spinner-border spinner-border-sm"
						role="status"
						aria-hidden="true"
					></span>
					Remove assignments from Dispatch
				</button>
				<button @click="submit()" type="button" class="btn btn-primary">
					<span
						v-show="loading"
						class="spinner-border spinner-border-sm"
						role="status"
						aria-hidden="true"
					></span>
					{{
						record.id
							? loading
								? 'Saving changes'
								: 'Save changes'
							: 'Create'
					}}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';
import { TimePicker } from 'ant-design-vue';
import 'ant-design-vue/lib/time-picker/style/index.css';

import peaksDatesCalendarMixin from '@/mixins/peaksDatesCalendarMixin';
import { AxiosHelper } from '@/helpers/axiosHelper';

let fp, fp_time, fp_time_to;
const range = (start, stop, step = 1) =>
	Array.from(
		{ length: (stop - start) / step + 1 },
		(_, i) => start + i * step
	);

export default {
	name: 'WorksModal',
	components: {
		TimePicker,
	},
	mixins: [peaksDatesCalendarMixin],
	props: {
		record: {
			type: Object,
			required: true,
		},
		types: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			loading: false,
			timeLabel: 'Start time',
			loading_removing: false,
		};
	},
	computed: {
		Duration: {
			get() {
				if (this.record.id) return this.record.duration;
				else {
					// Дефолтные данные продолжительности
					let def_value = parseFloat(this.settings.default_duration);

					let type = this.estimate.type;
					if (
						type === 'local' &&
						this.estimate[type] &&
						parseFloat(this.estimate[type].hours_max) >= def_value
					) {
						def_value = parseFloat(this.estimate[type].hours_max);
					}
					this.record.duration = def_value;

					return def_value;
				}
			},
			set(value) {
				this.record.duration = value;
			},
		},
		assignedEmployees() {
			return this.record.dispatch_employees
				.map((item) => {
					return (
						item.employee.name +
						(item.employee.l_name !== null
							? ` ${item.employee.l_name}`
							: '')
					);
				})
				.join(', ');
		},
		assignedTrucks() {
			return this.record.dispatch_trucks
				.map((item) => {
					return item.truck.title;
				})
				.join(', ');
		},
		colorDispatch() {
			return {
				'badge-warning': !this.isCompleted,
				'badge-success': this.isCompleted,
			};
		},
		// Есть размеченные Траки или Команда
		hasDispatchTasks() {
			if (
				(this.record.trucks && this.record.dispatch_trucks_count) ||
				(this.record.employees && this.record.dispatch_employees_count)
			) {
				return true;
			}
			return false;
		},
		isCompleted() {
			return (
				this.record.dispatch_trucks_count === this.record.trucks &&
				this.record.dispatch_employees_count === this.record.employees
			);
		},
		settings() {
			return this.settingsEstimate('any');
		},
		startTimeType() {
			return this.record.start_time_to ? 'Range' : 'Normal';
		},
		trucksRange() {
			let from = 1;
			if (this.record.dispatch_trucks_count)
				from = this.record.dispatch_trucks_count;

			return [...range(from, 10)];
		},
		employeesRange() {
			let from = 1;
			if (this.record.dispatch_employees_count)
				from = this.record.dispatch_employees_count;

			return [...range(from, 20)];
		},
		...mapGetters({
			settingsEstimate: 'order/settingsEstimate',
			estimate: 'order/estimate',
		}),
	},
	mounted() {
		$('#modal-works').modal('show');

		$('#modal-works')
			.on('shown.bs.modal', () => {
				fp = flatpickr('#start_date', {
					inline: true,
					onChange: (selectedDates, dateStr, instance) => {
						// Запрещаем менять данные если назначены работы
						if (this.hasDispatchTasks)
							instance.setDate(this.record.start_date);
					},
				});

				// fp_time = flatpickr('#add-job-timepicker', {
				//     enableTime: true,
				//     noCalendar: true,
				//     altInput: true,
				//     altFormat: 'h:i K',
				//     dateFormat: 'H:i:ss',
				//     time_24hr: false,
				//     minuteIncrement: 15,
				//     onValueUpdate: (selectedDates, dateStr, instance) => {
				//         if (!this.record.start_time)
				//             this.record.start_time = dateStr;
				//     }
				// });
				//
				// this.initTimeTo();

				this.initPeaksDatesCalendarMixin(fp, this, $('#start_date')[0]);
				// Затягиваем PeaksDates, после открываем модалку
				window.VueApp.$store.dispatch('initPeaksDates').then(() => {
					// Тригерим обновление календаря
					fp.redraw();
					$('#modal-works .loader-div').hide();
				});
			})
			.on('hide.bs.modal', () => {
				if (fp) fp.destroy();
				if (fp_time) fp_time.destroy();

				if (fp_time_to) fp_time_to.destroy();
			});
	},
	methods: {
		clearAll() {
			this.clearTime();
			this.clearDate();
		},
		clearDate() {
			this.record.start_date = null;
			fp.clear();
		},
		clearTime() {
			this.record.start_time = null;
			this.record.start_time_to = null;
			if (fp_time) fp_time.clear();
			if (fp_time_to) fp_time_to.clear();
		},
		getPopupContainer(triggerNode) {
			return $(triggerNode).closest('.modal-body')[0];
		},
		initTimeTo() {
			// fp_time_to = flatpickr('#add-job-timepicker-to', {
			//     enableTime: true,
			//     noCalendar: true,
			//     altInput: true,
			//     altFormat: 'h:i K',
			//     dateFormat: 'H:i:ss',
			//     time_24hr: false,
			//     minuteIncrement: 15,
			//     onValueUpdate: (selectedDates, dateStr, instance) => {
			//         if (!this.record.start_time_to)
			//             this.record.start_time_to = dateStr;
			//     }
			// });
		},
		removeFromDispatch() {
			Swal.fire({
				title: 'Are you sure?',
				text: 'Remove all assignments of Trucks and Employees from this service',
				icon: 'warning',
				showCancelButton: true,
				reverseButtons: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, do it!',
				target: document.getElementById('modal-works'),
			}).then((result) => {
				if (result.value === true) {
					this.loading_removing = true;
					AxiosHelper({
						url: '/orders/works/remove-assignments',
						data: {
							id: this.record.id,
							order_id: this.record.order_id,
						},
					})
						.then(({ records }) => {
							this.$store.dispatch('order/updateWorks', records);

							$('#modal-works').modal('hide');
							App.Forms.showAlert(
								'success',
								'All assignments removed successfully'
							);
						})
						.finally(() => (this.loading_removing = false));
				}
			});
		},
		setTimeType(type) {
			if (type === 'normal') {
				this.timeLabel = 'Start time';
				this.record.start_time_to = null;
				if (fp_time_to) fp_time_to.destroy();
			} else {
				this.timeLabel = 'Start time range';
				// Инициализация календаря + задаем второе время +2ч
				setTimeout(() => this.initTimeTo());

				let time_to = '12:00:00';
				if (this.record.start_time) {
					let s_time = moment(this.record.start_time, 'HH:mm:ss').add(
						2,
						'hours'
					);
					if (parseInt(s_time.clone().format('HH')) < 24)
						time_to = s_time.format('HH:mm:ss');
				}
				this.record.start_time_to = time_to;
			}
			$('#modal-works .modal-title').trigger('click');
		},
		submit() {
			this.loading = true;
			AxiosHelper({
				url: '/orders/works/save',
				data: this.record,
			})
				.then(({ records }) => {
					this.$store.dispatch('order/updateWorks', records);
					$('#modal-works').modal('hide');

					this.$emit('recalculate');
				})
				.finally(() => (this.loading = false));
		},
		timepickerDisabledHours() {
			// return [0, 1, 2, 3, 4, 5, 6, 7, 21, 22, 23];
			return [];
		},
		timepickerDisabledHoursRangeTo() {
			return [];
			// return [0, 1, 2, 3, 4, 5, 6, 7, 21, 22, 23];
		},
	},
};
</script>

<style scoped>
.ant-time-picker {
	-webkit-box-flex: 1 !important;
	-ms-flex: 1 1 auto !important;
	flex: 1 1 auto !important;
	flex-grow: 1 !important;
	flex-shrink: 1 !important;
	flex-basis: auto !important;
}
</style>

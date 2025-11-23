<template>
	<div
		ref="workpanelref"
		class="panel-tag fs-sm px-2 py-2 rounded-0 position-relative"
		:class="computedStyle"
		:data-duration="Duration"
		:data-id="record.id"
		:data-random-ref="record.randomRef"
		:data-start="startOfTime"
	>
		<div
			v-if="hasPreviousDayDispatch"
			class="d-scheduler-only position-absolute previous-day-work-link"
		>
			<div class="d-flex align-items-center h-100">
				<i
					class="fal fa-angle-left fa-3x color-white cursor-pointer"
					@click="clickPrevDate"
				></i>
			</div>
		</div>

		<div v-if="loading" class="d-flex justify-content-center">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<template v-else>
			<template v-if="placement === 'trucks'">
				<div class="d-flex">
					<div class="fs-md mb-sm-1 text-nowrap">
						<ul class="tag-list-inline" title="Services">
							<li
								v-for="workType in record.work_types"
								:key="workType.id"
							>
								{{ worksTypes[workType.work_type_id].title }}
							</li>
						</ul>
					</div>
					<div class="fw-700 ml-2 pt-15 text-nowrap">
						<a
							:href="'/orders/' + record.order_id"
							class="work-modal-link"
							target="_blank"
							>#{{ record.order_id }}-{{ position }}</a
						>
						({{ record.order.estimate.type }})
					</div>
					<div
						class="position-absolute d-scheduler-only"
						style="right: 6px"
					>
						<button
							v-if="isAllowedRemove"
							@click="detachTruck()"
							class="btn btn-danger btn-xs btn-icon btn-remove-work"
						>
							<i class="fal fa-times"></i>
						</button>
					</div>
					<h6 class="mt-0 mb-0 ml-1">
						<span
							class="badge badge-primary badge-status-overflow"
							:style="{ 'background-color': orderStatus.color }"
						>
							{{ orderStatus.title }}
						</span>
					</h6>
					<!--                    <div v-if="record.start_time && record.duration" class="scheduler-hidden ml-auto pl-2">-->
					<!--                        <template v-if="record.start_time_to">Start: {{ timeFrom }}-{{ timeFromTo }} End: {{-->
					<!--                                timeTo-->
					<!--                            }}-->
					<!--                        </template>-->
					<!--                        <template v-else>{{ timeFrom }}-{{ timeTo }}</template>-->
					<!--                    </div>-->
					<!--                    <div v-else class="scheduler-hidden ml-auto pl-2 fw-700 text-danger">-->
					<!--                        Empty Start Time or Duration-->
					<!--                    </div>-->
				</div>
				<div class="d-flex">
					<div
						class="d-flex overflow-no-wrap"
						:title="
							'Trucks: ' +
							assignedTrucks +
							'/' +
							Trucks +
							' Crew: ' +
							crewCount +
							'/' +
							Employees +
							' Dur: ' +
							record.duration +
							'h [' +
							sinceTillDatetimePlainText +
							']'
						"
					>
						<div v-if="Trucks" class="mr-1">
							<span
								:class="{
									'badge badge-info': placement === 'trucks',
								}"
								><i class="fas fa-truck fs-nano"></i>
								{{ assignedTrucks }}/{{ Trucks }}</span
							>
						</div>
						<div
							v-if="Employees"
							class="mr-1 has-tooltip"
							data-html="true"
							:data-title="assignedCrewMembers"
							data-placement="top"
						>
							<span
								:class="{
									'badge badge-danger':
										placement === 'crews' &&
										+crewCount < +Employees,
									'badge badge-success':
										placement === 'crews' &&
										+crewCount == +Employees,
									'badge badge-warning':
										placement === 'crews' &&
										+crewCount > +Employees,
								}"
							>
								<i class="fas fa-user fs-nano"></i>
								{{ crewCount }}/{{ Employees }}</span
							>
						</div>
						<div class="mr-1">
							<span>
								<i class="fas fa-hourglass-end fs-nano"></i>
								{{ ceilDuration }}h [<span
									v-if="$store.state.dispatch.isLoaded"
									v-html="sinceDatetime"
								></span
								>]
							</span>
						</div>
					</div>
				</div>
				<div
					class="d-flex mb-0 overflow-no-wrap"
					:title="
						'(' + record.order.estimate.type + ') ' + priceDetails
					"
				>
					<div
						:title="`Client` + clientTitle"
						class="font-weight-bold"
					>
						{{ clientTitle }}
					</div>
					<div class="fw-300 ml-2" v-html="priceDetails"></div>
					<!-- Deposit -->
					<h6 class="ml-2 mb-0">
						<span
							class="badge"
							:class="{
								'badge-success': totalPaid,
								'badge-danger': !totalPaid,
							}"
						>
							${{ totalPaid }}
						</span>
					</h6>
				</div>
			</template>
			<template v-else-if="placement === 'crews'">
				<div class="d-flex">
					<div class="fw-700">
						<a
							:href="'/orders/' + record.order_id"
							class="work-modal-link"
							target="_blank"
							>#{{ record.order_id
							}}<span v-if="position">-{{ position }}</span></a
						>
						<!--                        <span v-if="totalPaid" class="ml-1 mr-1 fw-700 text-danger"-->
						<!--                              :title="`Total paid: $${totalPaid}`">$</span>-->
						({{ record.order.estimate.type }})
					</div>
					<!--                    <div v-if="record.start_time && record.duration" class="scheduler-hidden ml-auto pl-2">-->
					<!--                        <template v-if="record.start_time_to">Start: {{ timeFrom }}-{{ timeFromTo }} End: {{-->
					<!--                                timeTo-->
					<!--                            }}-->
					<!--                        </template>-->
					<!--                        <template v-else>{{ timeFrom }}-{{ timeTo }}</template>-->
					<!--                    </div>-->
					<!--                    <div v-else class="scheduler-hidden ml-auto pl-2 fw-700 text-danger">-->
					<!--                        Empty Start Time or Duration-->
					<!--                    </div>-->
				</div>
				<div class="d-flex mb-0">
					<div class="fw-300" v-html="priceDetails"></div>
					<!-- Deposit -->
					<h6 class="ml-2 mb-0">
						<span
							class="badge"
							:class="{
								'badge-success': totalPaid,
								'badge-danger': !totalPaid,
							}"
						>
							${{ totalPaid }}
						</span>
					</h6>
				</div>
				<ul class="tag-list-inline" title="Services">
					<li
						v-for="workType in record.work_types"
						:key="workType.id"
					>
						<b>{{ worksTypes[workType.work_type_id].title }}</b>
					</li>
				</ul>
				<div :title="`Client` + clientTitle" class="font-weight-bold">
					{{ clientTitle }}
				</div>
			</template>
			<div
				v-if="reservedTime"
				class="reserved-time"
				:style="{ width: reservedTime }"
			></div>
			<template v-if="placement === 'crews'">
				<ul class="tag-list-inline" title="Trucks">
					<li
						v-for="v in record.dispatch_trucks"
						:key="v.id"
						class="mx-sm-n1 mb-sm-2 pr-2"
						:style="{ color: trucks[v.truck_id].p_color }"
					>
						<code>{{ trucks[v.truck_id].title }}</code>
					</li>
				</ul>
			</template>
			<div class="scheduler-hidden">
				<i class="fas fa-cube fs-nano"></i>
				{{ record.order.sizing_volume }} CuFT /
				{{ record.order.sizing_weight }} lb
			</div>
			<div class="d-flex" v-if="placement === 'crews'">
				<div
					class="d-flex overflow-no-wrap"
					:title="
						'Trucks: ' +
						assignedTrucks +
						'/' +
						Trucks +
						' Crew: ' +
						crewCount +
						'/' +
						Employees +
						' Dur: ' +
						record.duration +
						'h [' +
						sinceTillDatetimePlainText +
						']'
					"
				>
					<div v-if="Trucks" class="mr-1">
						<span
							:class="{
								'badge badge-info': placement === 'trucks',
							}"
							><i class="fas fa-truck fs-nano"></i>
							{{ assignedTrucks }}/{{ Trucks }}</span
						>
					</div>
					<div
						v-if="Employees"
						class="mr-1 has-tooltip"
						data-html="true"
						:data-title="assignedCrewMembers"
						data-placement="top"
					>
						<span
							:class="{
								'badge badge-danger':
									placement === 'crews' &&
									+crewCount < +Employees,
								'badge badge-success':
									placement === 'crews' &&
									+crewCount == +Employees,
								'badge badge-warning':
									placement === 'crews' &&
									+crewCount > +Employees,
							}"
						>
							<i class="fas fa-user fs-nano"></i>
							{{ crewCount }}/{{ Employees }}</span
						>
					</div>
					<div class="mr-1">
						<span>
							<i class="fas fa-hourglass-end fs-nano"></i>
							{{ record.duration }}h [<span
								v-if="$store.state.dispatch.isLoaded"
								v-html="sinceTillDatetime"
							></span
							>]
						</span>
					</div>
				</div>
			</div>
			<div class="mt-1 mb-0">
				<div
					class="d-flex flex-wrap"
					v-if="
						record.order.waypoints && record.order.waypoints.length
					"
				>
					<div
						class="badge badge-info mr-1 mb-1"
						v-for="(waypoint, ind) in record.order.waypoints"
						:key="ind"
					>
						<i class="fas fa-map-marker-alt fs-nano"></i>
						<span>
							{{ waypoint.zip }}

							<!--                            {{-->
							<!--                                record.order.waypoints[-->
							<!--                                    record.order.waypoints.length - 1-->
							<!--                                ].zip-->
							<!--                            }}-->
						</span>
						<span v-show="waypoint.flights || waypoint.has_elevator"
							>[</span
						>
						<span v-if="waypoint.flights"
							><i class="fal fa-layer-group fs-nano"></i>
							{{ waypoint.flights.title }}</span
						>
						<span v-if="waypoint.has_elevator"
							><i class="fal fa-check fs-nano"></i> Elevator</span
						>
						<span v-show="waypoint.flights || waypoint.has_elevator"
							>]</span
						>
					</div>
				</div>
				<div v-else class="badge badge-info mr-1"># No waypoints</div>
			</div>
			<h6 class="mt-1 mb-0" v-if="placement === 'crews'">
				<span
					class="badge badge-primary badge-status-overflow"
					:style="{ 'background-color': orderStatus.color }"
				>
					{{ orderStatus.title }}
				</span>
			</h6>
			<template>
				<div v-if="placement === 'crews'" class="d-scheduler-only mt-1">
					<i>{{ notifyLabel }}</i>
					<!--                    <button @click="notify()" type="button" class="btn btn-xs waves-effect waves-themed notify-btn"-->
					<!--                            :class="notifyClass">-->
					<!--                       -->
					<!--                    </button>-->
				</div>
				<div
					v-if="isAllowedRemove"
					class="custom-control custom-checkbox"
					:class="{
						'crew-work-checkbox': placement === 'crews',
						'truck-work-checkbox': placement === 'trucks',
					}"
				>
					<input type="checkbox" class="custom-control-input" />
					<label class="custom-control-label"></label>
				</div>
			</template>
		</template>
		<div
			v-if="hasNextDayDispatch"
			class="d-scheduler-only position-absolute next-day-work-link"
		>
			<div class="d-flex align-items-center h-100">
				<i
					class="fal fa-angle-right fa-3x color-white cursor-pointer"
					@click="clickNextDate"
				></i>
			</div>
		</div>
	</div>
</template>

<script>
import cloneDeep from 'lodash.clonedeep';
import { mapGetters } from 'vuex';

export default {
	name: 'WorkPanel',
	props: {
		id: {
			type: Number,
			default: null,
		},
		originRecord: {
			type: Object,
			default: null,
		},
		placement: {
			type: String,
			default: 'trucks',
		},
		position: {
			type: Number,
			default: 0,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			record: {},
		};
	},
	computed: {
		clientTitle() {
			return this.record.order.client
				? `${this.record.order.client.name} ${this.record.order.client.lname}`
				: 'No client';
		},
		hasPreviousDayDispatch() {
			// if($(this.$refs.WorkPanelRef).parents('.crew-works').length)
			//     return false;
			if (
				Object.keys(this.record).length &&
				this.record.start_time &&
				this.record.start_date
			) {
				const startDT = moment(
					this.record.start_date + ' ' + this.record.start_time
				);
				// const endDT = moment(startDT).add(parseInt(this.record.duration, 10), 'h');
				const currentDay = moment(
					this.$store.state.dispatch.dispatchDay
				);
				if (startDT.isBefore(currentDay, 'day')) {
					return true;
				}
			}
			return false;
		},
		hasNextDayDispatch() {
			if (
				Object.keys(this.record).length &&
				this.record.start_date &&
				this.record.start_time &&
				this.record.duration
			) {
				const startDT = moment(
					this.record.start_date + ' ' + this.record.start_time
				);
				const endDT = moment(startDT).add(
					parseInt(this.record.duration, 10),
					'h'
				);
				const currentDay = moment(
					this.$store.state.dispatch.dispatchDay
				);
				if (endDT.isAfter(currentDay, 'day')) {
					return true;
				}
			}
			return false;
		},
		sinceTillDatetimePlainText() {
			let title = '';
			if (
				Object.keys(this.record).length &&
				this.record.start_time &&
				this.record.duration
			) {
				const startDT = moment(
					this.record.start_date + ' ' + this.record.start_time
				);
				const endDT = moment(startDT).add(
					parseInt(this.record.duration - 1, 10),
					'h'
				);
				if (startDT.isSame(endDT, 'day'))
					title = startDT.format('ha') + ' to ' + endDT.format('ha');
				else
					title =
						startDT.format('MMM Do, ha') +
						' to ' +
						endDT.format('MMM Do, ha');

				if (this.record.start_time_to) {
					const startRangeDT = moment(
						this.record.start_date + ' ' + this.record.start_time_to
					);
					const endRangeDT = moment(startRangeDT).add(
						parseInt(this.record.duration, 10),
						'h'
					);
					if (startDT.isSame(endDT, 'day'))
						title =
							startDT.format('h') +
							'-' +
							startRangeDT.format('ha') +
							' to ' +
							endDT.format('h') +
							'-' +
							endRangeDT.format('ha');
					else
						title =
							startDT.format('MMM Do, ha') +
							' to ' +
							endDT.format('MMM Do, ha');
				}
			} else {
				title = 'w/o startTime';
			}
			return title;
		},
		sinceTillDatetime() {
			let title = '';
			if (
				Object.keys(this.record).length &&
				this.record.start_time &&
				this.record.duration
			) {
				const startDT = moment(
					this.record.start_date + ' ' + this.record.start_time
				);
				const endDT = moment(startDT).add(
					+this.record.duration * 60,
					'm'
				);

				if (startDT.isSame(endDT, 'day'))
					title =
						startDT.format('h:mma') +
						' <b>to</b> ' +
						endDT.format('h:mma');
				else
					title =
						startDT.format('MMM Do, h:mma') +
						' <b>to</b> ' +
						endDT.format('MMM Do, h:ma');
				//
				if (this.record.start_time_to) {
					const startRangeDT = moment(
						this.record.start_date + ' ' + this.record.start_time_to
					);
					const endRangeDT = moment(startRangeDT).add(
						+this.record.duration - 1,
						'h'
					);
					if (startDT.isSame(endDT, 'day'))
						title =
							startDT.format('h:mm') +
							'-' +
							startRangeDT.format('h:mma') +
							' <b>to</b> ' +
							endDT.format('h:mm') +
							'-' +
							endRangeDT.format('h:mma');
					else
						title =
							startDT.format('MMM Do, h:mma') +
							' <b>to</b> ' +
							endDT.format('MMM Do, h:mma');
				}
			} else {
				title = '<span class="fw-700 text-danger">w/o startTime</span>';
			}
			return title;
		},
		sinceDatetime() {
			let title = 'from ';
			if (
				Object.keys(this.record).length &&
				this.record.start_time &&
				this.record.duration
			) {
				const startDT = moment(
					this.record.start_date + ' ' + this.record.start_time
				);
				const endDT = moment(startDT).add(
					+this.record.duration * 60,
					'm'
				);

				if (startDT.isSame(endDT, 'day'))
					title += startDT.format('h:mma').replace(':00', '');
				else
					title += startDT.format('MMM Do, h:mma').replace(':00', '');

				if (this.record.start_time_to) {
					const startRangeDT = moment(
						this.record.start_date + ' ' + this.record.start_time_to
					);
					const endRangeDT = moment(startRangeDT).add(
						+this.record.duration - 1,
						'h'
					);
					if (startDT.isSame(endDT, 'day'))
						title +=
							startDT.format('h:mm') +
							'-' +
							startRangeDT.format('h:mma') +
							' <b>to</b> ' +
							endDT.format('h:mm') +
							'-' +
							endRangeDT.format('h:mma');
					else
						title += startDT
							.format('MMM Do, h:mma')
							.replace(':00', '');
				}
			} else {
				title = '<span class="fw-700 text-danger">w/o startTime</span>';
			}
			return title;
		},
		// only if current dispatch date
		isAllowedRemove() {
			return (
				this.canManage &&
				this.$store.state.dispatch.dispatchDay == this.record.start_date
			);
		},
		orderStatus() {
			return this.record.order.status;
		},
		Duration() {
			return Math.floor(this.record.dispatch_duration).toFixed();
		},
		ceilDuration() {
			return this.record.duration.replace('.0', '');
		},
		Employees() {
			return this.record.employees ?? 0;
		},
		Trucks() {
			return this.record.trucks ?? 0;
		},
		assignedCrewMembers() {
			if (this.work.dispatch_employees) {
				return this.work.dispatch_employees
					.map(function (item) {
						if (item.employee)
							return (
								item.employee.name + ' ' + item.employee.l_name
							);
						return '';
					})
					.join('<br>');
			}
			return '';
		},
		crewCount() {
			if (this.work && this.work.dispatch_employees)
				return this.work.dispatch_employees.filter(function (item) {
					return item.employer_id;
				}).length;
			return 0;
		},
		assignedTrucks() {
			return this.work.dispatch_trucks.filter(function (item) {
				return item.truck_id;
			}).length;
		},
		computedStyle() {
			let styles = {
				'work-snippet':
					this.record.start_time && this.record.duration
						? true
						: false,
				'date-empty':
					!this.record.start_time || !this.record.duration
						? true
						: false,
				'not-allow-remove': !this.isAllowedRemove,
			};

			// Кастомные стили для плитки Crews
			if (!this.loading && this.placement === 'crews')
				styles = {
					'mb-1': true,
					'crew-half':
						this.crewCount > 0 &&
						this.crewCount < this.record.employees,
					'crew-completed': this.crewCount >= this.record.employees,
					'crew-empty': this.crewCount === 0,
					...styles,
				};

			return styles;
		},
		hasNotify() {
			return this.record.miscs && this.record.miscs.notify ? true : false;
		},
		notifyLabel() {
			let txt = '';
			if (this.hasNotify) {
				let date = moment.utc(this.record.miscs.notify.date).local();
				txt +=
					'Notify sent at ' +
					(date.isSame(moment(), 'day')
						? date.format('hh:mm A')
						: date.format('hh:mm A. MMM DD, YYYY')) +
					'';
			}

			return txt;
		},
		priceDetails() {
			let txt =
				this.record.order.estimate.type === 'interstate'
					? `<b class="text-capitalize fw-700 mr-1">${this.record.order.estimate.interstate?.estimate_rate}</b>`
					: '';

			let type = this.record.order.estimate.type;

			txt += `$${
				this.record.order.estimate[type]
					? this.record.order.estimate[type].rate
					: 'n/a'
			}`;
			if (type === 'local') txt += '';
			else if (type === 'interstate') txt += '/1 cbFt';
			else if (type === 'intrastate') txt += '/100 lbs';

			if (this.record.order.estimate?.travel_fee)
				txt +=
					' + ' +
					(this.record.order.estimate.fee_type === 'sum'
						? '$'
						: '%') +
					this.record.order.estimate.travel_fee;

			return txt;
		},
		reservedTime() {
			let percents = 0;
			if (this.record.start_time_to) {
				let maxDuration = parseInt(
					moment(this.record.start_time_to, 'H:mm:ss')
						.add(parseInt(this.record.duration), 'hours')
						.format('h')
				);
				let minDuration = parseInt(
					moment(this.record.start_time, 'H:mm:ss')
						.add(parseInt(this.record.duration), 'hours')
						.format('h')
				);

				percents = (minDuration / maxDuration - 1) * 100 * -1;
			}

			return percents + '%';
		},
		startOfTime() {
			return this.record.dispatch_col_start;
		},
		totalPaid() {
			let paid = this.record.order.payments.reduce((sum, item) => {
				return sum + Number(item.amount);
			}, 0);
			return Number((Math.round(paid * 100) / 100).toFixed(2));
		},
		work() {
			return this.works[this.record.id];
		},
		worksTypes() {
			return this.types('works');
		},
		...mapGetters({
			types: 'dispatch/getTypes',
			works: 'dispatch/getWorks',
			trucks: 'dispatch/getTrucks',
			virtualWorks: 'dispatch/virtualWorks',
		}),
	},
	mounted() {
		if (this.originRecord) {
			this.record = cloneDeep(this.originRecord);
			this.loading = false;
		} else {
			this.getStoreData();
		}
	},
	methods: {
		clickNextDate() {
			$('#dispatch-next-date').trigger('click');
		},
		clickPrevDate() {
			$('#dispatch-prev-date').trigger('click');
		},
		async detachTruck() {
			const type = 'truck';
			await window.Dispatch.saveAndDraw({
				type,
				randomRef: this.record.randomRef,
				work_id: this.record.id,
				entity_id: null,
			});

			Dispatch.unselectWorkTimeline(type);
			Dispatch.clearCheckedWorkers(type);
			Dispatch.uncheckCheckboxes(type);
		},
		// Достать с хранилища, если вызов standalone
		getStoreData() {
			this.$store.dispatch('dispatch/isWorksLoaded').then(() => {
				// Достать по ID + Позиции
				this.record = this.virtualWorks(this.placement)
					.filter((item) => {
						return item.id === this.id;
					})
					.filter((item) => {
						return item.position === this.position;
					})
					.shift();

				this.loading = false;
			});
		},
		notify() {
			let employee_id = this.record.employee_id;
			if (!employee_id) {
				// Из-за клона при назначении не долетает employee_id
				employee_id = this.originRecord.employer_id;
			}

			axios
				.post('/dispatch/notify', {
					order_id: this.record.order.id,
					work_id: this.record.id,
					employee_id,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						if (!this.record.miscs)
							this.record.miscs = {
								notify: {},
							};

						this.record.miscs.notify = {
							date: moment(),
						};
						App.Forms.showAlert('success', 'Notify sent');
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				})
				.finally(() => (this.loading = false));
		},
	},
};
</script>

<style scoped>
.pt-15 {
	padding-top: 0.1rem !important;
}

.btn-remove-work {
	opacity: 0.2;
}

.btn-remove-work:hover {
	opacity: 1;
}

.previous-day-work-link {
	left: 0;
	top: 0;
	height: 100%;
	width: 15px;
	background: rgba(29, 201, 183, 0.2);
}

.next-day-work-link {
	right: 0;
	top: 0px;
	height: 100%;
	width: 15px;
	background: rgba(29, 201, 183, 0.2);
}

.badge-status-overflow {
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;
	max-width: 140px;
}
</style>

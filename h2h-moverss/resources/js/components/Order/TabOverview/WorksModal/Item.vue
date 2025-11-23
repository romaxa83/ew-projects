<template>
	<div
		class="card mb-2"
		:class="{ 'work-has-multiple-times': record.start_time_to }"
	>
		<div class="card-body">
			<div class="d-flex">
				<div class="flex-grow-1">
					<h5 class="card-title mr-2 mb-2 d-inline-block">
						<a
							v-if="canManageItem"
							:href="`#id_${record.id}`"
							@click.prevent="clickModal(i)"
						>
							<span>{{ workLinkText }}</span>
						</a>
						<span v-else>{{ workLinkText }}</span>
					</h5>
					<span v-if="record.peak_date" class="badge badge-danger">{{
						record.peak_date.type.title
					}}</span>
				</div>
				<div
					:class="{ 'cursor-pointer': record.start_date }"
					@click="openDispatch()"
					v-if="record.trucks || record.employees"
				>
					<span
						:class="dispatchColor"
						class="badge"
						v-text="dispatchText"
					></span>
				</div>
				<div class="mt-n2" v-if="canManageItem">
					<button
						data-toggle="dropdown"
						aria-expanded="false"
						class="btn py-1 px-2 waves-effect waves-themed"
					>
						<i class="fal fa-2x fa-ellipsis-v"></i>
					</button>
					<div
						class="dropdown-menu dropdown-menu-animated dropdown-menu-right"
					>
						<button @click="clickModal(i)" class="dropdown-item">
							Edit
						</button>
						<button @click="cloneRecord(i)" class="dropdown-item">
							Copy
						</button>
						<button
							@click="removeRecord(record.id)"
							class="dropdown-item btn-danger"
						>
							Remove
						</button>
					</div>
				</div>
			</div>
			<div class="d-flex">
				<div class="flex-fill d-flex">
					<div
						v-for="vt in record.work_types"
						:key="vt.work_type_id"
						class="mr-1"
					>
						<span class="badge badge-info">
							{{ types[vt.pivot.work_type_id].title }}
						</span>
					</div>
				</div>
				<!--                <button @click="clickModal(i)" v-for="vt in record.work_types" :key="vt.work_type_id"-->
				<!--                        type="button"-->
				<!--                        class="btn btn-sm btn-info waves-effect waves-themed mr-1 mb-1">-->
				<!--                    {{ types[vt.pivot.work_type_id].title }}-->
				<!--                </button>-->
			</div>
			<div v-if="+assignedEmployeesCount > 0" class="fs-xs mt-2">
				<b>Crew:</b> {{ assignedEmployees }}
			</div>
			<div v-if="+assignedTrucksCount > 0" class="fs-xs mt-1">
				<b>Truck(s):</b> {{ assignedTrucks }}
			</div>
			<div
				v-if="record.notes"
				class="panel-tag position-relative mt-3 mb-1 pt-4 fs-sm"
			>
				{{ record.notes }}
				<div
					class="fs-xs opacity-70 pt-1 pr-2 position-absolute pos-right pos-top color-success-700"
				>
					{{ record.notes_by | managerName }},
					{{ record.notes_created_at | formatDate }}
				</div>
			</div>
		</div>
		<div class="card-footer py-2">
			<span class="fs-sm">
				<span v-if="record.duration" class="ml-2">
					<i class="fas fa-hourglass-half"></i>
					{{ +record.duration }} hrs
				</span>
				<span class="ml-2" v-if="Trucks || (!Employees && !Trucks)">
					<i class="fas fa-truck" :class="EmptyEmployeesTrucks"></i>
					{{ assignedTrucksCount }} / {{ Trucks }}
				</span>
				<span class="ml-2" v-if="Employees || (!Employees && !Trucks)">
					<i
						class="fas fa-user-friends"
						:class="EmptyEmployeesTrucks"
					></i>
					{{ assignedEmployeesCount }} / {{ Employees }}
				</span>
			</span>
		</div>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';

export default {
	name: 'Item',
	filters: {
		formatDate,
		managerName,
	},
	props: {
		i: {
			type: Number,
			required: true,
		},
		record: {
			type: Object,
			required: true,
		},
		types: {
			type: Object,
			required: true,
		},
		canManageItem: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		workLinkText() {
			let record = this.record;
			if (record.start_date && !record.start_time) {
				return formatDate(
					record.start_date,
					'MMMM Do, YYYY',
					'YYYY-MM-DD',
					true
				);
			} else if (record.start_date && record.start_time) {
				return formatDate(
					record.start_date + ' ' + record.start_time,
					'MMMM Do, YYYY LT',
					null,
					true
				);
			} else if (record.start_time_to) {
				return formatDate(
					record.start_time_to,
					'hh:mm A',
					'HH:mm:ss',
					true
				);
			}
			return '[No datetime]';
		},
		assignedTrucksCount() {
			return this.record.dispatch_trucks_count
				? this.record.dispatch_trucks_count
				: 0;
		},
		assignedEmployeesCount() {
			return this.record.dispatch_employees_count
				? this.record.dispatch_employees_count
				: 0;
		},
		assignedTrucks() {
			if (this.record.dispatch_trucks)
				return this.record.dispatch_trucks
					.map((item) => {
						return item?.truck?.title;
					})
					.join(', ');
			return '';
		},
		assignedEmployees() {
			if (this.record.dispatch_employees)
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
			return '';
		},

		Employees() {
			return this.record.employees ?? 0;
		},
		EmptyEmployeesTrucks() {
			return {
				'text-danger': !this.Trucks && !this.Employees,
			};
		},
		Trucks() {
			return this.record.trucks ?? 0;
		},
		dispatchColor() {
			return {
				'fs-md': true,
				'badge-warning': !this.isCompleted && this.record.in_dispatch,
				'badge-success': this.isCompleted && this.record.in_dispatch,
				'badge-danger': !this.record.in_dispatch,
			};
		},
		dispatchText() {
			if (this.record.in_dispatch) {
				return !this.isCompleted ? 'Scheduling' : 'Scheduled';
			}
			return 'Do not dispatch';
		},
		isCompleted() {
			return (
				this.record.dispatch_trucks_count === this.Trucks &&
				this.record.dispatch_employees_count === this.Employees
			);
		},
	},
	methods: {
		clickModal(index) {
			this.$emit('clickModal', index);
		},
		cloneRecord(index) {
			this.$emit('cloneRecord', index);
		},
		openDispatch() {
			if (this.record.start_date) {
				let url = '/dispatch?start_date=' + this.record.start_date;
				window.open(url, '_blank').focus();
			}
		},
		removeRecord(id) {
			// Есть задачи на траки и на шедул
			if (
				(this.record.trucks && this.record.dispatch_trucks_count) ||
				(this.record.employees && this.record.dispatch_employees_count)
			) {
				App.Forms.showAlert(
					'error',
					'Error',
					'Service has tasks in dispatch'
				);
			} else {
				this.$emit('removeRecord', id);
			}
		},
	},
};
</script>

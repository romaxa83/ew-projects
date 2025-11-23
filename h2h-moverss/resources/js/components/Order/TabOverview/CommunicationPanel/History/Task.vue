<template>
	<li v-clickoutside="disableEditMode" v-show="!displayOff">
		<div
			class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
			:class="{ 'd-none': !loading }"
		>
			<div class="d-flex justify-content-center">
				<div
					class="spinner-border text-info position-absolute"
					style="top: 30%"
					role="status"
				>
					<span class="sr-only">Loading...</span>
				</div>
			</div>
		</div>
		<!--        <button v-if="interface != 'calendar'" class="btn btn-lg btn-white btn-icon activity-timeline-icon rounded-circle js-waves-off">-->
		<!--            <i title="messages"-->
		<!--               class="fal fa-2x" :class="[hasResult ? 'fa-check': 'fa-alarm-clock']"></i>-->
		<!--        </button>-->
		<div
			class="card card-zoom-hover position-relative overflow-hidden"
			@mouseenter="mouseEnter()"
			@mouseleave="mouseLeave()"
			:class="{
				'border border-danger': overdued,
				'border border-success text-muted bg-gray-200': hasResult,
			}"
		>
			<div
				v-if="!hasResult && isHovered && isAllowedControl"
				class="bg-danger-50 position-absolute h-100 pos-right d-flex align-items-center control-block"
			>
				<div class="ml-auto mr-auto">
					<button
						@click="removeTask()"
						class="btn btn-danger waves-effect waves-themed"
					>
						Delete
					</button>
				</div>
			</div>

			<!--            <div v-if="!hasResult && isHovered" class="bg-danger-50 position-absolute h-100 pos-right d-flex align-items-center "-->
			<!--                 :class="{'expanded': deleteControl=='expanded', 'collapsed': deleteControl == 'collapsed'}"-->
			<!--                 @mouseover="deleteControl = 'expanded'" @mouseleave="deleteControl = 'collapsed'">-->
			<!--                <div class="ml-auto mr-auto" v-if="deleteControl == 'expanded'">-->
			<!--                    <button @click="removeTask()" class="btn btn-danger waves-effect waves-themed">Delete</button>-->
			<!--                </div>-->
			<!--            </div>-->
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap cursor-pointer"
				:class="[hasResult ? 'bg-gray-200 text-muted' : 'bg-white']"
				@click.self="enableEditMode"
			>
				<div class="mr-1">
					<i
						class="fal"
						:class="[hasResult ? 'fa-check' : 'fa-alarm-clock']"
					></i>
				</div>
				<div
					v-if="orderID && interface == 'calendar'"
					class="mr-1 fs-xs"
				>
					<a target="_blank" :href="'/orders/' + orderID"
						>#{{ orderID }}</a
					>
				</div>
				<div
					class="fs-xs mr-auto pr-3 text-muted"
					@click="enableEditMode"
				>
					<b>Task</b> {{ taskFromFor }}
				</div>
				<div
					class="d-flex position-relative pr-2 fs-xs text-muted"
					@click="enableEditMode"
				>
					{{ datetime }}
				</div>
			</div>
			<div
				class="card-body fs-sm py-2 cursor-pointer"
				@click="enableEditMode"
			>
				<div
					v-if="interface == 'calendar' && clientName"
					class="fw-300 mb-1"
				>
					{{ clientName }}
				</div>
				<div class="d-flex" :class="{ completed: hasResult }">
					<div v-if="item.type" class="fw-500 mr-2 text-nowrap">
						<i
							:class="item.type.icon"
							:style="{ color: item.type.color }"
						></i>
						{{ item.type.title }}
					</div>
					<div>{{ item.description }}</div>
				</div>
				<div v-if="hasResult" class="mt-2">{{ item.result }}</div>
				<div v-if="editMode" class="mt-2">
					<div class="input-group">
						<input
							v-model="taskResult"
							@keyup.enter="completeTask"
							type="text"
							class="form-control"
							:placeholder="resultPlaceholder"
						/>
						<div class="input-group-append">
							<button
								class="btn btn-primary waves-effect waves-themed"
								type="button"
								@click="completeTask"
								:disabled="!taskResult"
							>
								{{ completeBtnText }}
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="card-footer text-muted py-2" v-show="editMode">
				<div class="d-flex flex-wrap">
					<div class="custom-control custom-checkbox mr-2 mt-1">
						<input
							type="checkbox"
							name="test"
							v-model="makeTaskCopy"
							class="custom-control-input cursor-pointer"
                            :id="`task-${item.id}${v2 ? '-v2' : ''}`"
						/>
						<label
							class="custom-control-label"
                            :for="`task-${item.id}${v2 ? '-v2' : ''}`"
						></label>
					</div>
					<div>
						<input
							type="text"
							v-model="taskCopy.due_date"
							class="form-control-sm"
							ref="copyTaskDt"
						/>
					</div>
					<div class="ml-2 flex-fill" v-if="activeTypes.length">
						<vue-select2
							v-model="taskCopy.type_id"
							:config="configSelect2ForTypes()"
						>
							<option
								v-for="v in activeTypes"
								:key="v.id"
								v-bind:value="v.id"
								:data-icon="'fal fa-' + v.icon"
								:data-color="v.color"
							>
								{{ v.title }}
							</option>
						</vue-select2>
					</div>
					<div class="flex-fill" v-if="interface == 'order'">
						<vue-select2
							v-model="taskCopy.executor_id"
							:config="configSelect2ForUsers()"
						>
							<option
								v-for="v in activeUsers"
								:key="v.id"
								:value="v.id"
							>
								{{ v.name }}
							</option>
						</vue-select2>
					</div>
				</div>
				<div v-if="interface == 'calendar'" class="d-flex">
					<div class="align-items-center mr-2 py-2 fw-700">for</div>
					<div class="flex-fill">
						<vue-select2
							v-model="taskCopy.executor_id"
							:config="configSelect2ForUsers()"
						>
							<option
								v-for="v in activeUsers"
								:key="v.id"
								:value="v.id"
							>
								{{ v.name }}
							</option>
						</vue-select2>
					</div>
				</div>
			</div>
		</div>
	</li>
</template>

<script>
import VueSelect2 from '@components/VueSelect2';
import { mapGetters } from 'vuex';

export default {
	name: 'Task',
	props: ['record', 'datetime', 'interface', 'filter', 'v2'],
	data: () => ({
		isHovered: false,
		deleteControl: 'collapsed',
		editMode: false,
		makeTaskCopy: false,
		fpInstance: null,
		taskResult: null,
		updatedItem: null,
		loading: false,
		taskCopy: {
			type_id: null,
			due_date: null,
			executor_id: null,
		},
		configSelect2: {
			width: '100%',
			containerCssClass: 'border-0',
			dropdownCssClass: 'border-0 task-select2',
			selectionCssClass: 'border-0',
			// minimumResultsForSearch: Infinity
		},
	}),
	mounted() {
		this.taskCopy.type_id = this.record.item.type_id;
		this.taskCopy.executor_id = +this.record.item.executor_id;
		// this.$nextTick(() => {
		this.fpInstance = window.flatpickr(this.$refs.copyTaskDt, {
			//static: true,
			// appendTo: this.$refs.dueDtContainer,
			enableTime: true,
			position: 'auto',
			dateFormat: 'Y-m-d H:i:S',
			altInput: true,
			altFormat: 'M j, Y h:i K',
			minDate: 'today',
			minuteIncrement: 5,
			plugins: myFlatpickrPlugins,
			ranges: {
				'In 15 minutes': moment().add(15, 'minutes').toDate(),
				'In 30 minutes': moment().add(30, 'minutes').toDate(),
				'In a hour': moment().add(60, 'minutes').toDate(),
				Today: moment().endOf('day').toDate(),
				Tomorrow: moment().add(1, 'day').toDate(),
				'This week': moment().endOf('week').toDate(),
				'In 7 days': moment().add(7, 'day').toDate(),
				'In 30 days': moment().add(30, 'day').toDate(),
				// 'Last 30 Days': [moment().subtract(29, 'days').toDate(), new Date()],
				// 'This Month': [moment().startOf('month').toDate(), moment().endOf('month').toDate()],
				// 'Last Month': [
				//     moment().subtract(1, 'month').startOf('month').toDate(),
				//     moment().subtract(1, 'month').endOf('month').toDate()
				// ]
			},
			rangesOnly: false, // only show the ranges menu unless the custom range button is selected
			rangesAllowCustom: true, // adds a Custom Range button to show the calendar
			rangesCustomLabel: 'Custom Range', // customize the label for the custom range button
		});
		// });
	},
	methods: {
		mouseEnter() {
			if (!this.editMode) this.isHovered = true;
		},
		mouseLeave() {
			if (!this.editMode) this.isHovered = false;
		},
		removeTask() {
			this.loading = true;
			this.$store
				.dispatch('tasksCalendar/removeTask', { id: this.item.id })
				.then(() => {
					if (this.interface == 'order') {
						this.$store.commit('order/removeCommunicationRecord', {
							type: 'Task',
							id: this.item.id,
						});
					}
					if (this.interface == 'calendar') {
						this.$store.commit(
							'tasksCalendar/removeTask',
							this.item.id
						);
					}
					this.loading = false;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});
		},
		configSelect2ForUsers() {
			return this.configSelect2;
		},
		configSelect2ForTypes() {
			const config = {
				minimumResultsForSearch: Infinity,
				templateResult: this.icon,
				templateSelection: this.icon,
				// dropdownParent: $(this.$refs.card),
				escapeMarkup: function (elm) {
					return elm;
				},
			};
			return { ...config, ...this.configSelect2 };
		},
		icon(elm) {
			let _elm = $(elm.element);
			return (
				'<span style="color: ' +
				_elm.data('color') +
				'">' +
				(elm.id
					? "<i class='" + _elm.data('icon') + " mr-2'></i>"
					: '') +
				'</span>' +
				elm.text
			);
		},
		enableEditMode(e) {
			if (!this.updatedItem && !this.hasResult && !this.editMode) {
				this.editMode = true;
				this.isHovered = false;
			}
		},
		disableEditMode() {
			if (this.editMode) this.editMode = false;
		},
		completeTask() {
			this.loading = true;
			const requestData = {
				id: this.item.id,
				mode: 'status',
				val: 3, // completed status
				result: this.taskResult,
				returnFormat: 'communicationPanel',
				orderID: this.orderID,
			};
			if (this.makeTaskCopy) requestData.reCreate = this.taskCopy;

			this.$store
				.dispatch('tasksCalendar/completeTask', requestData)
				.then((data) => {
					if (this.interface == 'order') {
						if (data.record)
							this.$store.commit(
								this.v2
									? 'order/updateCommunicationRecordV2'
									: 'order/updateCommunicationRecord',
								data.record
							);
						if (data.new_record)
							this.$store.commit(
								this.v2
									? 'order/pushCommunicationRecordV2'
									: 'order/pushCommunicationRecord',
								data.new_record
							);
					}
					if (this.interface == 'calendar') {
						if (data.record)
							this.$store.commit(
								'tasksCalendar/updateTask',
								data.record
							);
						if (data.new_record)
							this.$store.commit(
								'tasksCalendar/pushTaskRecord',
								data.new_record
							);
					}
					this.loading = false;
					this.editMode = false;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});

			// this.$store.dispatch('order/completeTask', requestData)
			//     .then(() => {
			//         this.editMode = false;
			//         this.loading = false;
			//     }).catch((response) => {
			//     App.Forms.simpleErrors(response)
			// });

			// axiosPromise(axios.post('/tasks/modifyTask', requestData)).then((data) => {
			//     this.$store.commit('order/updateCommunicationTask', data.record)
			//     if (data.new_record)
			//         this.$store.commit('order/pushCommunicationRecord', data.new_record)
			//     // console.log(record)
			//     // this.updatedItem = record;
			//     this.editMode = false;
			//     this.loading = false;
			// })
		},
	},
	computed: {
		resultPlaceholder() {
			return 'Add result';
		},
		completeBtnText() {
			if (this.makeTaskCopy) return 'Complete & Add new';
			return 'Complete to-do';
		},
		displayOff() {
			if (
				this.hasResult &&
				this.filter &&
				this.filter.status == 'inwork'
			) {
				return true;
			}
			if (this.filter && this.filter.manager.length) {
				return !(
					this.filter.manager.includes(+this.executorID) ||
					this.filter.manager.includes(this.executorID.toString())
				);
			}
			return null;
		},
		...mapGetters({
			activeTypes: 'tasksCalendar/activeTypes',
			activeUsers: 'tasksCalendar/activeUsers',
		}),
		clientName() {
			if (this.item.order) {
				if (this.item.order.client) {
					return (
						this.item.order.client.name +
						' ' +
						this.item.order.client.lname
					);
				}
				return 'Noname';
			}
			return null;
		},
		isAllowedControl() {
			if (this.editMode) return false;
			if (
				this.whoami &&
				(this.whoami.is_admin || this.whoami.uid == this.item.user_id)
			)
				return true;
			return false;
		},
		whoami() {
			return this.$store.state.tasksCalendar.whoami;
		},
		hasResult() {
			return this.item.status_id == 3 || this.updatedItem?.status_id == 3;
		},
		overdued() {
			if (!this.hasResult) {
				if (
					moment().isAfter(moment.unix(this.record.timestamp).local())
				)
					return true;
			}
			return false;
		},
		orderID() {
			return this.item.order_id;
			// if (this.item.miscs?.relation?.type == 'order') {
			//     return this.item.miscs?.relation?.id;
			// }
			// return null;
			//return this.$store.state.session?.order?.id;
		},
		item() {
			// if (this.updatedItem)
			//     return this.updatedItem;
			return this.record.item;
		},
		author() {
			if (this.item.author.employee)
				return (
					this.item.author.employee.name +
					' ' +
					this.item.author.employee.l_name
				);
			return this.item.author.name;
		},
		executorID() {
			return this.item.executor_id;
		},
		executor() {
			if (this.item.executor.employee)
				return (
					this.item.executor.employee.name +
					' ' +
					this.item.executor.employee.l_name
				);
			return this.item.executor.name;
		},
		taskFromFor() {
			if (this.author == this.executor) return ' for ' + this.executor;
			return 'from ' + this.author + ' for ' + this.executor;
		},
	},
	watch: {
		makeTaskCopy(newVal, oldVal) {
			if (newVal && !this.taskCopy.due_date) {
				this.fpInstance.open();
				this.fpInstance.setDate(moment().add(1, 'day').toDate());
			}
		},
	},
	directives: {
		clickoutside: {
			inserted: (el, binding, vnode) => {
				// assign event to the element
				el.clickOutsideEvent = function (event) {
					// here we check if the click event is outside the element and it's children
					if (
						!(
							el == event.target ||
							$(event.target).closest('.task-select2').length ||
							$(event.target).closest('.flatpickr-calendar')
								.length ||
							el.contains(event.target)
						)
					) {
						// if clicked outside, call the provided method
						// console.log('outside click', el)
						vnode.context[binding.expression](event);
					} else {
						// console.log('inside click', el)
					}
				};
				// register click and touch events
				document.body.addEventListener('click', el.clickOutsideEvent);
				document.body.addEventListener(
					'touchstart',
					el.clickOutsideEvent
				);
			},
			unbind: function (el) {
				// unregister click and touch events before the element is unmounted
				document.body.removeEventListener(
					'click',
					el.clickOutsideEvent
				);
				document.body.removeEventListener(
					'touchstart',
					el.clickOutsideEvent
				);
			},
			stopProp(event) {
				event.stopPropagation();
			},
		},
	},
	components: {
		VueSelect2,
	},
};
</script>

<style scoped>
.control-block {
	z-index: 3;
	width: 150px;
	right: -135px;
	cursor: pointer;
	transition: 0.5s;
}

.control-block:hover {
	right: 0px;
	cursor: default;
}

.expanded {
	transition: all 0.2s linear;
	width: 150px;
	z-index: 3;
}

.collapsed {
	/*animation: 0.5s linear;*/
	cursor: pointer;
	width: 15px;
	z-index: 3;
	opacity: 0.5;
}

.completed {
	text-decoration: line-through;
}
</style>

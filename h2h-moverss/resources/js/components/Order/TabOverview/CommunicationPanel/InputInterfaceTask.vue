<template>
	<div
		ref="inputInterfaceTask"
		class="panel-content position-relative border-faded border-0 mt-0 pt-0 bg-faded"
	>
		<div
			class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
			:class="{ 'd-none': !loading }"
		>
			<div class="d-flex justify-content-center">
				<div
					class="spinner-border text-info position-absolute"
					style="top: 40%"
					role="status"
				>
					<span class="sr-only">Loading...</span>
				</div>
			</div>
		</div>
		<pinned-notes-container
			v-if="
				interface == 'order' &&
				$store.getters[
					// TODO: Remove prev version after migration
					this.v2
						? 'order/isShowPinnedNotesV2'
						: 'order/isShowPinnedNotes'
				]
			"
		/>
		<vue-select2
			v-if="interface == 'calendar'"
			v-model="customOrderID"
			:config="configSelect2ForOrder()"
		></vue-select2>
		<div
			class="d-flex align-items-center py-2 px-2 bg-white border border-bottom-0"
			:class="[interface == 'calendar' ? 'border-top-0' : 'rounded-top']"
		>
			<div>
				<div
					v-if="
						$store.getters[
							// TODO: Remove prev version after migration
							this.v2
								? 'order/pinnedNotesTextV2'
								: 'order/pinnedNotesText'
						]
					"
					class="fa-sm ml-2 pinned-notes-link"
				>
					<a
						href="pinnedNotes"
						@click.prevent="
							$store.commit('order/toggleShowPinnedNotes')
						"
						>{{
							$store.getters[
								// TODO: Remove prev version after migration
								this.v2
									? 'order/pinnedNotesTextV2'
									: 'order/pinnedNotesText'
							]
						}}</a
					>
				</div>

				<input-interface-dropdown
					v-if="interface == 'order'"
					:mode="mode"
				/>
			</div>
			<div class="ml-2 d-flex flex-fill flex-wrap">
				<div class="align-items-center mr-2 py-2 fw-700">due</div>
				<div class="mr-2" ref="dueDtContainer">
					<input
						type="text"
						v-model="record.due_date"
						ref="dueDt"
						class="form-control border-0"
						value=""
					/>
				</div>
				<div class="align-items-center mr-2 py-2 fw-700">for</div>
				<div class="d-flex flex-fill">
					<div class="mr-2 flex-fill">
						<vue-select2
							v-model="record.executor_id"
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
						<!--                    <select class="form-control">-->
						<!--                        <option>User1</option>-->
						<!--                        <option>User2</option>-->
						<!--                    </select>-->
					</div>
					<div class="mr-2 flex-fill">
						<vue-select2
							v-model="record.type_id"
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
						<!--                    <select class="form-control">-->
						<!--                        <option>type1</option>-->
						<!--                        <option>type1</option>-->
						<!--                    </select>-->
					</div>
				</div>
			</div>
		</div>
		<textarea
			v-model="record.description"
			rows="3"
			class="form-control border border-bottom-left-radius-0 border-bottom-right-radius-0 border-top-left-radius-0 border-top-right-radius-0"
			placeholder="Type here..."
		></textarea>
		<div
			class="d-flex align-items-center py-2 px-2 bg-white border border-top-0 rounded-bottom"
		>
			<!--            <button type="button" class="btn btn-icon fs-lg waves-effect waves-themed">-->
			<!--                <i class="fal fa-paperclip"></i>-->
			<!--            </button>-->
			<button
				@click="submit"
				class="btn btn-primary btn-sm ml-auto waves-effect waves-themed"
				:disabled="submitDisabled"
			>
				Create Task
			</button>
		</div>
	</div>
</template>

<script>
import { axiosPromise } from '@/helpers/axiosPromise';
import pinnedNotesContainer from '@components/Order/TabOverview/CommunicationPanel/History/PinnedNotesContainer';
import inputInterfaceDropdown from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceDropdown';
import VueSelect2 from '@components/VueSelect2';
import { mapGetters } from 'vuex';

export default {
	name: 'InputInterfaceTask',
	props: {
		mode: {
			type: Object,
			default: () => {
				return {};
			},
		},
		interface: {
			default: 'order',
		},
		v2: {
			type: Boolean,
			default: false,
		},
	},
	data: () => ({
		loading: true,
		configSelect2: {
			containerCssClass: 'border-0',
			dropdownCssClass: 'border-0',
			selectionCssClass: 'border-0',
			//dropdownParent: $(document.body)
			// minimumResultsForSearch: Infinity
		},
		customOrderID: null,
		record: {
			type_id: null,
			executor_id: null,
			// title: null,
			description: '',
			// priority: 1,
			// due_date: moment().format('YYYY-MM-DD'),
			// due_type: null,
			// due_time_from: null,
			// due_time_to: null,
			// subscribers: [],
			// notify_holder: null,
			// notify_subscribers: null,
			// miscs: {
			//     relation: null
			// },
		},
		form: {
			text: '',
		},
	}),
	computed: {
		orderID() {
			if (this.interface == 'calendar') return this.customOrderID;
			if (this.interface == 'order')
				return this.$store.state.session?.order?.id;
			return null;
		},
		divisionID() {
			return this.$store.state.session?.order?.division_id;
		},
		submitDisabled() {
			// if (this.record.description.trim().length > 0)
			//     return false;
			// return true;
			return false;
		},
		whoami() {
			return this.$store.state.tasksCalendar.whoami;
		},
		// types() {
		//     return this.$store.state.tasksCalendar.additional.types;
		// },
		statuses() {
			return this.$store.state.tasksCalendar.additional.statuses;
		},
		users() {
			return this.$store.state.tasksCalendar.additional.users;
		},
		...mapGetters({
			clientID: 'clients/clientId',
			// users: 'appTasks/users',
			// statuses: 'appTasks/statuses',
			// types: 'appTasks/types',
			// whoami: 'appTasks/whoami',
			activeTypes: 'tasksCalendar/activeTypes',
			activeUsers: 'tasksCalendar/activeUsers',
		}),
	},
	mounted() {
		this.record = this.emptyRecord();
		this.$nextTick(() => {
			window.fp1 = window.flatpickr(this.$refs.dueDt, {
				static: true,
				// appendTo: this.$refs.dueDtContainer,
				enableTime: true,
				position: 'above',
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

			this.loading = false;
		});
	},
	methods: {
		configSelect2ForOrder() {
			const config = {
				dropdownParent: $(this.$refs.inputInterfaceTask).parents(
					'.modal-body:first'
				),
				containerCssClass:
					'border-bottom-left-radius-0 border-bottom-right-radius-0',
				selectionCssClass:
					'border-bottom-left-radius-0 border-bottom-right-radius-0',
				placeholder: 'Search Order (by id, client name, phones, email)',
				allowClear: true,
				ajax: {
					url: '/orders/autocomplete',
					method: 'POST',
					delay: 400,
					dataType: 'json',
					data(params) {
						return {
							q: params.term, // search term
							page: params.page || 1,
						};
					},
					// processResults(response, params) {
					//     return {
					//         results: response.data.results,
					//         pagination: response.data.pagination
					//     };
					// },
					cache: true,
				},
				escapeMarkup(markup) {
					return markup;
				},
				minimumInputLength: 0,
				templateResult: App.Miscs.templateSelect2Order,
				templateSelection: function (v) {
					if (v.id) return 'Order #' + v.id;
					// else if (el.data('route').includes('client') && v.name) {
					//     return v.name + ' ' + v.lname;
					// }
					return v.text;
				},
			};
			return config;
		},
		configSelect2ForUsers() {
			if (this.interface == 'calendar') {
				const config = {
					dropdownParent: $(this.$refs.inputInterfaceTask).parents(
						'.modal-body:first'
					),
				};
				return { ...config, ...this.configSelect2 };
			}

			//dropdownParent: $(document.body)
			return this.configSelect2;
		},
		configSelect2ForTypes() {
			const config = {
				minimumResultsForSearch: Infinity,
				templateResult: this.icon,
				templateSelection: this.icon,
				escapeMarkup: function (elm) {
					return elm;
				},
			};
			if (this.interface == 'calendar') {
				config['dropdownParent'] = $(
					this.$refs.inputInterfaceTask
				).parents('.modal-body:first');
			}

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
		submit() {
			this.loading = true;
			// let due_date = moment.utc(moment(this.record.due_date)).format('YYYY-MM-DD HH:mm:ss');
			let due_date = moment(this.record.due_date).format(
				'YYYY-MM-DD HH:mm:ss'
			);
			if (this.orderID) this.record.order_id = this.orderID;
			// if (!this.orderID)
			//     this.record.miscs = {};
			// else
			//     this.record.miscs = {
			//         href: '/orders/' + +this.orderID,
			//         relation: {
			//             title: 'Order #' + +this.orderID,
			//             type: 'order',
			//             id: +this.orderID,
			//             branch_id: this.divisionID,
			//             client_id: this.client?.id,
			//         }
			//     }
			axiosPromise(
				axios.post('/tasks/create', {
					record: {
						...this.record,
						due_date,
					},
					returnFormat: 'communicationPanel',
					orderID: this.orderID,
				})
			)
				.then((data) => {
					if (this.interface == 'order')
						this.$store.commit(
							this.v2
								? 'order/pushCommunicationRecordV2'
								: 'order/pushCommunicationRecord',
							data.record
						);
					if (this.interface == 'calendar') {
						this.$store.commit(
							'tasksCalendar/pushTaskRecord',
							data.record
						);
						this.$emit('close-modal');
					}
					App.Forms.showAlert('success', 'Task created');
					this.record = this.emptyRecord();

					this.loading = false;
					// this.$emit('refresh-history');
					// this.form.pinned = false;
					// this.form.text = '';
					// reload history
				})
				.catch((error) => {
					console.log(error);
					App.Forms.simpleErrors(error);
				});
		},
		emptyRecord() {
			return {
				type_id: 1,
				executor_id: this.whoami?.uid ?? null,
				title: null,
				description: '',
				priority: 1,
				due_date: moment().format('YYYY-MM-DD HH:mm:ss'),
				due_type: null,
				due_time_from: null,
				due_time_to: null,
				subscribers: [],
				notify_holder: null,
				notify_subscribers: null,
				miscs: {
					href: '/orders/' + this.orderID,
					relation: {
						title: 'Order #' + this.orderID,
						type: 'order',
						id: this.orderID,
						branch_id: this.divisionID,
						client_id: this.clientID,
					},
				},
			};
		},
	},
	components: {
		VueSelect2,
		inputInterfaceDropdown,
		pinnedNotesContainer,
	},
};
</script>

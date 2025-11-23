<template>
	<div
		class="modal fade"
		id="modal-create-task"
		role="dialog"
		aria-hidden="true"
	>
		<div
			class="modal-dialog modal-lg modal-dialog-centered"
			role="document"
		>
			<div class="modal-content">
				<div class="modal-header bg-fusion-100">
					<h5 class="modal-title">
						Create Task
						<i
							v-if="
								record.miscs.relation &&
								record.miscs.relation.title
							"
							class="ml-4 fs-sm"
							v-text="record.miscs.relation.title"
						></i>
					</h5>
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
				<div class="modal-body">
					<div class="form-group">
						<vue-typeahead-bootstrap
							id="modal_title"
							v-model="record.title"
							placeholder="You can find by Order Number or Client Phone..."
							:data="autocomplete.data"
							:serializer="(item) => item.text"
							:disabled="record.miscs.relation ? true : false"
							@hit="autocompleteOnSelect($event)"
							@input="autocompleteQuery()"
						>
							<template
								slot="append"
								v-if="record.miscs.relation"
							>
								<button
									class="btn btn-outline-danger"
									type="button"
									@click="removeLinking"
								>
									<i class="fal fa-times"></i>
								</button>
							</template>
						</vue-typeahead-bootstrap>
					</div>

					<div class="row mb-3">
						<div class="col-sm" title="Executor">
							<select
								v-model="record.executor_id"
								class="form-control select2"
								id="modal_executor_id"
							>
								<option
									v-for="v in activeUsers"
									:key="v.id"
									v-bind:value="v.id"
								>
									{{ v.name }}
								</option>
							</select>
						</div>
						<div class="col-sm">
							<input
								class="form-control flatpickr"
								id="due_date"
								v-model="record.due_date"
							/>
						</div>
						<div class="col-sm">
							<select
								v-model="record.type_id"
								class="form-control"
								id="type_id"
							>
								<option :value="null">
									-- select a type --
								</option>
								<option
									v-for="v in activeTypes"
									:key="v.id"
									v-bind:value="v.id"
									:data-icon="'fal fa-' + v.icon"
									:data-color="v.color"
								>
									{{ v.title }}
								</option>
							</select>
						</div>
					</div>

					<div class="form-group mt-4">
						<label class="form-label" for="modal_description"
							>Description:</label
						>
						<textarea
							class="form-control"
							rows="5"
							id="modal_description"
							v-model="record.description"
						></textarea>
					</div>

					<!--                            <div class="btn-group mt-1 mb-2">-->
					<!--                                <button class="btn btn-xs btn-outline-secondary dropdown-toggle" type="button"-->
					<!--                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">-->
					<!--                                    Due time: {{ dueTimeType }}-->
					<!--                                </button>-->
					<!--                                <div class="dropdown-menu">-->
					<!--                                    <a v-show="record.due_time_from" class="dropdown-item" href="#"-->
					<!--                                       @click.prevent="resetTime">Reset</a>-->
					<!--                                    <a class="dropdown-item" :class="{ active: record.due_type === 'normal' }"-->
					<!--                                       href="#" @click.prevent="setTimeType('normal')">Normal</a>-->
					<!--                                    <a class="dropdown-item" :class="{ active: record.due_type === 'range' }"-->
					<!--                                       href="#" @click.prevent="setTimeType('range')">Range</a>-->
					<!--                                </div>-->
					<!--                            </div>-->

					<!--                            <div class="form-group mb-2">-->
					<!--                                <div class="input-group">-->
					<!--                                    <input v-model="record.due_time_from" id="due_time_from"-->
					<!--                                           class="form-control flatpickr"-->
					<!--                                           :class="{'content-hidden': !record.due_time_from}"-->
					<!--                                           :placeholder="record.due_time_to ? 'Start time from' : 'Start time'"-->
					<!--                                           type="text"/>-->
					<!--                                    <input v-model="record.due_time_to" id="due_time_to" class="form-control flatpickr"-->
					<!--                                           :class="{'content-hidden': !record.due_time_to}"-->
					<!--                                           placeholder="Start time to" type="text"/>-->
					<!--                                </div>-->
					<!--                            </div>-->

					<!--                            <div class="form-group mb-2">-->
					<!--                                <label for="status_id" class="form-label">Status:</label>-->
					<!--                                <div class="input-group">-->
					<!--                                    <div class="input-group-prepend">-->
					<!--                                        <div class="input-group-text">-->
					<!--                                            <i class="fas fa-check"></i>-->
					<!--                                        </div>-->
					<!--                                    </div>-->
					<!--                                    <select v-model="record.status_id" class="form-control" id="status_id">-->
					<!--                                        <option v-for="v in statuses" :key="v.id" v-bind:value="v.id">-->
					<!--                                            {{ v.title }}-->
					<!--                                        </option>-->
					<!--                                    </select>-->
					<!--                                </div>-->
					<!--                            </div>-->
					<!--                            <div class="form-group mb-2">-->
					<!--                                <label for="modal_priority" class="form-label">Priority:</label>-->
					<!--                                <div class="input-group">-->
					<!--                                    <div class="input-group-prepend">-->
					<!--                                        <div class="input-group-text">-->
					<!--                                            <i class="fas fa-exclamation-circle"></i>-->
					<!--                                        </div>-->
					<!--                                    </div>-->
					<!--                                    <select v-model="record.priority" class="form-control" id="modal_priority">-->
					<!--                                        <option v-for="index in 5" :key="index" v-bind:value="index">-->
					<!--                                            {{ index }}-->
					<!--                                        </option>-->
					<!--                                    </select>-->
					<!--                                </div>-->
					<!--                            </div>-->

					<!--                            <div class="form-group mt-4">-->
					<!--                                <label class="form-label" for="notify_holder">Notify</label>-->
					<!--                                <input class="form-control" id="notify_holder" v-model="record.notify_holder">-->
					<!--                            </div>-->

					<!--                            <div class="form-group mb-2">-->
					<!--                                <label for="modal_subscribers" class="form-label">Subscribers:</label>-->
					<!--                                <div class="input-group">-->
					<!--                                    <select v-model="record.subscribers" class="form-control select2" multiple-->
					<!--                                            id="modal_subscribers">-->
					<!--                                        <option v-for="v in activeUsers" :key="v.id" v-bind:value="v.id">-->
					<!--                                            {{ v.name }}-->
					<!--                                        </option>-->
					<!--                                    </select>-->
					<!--                                </div>-->
					<!--                            </div>-->

					<!--                            <div v-show="record.subscribers.length" class="form-group mt-4">-->
					<!--                                <label class="form-label" for="modal_notify_subscribers">Notify subscribers:</label>-->
					<!--                                <input class="form-control" id="modal_notify_subscribers"-->
					<!--                                       v-model="record.notify_subscribers">-->
					<!--                            </div>-->
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
						<button
							@click="submit()"
							type="button"
							class="btn btn-primary"
						>
							<span
								v-show="loading"
								class="spinner-border spinner-border-sm"
								role="status"
								aria-hidden="true"
							></span>
							Creat{{ loading ? 'ing...' : 'e' }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import { AxiosHelper } from '@/helpers/axiosHelper';
import Debounce from 'lodash.debounce';
import { mapGetters } from 'vuex';

let fp, fp_time, fp_time_to;

export default {
	name: 'AppTasksCreateModal',
	props: {
		params: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			loading: false,
			record: this.emptyRecord(),
			autocomplete: {
				data: [],
			},
		};
	},
	computed: {
		dueTimeType() {
			if (this.record.due_type) return this.record.due_type;
			return 'not set';
		},
		...mapGetters({
			users: 'appTasks/users',
			statuses: 'appTasks/statuses',
			types: 'appTasks/types',
			whoami: 'appTasks/whoami',
			activeTypes: 'appTasks/activeTypes',
			activeUsers: 'appTasks/activeUsers',
		}),
	},
	mounted() {
		this.init();
	},
	methods: {
		autocompleteOnSelect(e) {
			this.record.miscs.relation = {
				title: e.title,
				type: e.type,
				id: e.id,
				branch_id: e.branch_id ?? null,
			};

			if (e.type === 'order') {
				this.record.miscs.href = '/orders/' + e.id;
				this.record.miscs.relation.client_id = e.client_id
					? e.client_id
					: null;
			}

			this.autocomplete.data = [];
			this.record.title = e.title;
		},
		autocompleteQuery: Debounce(function () {
			if (!this.record.miscs.relation)
				Promise.all([
					this.autocompleteQueryClients(),
					this.autocompleteQueryOrders(),
				]).then((res) => (this.autocomplete.data = res.flat()));
		}, 500),
		autocompleteQueryClients() {
			return AxiosHelper({
				url: '/client/profile/autocomplete',
				data: {
					q: this.record.title,
				},
			}).then((res) => {
				return res.data.results.map((item) => {
					item.type = 'client';
					item.title =
						'Client #' + item.id + ` ${item.name} ${item.lname}`;
					item.text =
						'Client #' +
						item.id +
						` ${item.name} ${item.lname}` +
						(item.phones
							? ' phones: ' +
							  item.phones.map((phone) => phone.value).join(',')
							: null);

					return item;
				});
			});
		},
		autocompleteQueryOrders() {
			return AxiosHelper({
				url: '/reports/report-authorize/order-autocomplete',
				data: {
					q: this.record.title,
				},
			}).then(({ items }) => {
				return items.map((item) => {
					item.type = 'order';
					item.text = item.title = 'Order ' + item.text;

					return item;
				});
			});
		},
		emptyRecord() {
			return {
				type_id: null,
				executor_id: this.whoami?.uid ?? null,
				title: null,
				description: null,
				priority: 1,
				due_date: moment().format('YYYY-MM-DD'),
				due_type: null,
				due_time_from: null,
				due_time_to: null,
				subscribers: [],
				notify_holder: null,
				notify_subscribers: null,
				miscs: {
					relation: null,
				},
			};
		},
		init() {
			let select2 = $('#modal-create-task .select2');
			$('#modal-create-task').modal('show');

			$('#modal-create-task')
				.on('shown.bs.modal', () => {
					this.record = this.emptyRecord();

					if (this.params && this.params.miscs) {
						this.record.miscs.href = this.params.miscs.href;
						this.record.miscs.relation = {
							...this.params.miscs.relation,
						};
						this.$set(
							this.record.miscs,
							'relation',
							this.record.miscs.relation
						);
					} else this.record.miscs.href = window.location.pathname;

					this.$nextTick(() => {
						fp = flatpickr('#due_date', {
							enableTime: true,
							dateFormat: 'Y-m-d H:i:ss',
							altInput: true,
							altFormat: 'm/d/Y h:i K',
							minDate: 'today',
							minuteIncrement: 15,
						});

						// this.initTimeFrom();
						// this.initTimeTo();
						select2.trigger('change');
					});
				})
				.on('hide.bs.modal', () => {
					fp.destroy();
					// fp_time.destroy();
					// fp_time_to.destroy();
				});

			select2.select2({
				dropdownParent: $('#modal-create-task'),
			});
			select2.on('select2:close', function (e) {
				this.dispatchEvent(new Event('change', { target: e.target }));
			});

			this.select2Icon();
		},
		initTimeFrom(params = {}) {
			// fp_time = flatpickr('#due_time_from', {
			//     enableTime: true,
			//     noCalendar: true,
			//     altInput: true,
			//     altFormat: 'h:i K',
			//     dateFormat: 'H:i:ss',
			//     time_24hr: false,
			//     minuteIncrement: 15,
			//     ...params,
			//     onValueUpdate: (selectedDates, dateStr, instance) => {
			//         if (!this.record.due_time_from)
			//             this.record.due_time_from = dateStr;
			//     }
			// });
		},
		initTimeTo(params = {}) {
			// fp_time_to = flatpickr('#due_time_to', {
			//     enableTime: true,
			//     noCalendar: true,
			//     altInput: true,
			//     altFormat: 'h:i K',
			//     dateFormat: 'H:i:ss',
			//     time_24hr: false,
			//     minuteIncrement: 15,
			//     maxTime: '23:59',
			//     ...params,
			//     onValueUpdate: (selectedDates, dateStr, instance) => {
			//         if (!this.record.due_time_to)
			//             this.record.due_time_to = dateStr;
			//     }
			// });
		},
		removeLinking() {
			this.record.miscs.relation = null;
		},
		resetTime() {
			this.record.due_type = null;
			this.record.due_time_from = null;
			this.record.due_time_to = null;

			// setTimeout(() => this.initTimeFrom());
			// setTimeout(() => this.initTimeTo());

			$('#modal-create-task .modal-title').trigger('click');
		},
		resetTimeTo() {
			this.record.due_time_to = null;
			// setTimeout(() => this.initTimeTo());

			if (fp_time_to) fp_time_to.clear();
		},
		select2Icon() {
			$('#type_id')
				.select2({
					dropdownParent: $('#modal-create-task'),
					minimumResultsForSearch: 1 / 0,
					templateResult: icon,
					templateSelection: icon,
					escapeMarkup: function (elm) {
						return elm;
					},
				})
				.on('select2:close', function (e) {
					this.dispatchEvent(
						new Event('change', { target: e.target })
					);
				});

			function icon(elm) {
				let _elm = $(elm.element);
				return (
					'<span style="color: ' +
					_elm.data('color') +
					'">' +
					(elm.id
						? "<i class='" +
						  _elm.data('icon') +
						  " mr-2'></i>" +
						  elm.text
						: elm.text) +
					'</span>'
				);
			}
		},
		setTimeType(type) {
			this.record.due_type = type;

			if (type === 'normal') {
				this.record.due_time_to = null;
				fp_time_to.destroy();

				this.record.due_time_from = moment()
					.add(2, 'hours')
					.set({
						m: 0,
						s: 0,
					})
					.format('HH:mm:ss');
				let params = {};
				if (moment(this.record.due_date).isSame(moment(), 'day')) {
					params = {
						minTime: moment()
							.add(1, 'hours')
							.set({
								m: 0,
								s: 0,
							})
							.format('HH:mm:ss'),
					};
				}

				setTimeout(() => this.initTimeFrom(params));
			}

			if (type === 'range') {
				// Инициализация календаря + задаем второе время +2ч
				setTimeout(() =>
					this.initTimeTo({
						minTime: this.record.due_time_from,
					})
				);

				this.record.due_time_to = moment(
					this.record.due_time_from,
					'HH:mm:ss'
				)
					.add(2, 'hours')
					.format('HH:mm:ss');
			}
			$('#modal-create-task .modal-title').trigger('click');
		},
		submit() {
			this.loading = true;

			// Time to UTC
			let due_date = moment
				.utc(moment(this.record.due_date))
				.format('YYYY-MM-DD HH:mm:ss');

			AxiosHelper({
				url: '/tasks/create',
				data: {
					record: {
						...this.record,
						due_date,
					},
				},
			})
				.then(({ record }) => {
					$('#modal-create-task').modal('hide');
					App.Forms.showAlert('success', 'Task created');

					this.$store.dispatch('appTasks/updateRecords', [record]);
				})
				.finally(() => (this.loading = false));
		},
	},
};
</script>

<template>
	<div
		class="modal fade"
		id="modal-view-task"
		role="dialog"
		aria-hidden="true"
	>
		<div
			class="modal-dialog modal-lg modal-dialog-centered"
			role="document"
		>
			<div class="modal-content">
				<div class="modal-header bg-fusion-100">
					<a
						:href="record.miscs.href"
						:style="typeColorStyle"
						title="Task created from this object"
						class="btn fs-sm rounded-circle btn-icon waves-effect waves-themed position-absolute"
						style="top: 15px"
					>
						<i :class="linkClass"></i>
					</a>
					<h5
						class="ml-6 modal-title"
						:class="{ 'mb-2': record.type_id }"
					>
						{{ '#' + record.id + ' ' + record.title }}
						<div
							v-if="record.type_id"
							class="fs-nano position-absolute"
							title="Task Type"
						>
							{{ types[record.type_id].title }}
						</div>
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
				<div class="modal-body pb-0">
					<div
						v-if="loading"
						class="frame-wrap position-absolute w-75 h-100 opacity-50"
					>
						<div
							class="w-100 d-flex justify-content-center align-items-center"
						>
							<div
								class="spinner-border text-info position-absolute"
								style="top: 50%"
								role="status"
							>
								<span class="sr-only">Loading...</span>
							</div>
						</div>
					</div>

					<div
						class="d-flex mb-2"
						v-if="
							(record.miscs.relation &&
								record.miscs.relation.type) ||
							record.client
						"
					>
						<div
							v-if="
								record.miscs.relation &&
								record.miscs.relation.type
							"
						>
							<div
								v-if="record.miscs.relation.type === 'order'"
								class="text-capitalize mr-4"
							>
								{{ record.miscs.relation.type }}:
								<a
									:href="record.miscs.href"
									v-text="`#${record.miscs.relation.id}`"
								></a>
							</div>
						</div>
						<div
							v-if="record.client"
							class="client-name"
							title="Client Contacts"
						>
							{{ record.client.name + ' ' + record.client.lname }}
							<span v-if="record.client.value.type === 'email'">
								(${record.client.value.value})
							</span>
							<span
								v-else-if="record.client.value.type === 'phone'"
							>
								{{ record.client.value.value | formatPhone }}
							</span>
						</div>
					</div>

					<div class="d-flex">
						<div>
							<span
								:class="{ 'text-danger': isOverdue }"
								v-text="record.fullDueDate"
							></span>
							<div
								class="text-muted fs-sm"
								v-if="
									record.due_date !=
									record.miscs.original.due_date
								"
							>
								<span class="fs-md fw-700">Initial</span>
								{{ originalDueDate }}
							</div>
						</div>
						<div class="ml-4" title="Executor">
							for <b>{{ executorName }}</b>
						</div>
						<div>
							<div
								v-if="record.type_id"
								class="ml-4"
								:style="typeColorStyle"
							>
								<i :class="types[record.type_id].icon"></i>
								{{ types[record.type_id].title }}
							</div>
						</div>
						<div
							v-show="record.description"
							class="ml-4"
							title="Description"
						>
							<div
								class="text-muted"
								v-text="record.description"
							></div>
						</div>
					</div>

					<div class="row" v-show="record.result" title="Result">
						<div class="col">
							<hr class="mt-2 mb-2" />
							<div
								class="text-muted"
								v-text="record.result"
							></div>
						</div>
					</div>

					<div class="form-group" v-if="canManage">
						<hr class="mt-2 mb-2" />
						<label class="form-label" :for="'mod_res_' + record.id"
							>Add Result:</label
						>
						<div class="input-group">
							<input
								type="text"
								class="form-control"
								:id="'mod_res_' + record.id"
								v-model="todoResult"
							/>
							<div class="input-group-append">
								<button
									class="btn btn-outline-success"
									type="button"
									@click="modifyTask('status', 3)"
								>
									Complete
								</button>
							</div>
						</div>
					</div>
					<!--                    <div class="row" v-show="record.subscribers.length">-->
					<!--                        <div class="col">-->
					<!--                            <hr class="mt-2 mb-2">-->
					<!--                            <label class="form-label">Observed by</label>-->
					<!--                            <div class="text-muted" v-text="observers"></div>-->
					<!--                        </div>-->
					<!--                    </div>-->
				</div>

				<div
					class="modal-footer justify-content-between"
					v-if="canManage"
					:class="{
						'd-flex': !isFinalStatus,
						'd-none': isFinalStatus,
					}"
				>
					<div>
						<input type="checkbox" v-model="reCreate.enabled" />
					</div>

					<div class="flex-fill">
						<select
							v-model="reCreate.type_id"
							class="form-control select2"
							id="type_id"
						>
							<option :value="null">
								-- select an option --
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

					<div class="flex-fill">
						<div
							class="d-flex flex-row align-items-center justify-content-center"
						>
							<div v-show="reCreate.due_date">
								<input
									class="form-control flatpickr"
									id="due_date_view"
									v-model="reCreate.due_date"
								/>
							</div>

							<template v-if="!reCreate.due_date">
								<a href="#" @click.prevent="setDueDateDays()"
									>Tomorrow</a
								>
								<a
									class="ml-4"
									href="#"
									@click.prevent="setDueDateDays(7)"
									>In 7 days</a
								>
								<a
									class="ml-4"
									href="#"
									@click.prevent="setDueDateDays(30)"
									>In 30 days</a
								>
							</template>
						</div>
					</div>
				</div>
				<div class="mb-3" v-else></div>
			</div>
		</div>
	</div>
</template>
<script>
let fp;

import formatPhone from '@/filters/formatPhone.filter';
import { AxiosHelper } from '@/helpers/axiosHelper';
import { mapGetters } from 'vuex';

export default {
	name: 'AppTasksViewModal',
	filters: {
		formatPhone,
	},
	props: {
		params: {
			type: Object,
			required: true,
		},
		record: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			loading: false,
			todoResult: null,
			reCreate: this.emptyRecord(),
		};
	},
	computed: {
		canManage() {
			return (
				(this.uid === this.record.executor.id ||
					this.whoami.is_admin) &&
				!this.isFinalStatus
			);
		},
		createdBy() {
			let name = this.record.author.name,
				names = name.split(' ');

			return (
				names[0] +
				(names[1] ? ' ' + names[1][0] : '') +
				(names[2] ? ' ' + names[2][0] : '')
			);
		},
		executorName() {
			if (this.uid === this.record.executor.id) return 'You';

			let name = this.record.executor.name,
				names = name.split(' ');

			return (
				names[0] +
				(names[1] ? ' ' + names[1][0] : '') +
				(names[2] ? ' ' + names[2][0] : '')
			);
		},
		isFinalStatus() {
			return [2, 3, 4].includes(this.record.status_id);
		},
		isOverdue() {
			return (
				this.record.due_date && moment().diff(this.record.dueDate) >= 0
			);
		},
		linkClass() {
			let link = 'fal fa-external-link';
			if (this.record.type_id) {
				link = this.types[this.record.type_id].icon;
			}

			return {
				[link]: true,
			};
		},
		observers() {
			let users = [];
			this.record.subscribers.forEach((uid) => {
				let name = this.users[uid.user_id].name,
					names = name.split(' ');

				name =
					names[0] +
					(names[1] ? ' ' + names[1][0] : '') +
					(names[2] ? ' ' + names[2][0] : '');

				users.push(name);
			});
			return users.join(', ');
		},
		originalDueDate() {
			return moment
				.utc(this.record.miscs.original.due_date, 'YYYY-MM-DD HH:mm:ss')
				.local()
				.format('ll [at] h:mm a');
		},
		typeColorStyle() {
			let color = this.record.type_id
				? this.types[this.record.type_id].color
				: '#1dc9b7';

			return {
				color,
			};
		},
		uid() {
			return this.whoami.uid;
		},
		...mapGetters({
			users: 'appTasks/users',
			statuses: 'appTasks/statuses',
			types: 'appTasks/types',
			whoami: 'appTasks/whoami',
			activeTypes: 'appTasks/activeTypes',
		}),
	},
	mounted() {
		$('#modal-view-task').modal('show');
		$('#modal-view-task')
			.on('shown.bs.modal', () => {
				this.select2Icon();

				this.reCreate = this.emptyRecord();

				this.$nextTick(() => {
					if (this.canManage)
						fp = flatpickr('#due_date_view', {
							enableTime: true,
							dateFormat: 'Z',
							altInput: true,
							altFormat: 'm/d/Y h:i K',
							minDate: 'today',
							minuteIncrement: 15,
						});
				});
			})
			.on('hide.bs.modal', () => {
				if (this.canManage) fp.destroy();
			});
	},
	methods: {
		emptyRecord() {
			return {
				enabled: false,
				type_id: null,
				due_date: null,
			};
		},
		modifyTask(mode, val) {
			let data = {
				id: this.record.id,
				mode,
				val,
				result: this.todoResult,
			};

			let msg = 'Task updated';
			if (this.reCreate.enabled) {
				msg = 'New task created';
				// Time to UTC
				let due_date = this.reCreate.due_date
					? moment
							.utc(moment(this.reCreate.due_date))
							.format('YYYY-MM-DD HH:mm:ss')
					: null;

				data.reCreate = {
					due_date,
					type_id: this.reCreate.type_id,
				};
			}

			this.loading = true;
			AxiosHelper({
				url: '/tasks/modifyTask',
				data,
			})
				.then((resp) => {
					$('#modal-view-task').modal('hide');
					App.Forms.showAlert('success', msg);

					this.$store.dispatch('appTasks/updateRecords', [
						resp.record,
					]);
					if (resp.new_record) {
						this.$store.dispatch('appTasks/updateRecords', [
							resp.new_record,
						]);
						this.todoResult = null;
					}
				})
				.finally(() => (this.loading = false));
		},
		select2Icon() {
			$('#type_id')
				.select2({
					dropdownParent: $('#modal-view-task'),
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
				// elm.element;
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
		setDueDateDays(days = 1) {
			this.reCreate.enabled = true;

			let date = moment()
				.add(days, 'days')
				.format('YYYY-MM-DDTHH:mm:ssZ');
			fp.setDate(date, true);
		},
	},
};
</script>

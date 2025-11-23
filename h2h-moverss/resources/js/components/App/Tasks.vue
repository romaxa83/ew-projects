<template>
	<div class="d-none d-md-block flex-shrink-1" style="min-width: 110px">
		<div class="panel notify-panel h-100" :class="{ open: panelOpen }">
			<div class="panel-hdr">
				<h2>Tasks</h2>
				<div class="panel-toolbar mr-1 hide-on-closed">
					<button
						@click="openCreateModal()"
						class="btn btn-primary btn-xs btn-icon rounded-circle waves-effect waves-themed"
					>
						<i class="fal fa-plus"></i>
					</button>
				</div>
				<div class="panel-toolbar">
					<h5 class="m-0 cursor-pointer" @click="toggleOpen">
						<span
							class="badge badge-primary fw-400 l-h-n fs-xl pt-0"
						>
							{{ !panelOpen ? '&laquo;' : '»' }}
						</span>
					</h5>
				</div>
			</div>
			<div class="panel-container">
				<div class="panel-content">
					<div v-if="!loading" class="d-flex justify-content-center">
						<div class="spinner-border" role="status">
							<span class="sr-only">Loading...</span>
						</div>
					</div>
					<template v-else>
						<div class="total-badges fs-xl mb-4">
							<div class="mb-1">
								<span
									class="badge badge-success"
									v-text="stat.today"
								></span>
								<span class="ml-1 opacity-80">For today</span>
							</div>
							<div class="mb-1">
								<span
									class="badge badge-warning"
									v-text="stat.open"
								></span>
								<span class="ml-1 opacity-80">Open</span>
							</div>
							<div class="mb-1">
								<span
									class="badge badge-danger"
									v-text="stat.overdue"
								></span>
								<span class="ml-1 opacity-80">Overdue</span>
							</div>
						</div>

						<div class="form-group">
							<select
								id="active"
								class="form-control"
								v-model="filter"
							>
								<option value="all">All</option>
								<option value="author">You is author</option>
								<option value="executor">
									You is executor
								</option>
							</select>
						</div>

						<app-tasks-item
							v-for="(v, i) in filteredRecords"
							:key="i"
							:record="v"
							:index="i"
							@openRecord="openViewModal"
						></app-tasks-item>
					</template>
				</div>
			</div>
		</div>

		<app-tasks-create-modal
			v-if="createModal"
			:params="modalParams"
		></app-tasks-create-modal>

		<app-tasks-view-modal
			v-if="viewModal"
			:params="modalParams"
			:record="record"
		></app-tasks-view-modal>
	</div>
</template>

<script>
import Cookies from 'js-cookie';
import { mapGetters } from 'vuex';
import AppTasksItem from './Tasks/Item';

const AppTasksCreateModal = () =>
	import(/* webpackChunkName: "AppTasksCreateModal" */ './Tasks/CreateModal');
const AppTasksViewModal = () =>
	import(/* webpackChunkName: "AppTasksViewModal" */ './Tasks/ViewModal');

export default {
	name: 'AppTasks',
	components: { AppTasksItem, AppTasksCreateModal, AppTasksViewModal },
	data() {
		return {
			panelOpen: false,
			createModal: false,
			viewModal: false,
			modalParams: {},
			record: {},
			filter: 'all',
		};
	},
	computed: {
		filteredRecords() {
			let records = this.sidebarRecords;
			if (this.filter === 'author') {
				records = records.filter(
					(item) => item.user_id === this.whoami.uid
				);
			} else if (this.filter === 'executor') {
				records = records.filter(
					(item) => item.executor_id === this.whoami.uid
				);
			}

			return records.slice(0, 20);
		},
		stat() {
			let from = moment().utc(),
				to = moment().utc().endOf('day');

			let overdue = this.sidebarRecords.slice().reduce((sum, item) => {
					if (item.dueDate < from) sum++;

					return sum;
				}, 0),
				today = this.sidebarRecords.slice().reduce((sum, item) => {
					if (item.dueDate > from && item.dueDate < to) sum++;

					return sum;
				}, 0),
				open = this.sidebarRecords.slice().reduce((sum, item) => {
					if (item.dueDate > to) sum++;

					return sum;
				}, 0);

			return {
				overdue: overdue,
				today: today,
				open: open,
			};
		},
		...mapGetters({
			isInitialized: 'appTasks/isInitialized',
			loading: 'appTasks/isAdditionalInitialized',
			whoami: 'appTasks/whoami',
			sidebarRecords: 'appTasks/sidebarRecords',
			records: 'appTasks/records',
		}),
	},
	async mounted() {
		if (!this.isInitialized) {
			await this.$store.dispatch('appTasks/initData');
		}

		if (Cookies.get('app_tasks_open') === 'true') {
			this.panelOpen = true;
		}
	},
	methods: {
		fetchData(onlyData = false) {
			alert('fetchData');
		},
		openCreateModal(params = {}) {
			this.modalParams = params;
			if (!this.createModal) this.createModal = true;
			else $('#modal-create-task').modal('show');
		},
		async openViewModal(params = {}) {
			this.modalParams = params;

			let findRecord = this.records.findIndex(
				(item) => item.id === params.id
			);
			if (findRecord !== -1) {
				this.record = this.records[findRecord];
			} else {
				await axios
					.post('/tasks/view', {
						id: params.id,
					})
					.then((resp) => {
						if (resp.data.success === true) {
							this.record = resp.data.record;
						} else {
							throw {
								response: {
									data: resp.data,
								},
							};
						}
					})
					.catch((error) => {
						App.Forms.simpleErrors(error.response.data);
					});
			}

			if (!this.viewModal) this.viewModal = true;
			else $('#modal-view-task').modal('show');
		},
		toggleOpen() {
			this.panelOpen = !this.panelOpen;
			Cookies.set('app_tasks_open', this.panelOpen, { expires: 7 });
		},
	},
};
</script>

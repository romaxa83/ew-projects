<template>
	<div class="row">
		<div class="col-xl-12">
			<div class="panel" v-if="tasksEnvironment">
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
				<div class="panel-hdr">
					<h2>Tasks Calendar</h2>
					<div class="panel-toolbar">
						<button
							type="button"
							class="btn btn-md btn-primary waves-effect waves-themed"
							v-b-modal="'create-new-task'"
						>
							+ New Task
						</button>
					</div>
				</div>
				<div class="panel-container show">
					<div
						class="panel-content border-bottom border-bottom-left-radius-0 border-bottom-right-radius-0"
					>
						<div class="row">
							<div class="col-sm-6 col-lg-4 mb-2">
								<div class="form-group">
									<label class="form-label"
										>Manager (executor)</label
									>
									<vue-select2
										v-if="uid.id"
										ref="manager"
										v-model="filter.manager"
										:config="configSelect2()"
									>
										<option
											:value="uid.id"
											v-text="uid.title"
										></option>
										<!--                                        <option v-for="manager of activeUsers" :value="manager.id">{{ manager.name }}</option>-->
									</vue-select2>
								</div>
							</div>
							<div class="col-sm-6 col-lg-3 mb-2">
								<div class="form-group">
									<label class="form-label">Status</label>
									<select
										v-model="filter.status"
										class="form-control"
									>
										<option value="inwork">InWork</option>
										<option value="completed">
											Completed
										</option>
										<option value="all">All</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-container show">
					<div class="panel-content bg-faded">
						<div class="d-flex pipeline-cols-container w-100">
							<div
								class="pipeline-col"
								v-if="tasksOverdued.length"
							>
								<div
									class="p-2 bg-danger-800 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											Overdue Tasks
											<small class="m-0 l-h-n"
												>{{
													tasksOverdued.length
												}}
												to-dos</small
											>
										</h4>
									</div>
									<i
										class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n4 mr-n4"
										style="font-size: 6rem"
									></i>
								</div>
								<!--                                                                <div class="p-2 bg-white rounded overflow-hidden position-relative border border-danger">-->
								<!--                                                                    <div class="">-->
								<!--                                                                        <h4 class="d-block l-h-n m-0 fw-500">-->
								<!--                                                                            Overdue-->
								<!--                                                                            <small class="m-0 l-h-n">2 to-dos</small>-->
								<!--                                                                        </h4>-->
								<!--                                                                    </div>-->
								<!--                                                                    &lt;!&ndash;<i class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>&ndash;&gt;-->
								<!--                                                                </div>-->

								<vue-lazy-component>
									<!-- real component-->
									<ul
										v-if="tasksEnvironment"
										class="pipeline-list"
									>
										<vue-lazy-component
											v-for="(
												record, key
											) in tasksOverdued"
											:key="key"
										>
											<!-- real component-->
											<task
												:record="record"
												:key="key"
												:filter="filter"
												:datetime="
													localDT(record.timestamp)
												"
												interface="calendar"
											/>
											<!-- skeleton component -->
											<content-placeholders
												style="margin: 20px 0"
												slot="skeleton"
											>
												<content-placeholders-heading
													:img="true"
												/>
												<content-placeholders-text
													:lines="1"
												/>
											</content-placeholders>
										</vue-lazy-component>
									</ul>
									<!-- skeleton component -->
									<content-placeholders
										style="margin: 20px 0"
										slot="skeleton"
									>
										<content-placeholders-heading
											:img="true"
										/>
										<content-placeholders-text :lines="1" />
									</content-placeholders>
								</vue-lazy-component>
							</div>

							<div class="pipeline-col" v-if="tasksToday.length">
								<div
									class="p-2 bg-warning-500 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											To-Do Today
											<small class="m-0 l-h-n"
												>{{
													tasksToday.length
												}}
												to-dos</small
											>
										</h4>
									</div>
									<i
										class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n4 mr-n4"
										style="font-size: 6rem"
									></i>
								</div>

								<vue-lazy-component>
									<!-- real component-->
									<ul
										v-if="tasksEnvironment"
										class="pipeline-list"
									>
										<vue-lazy-component
											v-for="(record, key) in tasksToday"
											:key="key"
										>
											<!-- real component-->
											<task
												:record="record"
												:key="key"
												:filter="filter"
												:datetime="
													localDT(record.timestamp)
												"
												interface="calendar"
											/>
											<!-- skeleton component -->
											<content-placeholders
												style="margin: 20px 0"
												slot="skeleton"
											>
												<content-placeholders-heading
													:img="true"
												/>
												<content-placeholders-text
													:lines="1"
												/>
											</content-placeholders>
										</vue-lazy-component>
									</ul>
									<!-- skeleton component -->
									<content-placeholders
										style="margin: 20px 0"
										slot="skeleton"
									>
										<content-placeholders-heading
											:img="true"
										/>
										<content-placeholders-text :lines="1" />
									</content-placeholders>
								</vue-lazy-component>
							</div>

							<div
								class="pipeline-col"
								v-if="tasksTommorow.length"
							>
								<div
									class="p-2 bg-primary-500 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											To-Do Tomorrow
											<small class="m-0 l-h-n"
												>{{
													tasksTommorow.length
												}}
												to-dos</small
											>
										</h4>
									</div>
									<i
										class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n4 mr-n4"
										style="font-size: 6rem"
									></i>
								</div>

								<vue-lazy-component>
									<!-- real component-->
									<ul
										v-if="tasksEnvironment"
										class="pipeline-list"
									>
										<vue-lazy-component
											v-for="(
												record, key
											) in tasksTommorow"
											:key="key"
										>
											<!-- real component-->
											<task
												:record="record"
												:key="key"
												:filter="filter"
												:datetime="
													localDT(record.timestamp)
												"
												interface="calendar"
											/>
											<!-- skeleton component -->
											<content-placeholders
												style="margin: 20px 0"
												slot="skeleton"
											>
												<content-placeholders-heading
													:img="true"
												/>
												<content-placeholders-text
													:lines="1"
												/>
											</content-placeholders>
										</vue-lazy-component>
									</ul>
									<!-- skeleton component -->
									<content-placeholders
										style="margin: 20px 0"
										slot="skeleton"
									>
										<content-placeholders-heading
											:img="true"
										/>
										<content-placeholders-text :lines="1" />
									</content-placeholders>
								</vue-lazy-component>
							</div>
							<div class="pipeline-col" v-if="tasksWeek.length">
								<div
									class="p-2 bg-primary-500 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											To-Do This week
											<small class="m-0 l-h-n"
												>{{
													tasksWeek.length
												}}
												to-dos</small
											>
										</h4>
									</div>
									<i
										class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n4 mr-n4"
										style="font-size: 6rem"
									></i>
								</div>
								<vue-lazy-component>
									<!-- real component-->
									<ul
										v-if="tasksEnvironment"
										class="pipeline-list"
									>
										<vue-lazy-component
											v-for="(record, key) in tasksWeek"
											:key="key"
										>
											<!-- real component-->
											<task
												:record="record"
												:key="key"
												:filter="filter"
												:datetime="
													localDT(record.timestamp)
												"
												interface="calendar"
											/>
											<!-- skeleton component -->
											<content-placeholders
												style="margin: 20px 0"
												slot="skeleton"
											>
												<content-placeholders-heading
													:img="true"
												/>
												<content-placeholders-text
													:lines="1"
												/>
											</content-placeholders>
										</vue-lazy-component>
									</ul>
									<!-- skeleton component -->
									<content-placeholders
										style="margin: 20px 0"
										slot="skeleton"
									>
										<content-placeholders-heading
											:img="true"
										/>
										<content-placeholders-text :lines="1" />
									</content-placeholders>
								</vue-lazy-component>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<b-modal
			id="create-new-task"
			ref="create-new-task"
			hide-backdrop
			size="lg"
			centered
			header-class="d-none"
			footer-class="d-none"
		>
			<input-interface-task
				interface="calendar"
				@close-modal="$refs['create-new-task'].hide()"
			/>
		</b-modal>
	</div>
</template>

<script>
// let fp;

import formatDateTime from '@/filters/formatDateTime.filter';
// import AppTasksCalendarItem from "@components/App/TasksCalendar/Item";
import Task from '@components/Order/TabOverview/CommunicationPanel/History/Task';
import InputInterfaceTask from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceTask';
// import localDateTime from "@/filters/formatDateTime.filter";
import VueSelect2 from '@components/VueSelect2';
import { component as VueLazyComponent } from '@xunlei/vue-lazy-component';
import { BModal, VBModal } from 'bootstrap-vue';
// https://codepen.io/thenutz/pen/VwYeYEE
import debounce from 'lodash/debounce';
import {
	ContentPlaceholders,
	ContentPlaceholdersHeading,
	ContentPlaceholdersImg,
	ContentPlaceholdersText,
} from 'vue-content-placeholders';
import { mapGetters } from 'vuex';

export default {
	name: 'AppTasksCalendar',
	components: {
		InputInterfaceTask,
		VueSelect2,
		Task,
		BModal,
		ContentPlaceholders,
		ContentPlaceholdersText,
		ContentPlaceholdersHeading,
		ContentPlaceholdersImg,
		'vue-lazy-component': VueLazyComponent,
		// AppTasksCalendarItem
	},

	directives: {
		'b-modal': VBModal,
	},
	data() {
		return {
			showOld: false,
			loading: true,
			uid: {
				id: 0,
				title: $('.profile-image').attr('alt'),
			},
			//loading: 'appTasks/loading',
			dateRange: null,
			// divisions: App.Miscs.getDivisions(),
			filter: {
				manager: [],
				status: 'inwork',
			},
			cacheDataSource: {},
		};
	},
	computed: {
		filterManager() {
			return this.filter.manager;
		},
		filterStatus() {
			return this.filter.status;
		},
		// selectedUser: {
		//     get() {
		//         return this.filters.user;
		//     },
		//     set(user_id) {
		//         this.$store.commit('appTasks/setFilterUser', user_id);
		//         this.$store.dispatch('appTasks/initData')
		//     }
		// },
		tasksEnvironment() {
			return this.$store.state.tasksCalendar.env;
		},
		timezone() {
			return this.$store.state.tasksCalendar.timezone;
		},

		...mapGetters({
			tasksToday: 'tasksCalendar/tasksToday',
			tasksTommorow: 'tasksCalendar/tasksTommorow',
			tasksWeek: 'tasksCalendar/tasksWeek',
			tasksOverdued: 'tasksCalendar/tasksOverdued',
			activeUsers: 'tasksCalendar/activeUsers',

			//loading: 'appTasks/loading',
			isInitialized: 'appTasks/isInitialized',
			isAdditionalInitialized: 'appTasks/isAdditionalInitialized',
			overdue: 'appTasks/overdue',
			today: 'appTasks/today',
			tomorrow: 'appTasks/tomorrow',
			thisWeek: 'appTasks/thisWeek',
			types: 'appTasks/activeTypes',
			statuses: 'appTasks/statuses',
			users: 'appTasks/activeUsers',
			whoami: 'appTasks/whoami',
			filters: 'appTasks/filters',
		}),
	},
	mounted() {
		this.$store.dispatch('tasksCalendar/fetchEnvironment').then(() => {
			this.uid.id = this.$store.state.tasksCalendar.whoami.uid;
			this.filter.manager.push(this.uid.id);
			// this.$store.dispatch('tasksCalendar/fetchTasks', this.filter)
		});
		// if (!this.isInitialized) {
		//     await this.$store.dispatch('appTasks/initData')
		// }
	},
	methods: {
		loadTasks: debounce(function () {
			this.loading = true;
			this.$store
				.dispatch('tasksCalendar/fetchTasks', this.filter)
				.finally(() => (this.loading = false))
				.catch((error) => App.Forms.simpleErrors(error));
		}, 500),
		// loadTasks() {
		//     this.$store.dispatch('tasksCalendar/fetchTasks', this.filter)
		// },
		configSelect2() {
			return {
				query: (query) => {
					let key = query.term;
					let cachedData = this.cacheDataSource[key];

					let formatResults = function (response) {
						return {
							results: response.results.map((item) => {
								return {
									id: item.id,
									text: item.name,
									roles:
										item.roles && item.roles.length
											? item.roles.map((v) => v.title)
											: [],
								};
							}),
							pagination: response.pagination,
						};
					};

					if (cachedData) {
						query.callback({ results: cachedData });
					} else {
						let data = {
							q: key, // search term
							page: query.page || 1,
						};
						$.ajax({
							url: '/company/employees/autocomplete',
							dataType: 'json',
							type: 'POST',
							data,
							success: (data) => {
								data = formatResults(data);

								this.cacheDataSource[key] = data.results;
								query.callback({ results: data.results });
							},
						});
					}
				},
				data: function () {
					// Это триндос! Без этого пустого метода не селектит, причем в него даже не заходит, тупо дииич!
					// Два часа шпиливилли :evil:
				},
				templateResult: (item) => {
					if (item.id) {
						let option = `<div class="d-flex"><div>${item.text}</div><div class="ml-auto">`;
						for (const role of item.roles) {
							option += `<span class="ml-1 badge badge-primary">${role}</span>`;
						}
						option += `</div></div>`;
						return $(option);
					} else return item;
				},
				placeholder: 'No matter',
				multiple: true,
			};
		},
		localDT(timestampUTC) {
			return formatDateTime(timestampUTC, this.timezone);
		},
		// changeDivision(k = null) {
		//     k = k ? parseInt(k) : k;
		//
		//     this.$store.commit('appTasks/setFilterDivision', k);
		//
		//     $('.panel-hdr').click();
		// },
		// changeStatus(status_id) {
		//     this.$store.commit('appTasks/setFilterStatus', status_id);
		//     this.$store.dispatch('appTasks/initData');
		//
		//     $('.panel-hdr').click();
		// },
	},
	watch: {
		filterManager(newVal, oldVal) {
			this.loadTasks();
		},
		filterStatus(newVal, oldVal) {
			this.loadTasks();
		},
	},
};
</script>

<style lang="scss">
.pipeline-cols-container {
	flex-direction: row;
	-webkit-flex-direction: row;
	overflow-x: auto;
	width: 100%;
	align: center;
	min-height: 80vh;
	//width: 1200px;
}

.pipeline-col {
	padding-right: 0.5rem;
	padding-left: 0.5rem;
	//width: 350px;
	//width:350px;
	max-width: 450px;
	min-width: 350px;
	flex: 0 0 25%;
	//flex: 0 0 25%;

	&:first-child {
		margin-left: auto;
	}

	&:last-child {
		margin-right: auto;
	}
}

/*.pipeline-col:first-child {*/
/*    margin-left: 0px !important;*/
/*}*/

//.pipeline-col:last-child {
//    margin-right: 0px !important;
//}

.pipeline-list {
	list-style-type: none;
	position: relative;
	padding: 0;

	& li {
		margin: 20px 0;
	}
}

/*.pipeline-cols-container::-webkit-scrollbar {*/
/*    display: none;*/
/*}*/
</style>

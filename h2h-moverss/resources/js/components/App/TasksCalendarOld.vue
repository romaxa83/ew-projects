<template>
	<div class="row">
		<div class="col-xl-12">
			<div class="panel">
				<div class="panel-hdr">
					<h2>Tasks Calendar</h2>
				</div>
				<div class="panel-container show">
					<div
						class="panel-content border-bottom border-bottom-left-radius-0 border-bottom-right-radius-0"
					>
						filter
					</div>
				</div>
				<div class="panel-container show">
					<div class="panel-content bg-faded">
						<div
							class="d-flex tasks-cols-container"
							style="overflow-x: auto"
						>
							<div class="task-col">
								<div
									class="p-2 bg-danger-800 rounded overflow-hidden position-relative text-white mb-g"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											Overdue
											<small class="m-0 l-h-n"
												>2 to-dos</small
											>
										</h4>
									</div>
									<!--<i class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>-->
								</div>
								<!--                                <div class="p-2 bg-white rounded overflow-hidden position-relative border border-danger">-->
								<!--                                    <div class="">-->
								<!--                                        <h4 class="d-block l-h-n m-0 fw-500">-->
								<!--                                            Overdue-->
								<!--                                            <small class="m-0 l-h-n">2 to-dos</small>-->
								<!--                                        </h4>-->
								<!--                                    </div>-->
								<!--                                    &lt;!&ndash;<i class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>&ndash;&gt;-->
								<!--                                </div>-->

								<div></div>
							</div>

							<div class="task-col">
								<div
									class="p-2 bg-warning-500 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											Today
											<small class="m-0 l-h-n"
												>3 to-dos</small
											>
										</h4>
									</div>
									<!--<i class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>-->
								</div>
							</div>

							<div class="task-col">
								<div
									class="p-2 bg-primary-500 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											Tomorrow
											<small class="m-0 l-h-n"
												>0 to-dos</small
											>
										</h4>
									</div>
									<!--<i class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>-->
								</div>
							</div>
							<div class="task-col">
								<div
									class="p-2 bg-primary-500 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											This week
											<small class="m-0 l-h-n"
												>6 to-dos</small
											>
										</h4>
									</div>
									<!--<i class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>-->
								</div>
							</div>

							<!--                            <div class="task-col">-->
							<!--                            </div>-->

							<!--                            <div class="task-col">-->
							<!--                            </div>-->
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xl-12">
			<div id="panel-1" class="panel">
				<div class="panel-hdr">
					<h2>Tasks This Week</h2>
					<div class="panel-toolbar">
						<span
							v-show="loading"
							class="spinner-border spinner-border-sm mr-2"
							role="status"
							aria-hidden="true"
						></span>

						<!--                        <div class="btn-group mr-2">-->
						<!--                            <input class="form-control flatpickr" id="date_range" v-model="dateRange">-->
						<!--                        </div>-->
						<template v-if="isAdditionalInitialized">
							<div class="btn-group" v-if="whoami.is_multiple">
								<button
									class="btn btn-outline-secondary dropdown-toggle ml-2"
									type="button"
									data-toggle="dropdown"
									aria-haspopup="true"
									aria-expanded="false"
								>
									Branch:
									{{
										filters.division_id
											? divisions[filters.division_id]
											: 'All'
									}}
								</button>
								<div class="dropdown-menu">
									<a
										class="dropdown-item"
										href="#"
										:class="{
											active: !filters.division_id,
										}"
										@click.prevent="changeDivision()"
										>All</a
									>
									<a
										class="dropdown-item"
										href="#"
										:class="{
											active: filters.division_id == k,
										}"
										v-for="(division, k) in divisions"
										:key="k"
										@click.prevent="changeDivision(k)"
										>{{ division }}</a
									>
								</div>
							</div>
							<div class="btn-group ml-2">
								<button
									class="btn btn-outline-secondary dropdown-toggle"
									type="button"
									data-toggle="dropdown"
									aria-haspopup="true"
									aria-expanded="false"
								>
									Status: {{ statuses[filters.status].title }}
								</button>
								<div class="dropdown-menu">
									<a
										class="dropdown-item"
										:class="{
											active: filters.status === 1,
										}"
										href="#"
										@click.prevent="changeStatus(1)"
										>{{ statuses[1].title }}</a
									>
									<a
										class="dropdown-item"
										:class="{
											active: filters.status === 3,
										}"
										href="#"
										@click.prevent="changeStatus(3)"
										>{{ statuses[3].title }}</a
									>
								</div>
							</div>
							<div class="btn-group ml-2">
								<select
									v-model="selectedUser"
									class="form-control select2"
								>
									<option :value="whoami.uid">
										My tasks
									</option>
									<option
										:value="null"
										v-if="whoami.is_admin"
									>
										All tasks
									</option>
									<option
										v-for="v in users"
										:key="v.id"
										v-bind:value="v.id"
									>
										{{ v.name }}
									</option>
								</select>
							</div>
						</template>
					</div>
				</div>
				<div class="panel-container show">
					<div class="panel-content">
						<div
							v-if="loading"
							class="d-flex justify-content-center"
						>
							<div class="spinner-border" role="status">
								<span class="sr-only">Loading...</span>
							</div>
						</div>
						<div class="row">
							<div class="col col-sm col-xl">
								<div
									style="border-bottom: 2px #c02a2a solid"
									class="mb-2"
								>
									<h4>Overdue</h4>
									{{ overdue.length }} Todo
								</div>

								<app-tasks-calendar-item
									class="border-danger"
									v-for="v in overdue"
									:key="v.id"
									:record="v"
								></app-tasks-calendar-item>
							</div>
							<div class="col col-sm col-xl">
								<div
									style="border-bottom: 2px #66d554 solid"
									class="mb-2"
								>
									<h4>Today</h4>
									{{ today.length }} Todo
								</div>

								<app-tasks-calendar-item
									class="border-success"
									v-for="v in today"
									:key="v.id"
									:record="v"
								></app-tasks-calendar-item>
							</div>
							<div class="col col-sm col-xl">
								<div
									style="border-bottom: 2px #66d554 solid"
									class="mb-2"
								>
									<h4>Tomorrow</h4>
									{{ tomorrow.length }} Todo
								</div>

								<app-tasks-calendar-item
									class="border-success"
									v-for="v in tomorrow"
									:key="v.id"
									:record="v"
								></app-tasks-calendar-item>
							</div>
							<div class="col col-sm col-xl">
								<div
									style="border-bottom: 2px #888888 solid"
									class="mb-2"
								>
									<h4>This week</h4>
									{{ thisWeek.length }} Todo
								</div>

								<app-tasks-calendar-item
									class="border-primary"
									v-for="v in thisWeek"
									:key="v.id"
									:record="v"
								></app-tasks-calendar-item>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
let fp;

import { mapGetters } from 'vuex';
import AppTasksCalendarItem from '@components/App/TasksCalendar/Item';

// https://codepen.io/thenutz/pen/VwYeYEE

export default {
	name: 'AppTasksCalendarOld',
	components: { AppTasksCalendarItem },
	data() {
		return {
			dateRange: null,
			divisions: App.Miscs.getDivisions(),
		};
	},
	computed: {
		selectedUser: {
			get() {
				return this.filters.user;
			},
			set(user_id) {
				this.$store.commit('appTasks/setFilterUser', user_id);
				this.$store.dispatch('appTasks/initData');
			},
		},
		...mapGetters({
			loading: 'appTasks/loading',
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
	async mounted() {
		if (!this.isInitialized) {
			await this.$store.dispatch('appTasks/initData');
		}

		// this.dateRange = `${this.filters.dateFrom} to ${this.filters.dateTo}`;
		//
		// fp = flatpickr('#date_range', {
		//     mode: "range",
		//     minDate: "2021-01-01",
		//     dateFormat: "Y-m-d",
		//     defaultDate: [this.filters.dateFrom, this.filters.dateTo],
		//     altInput: true,
		//     altFormat: "m/d/Y",
		//     onChange: (selectedDates) => {
		//         if (selectedDates[1]) {
		//             let dateFrom = moment(selectedDates[0]).format('YYYY-MM-DD'),
		//                 dateTo = moment(selectedDates[1]).format('YYYY-MM-DD');
		//
		//             this.$store.commit('appTasks/setFilterDates', {
		//                 dateFrom,
		//                 dateTo
		//             })
		//             this.$store.dispatch('appTasks/initData')
		//         }
		//     },
		// });
	},
	methods: {
		changeDivision(k = null) {
			k = k ? parseInt(k) : k;

			this.$store.commit('appTasks/setFilterDivision', k);

			$('.panel-hdr').click();
		},
		changeStatus(status_id) {
			this.$store.commit('appTasks/setFilterStatus', status_id);
			this.$store.dispatch('appTasks/initData');

			$('.panel-hdr').click();
		},
	},
};
</script>

<style>
.task-col {
	margin-right: 1rem;
	margin-left: 1rem;
	flex: 1 1 auto;
	/*flex: 0 0 25%;*/
}

.task-col:first-child {
	margin-left: 0px !important;
}

.task-col:last-child {
	margin-right: 0px !important;
}

.tasks-cols-container {
	overflow-x: auto;
}

/*.tasks-cols-container::-webkit-scrollbar {*/
/*    display: none;*/
/*}*/
</style>

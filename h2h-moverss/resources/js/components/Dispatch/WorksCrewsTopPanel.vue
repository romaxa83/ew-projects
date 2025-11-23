<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2 style="flex: none">
				Orders Services
				<span
					class="badge badge-pill fw-400 l-h-n"
					:class="[
						unscheduledWorksTotal > 0
							? 'badge-danger'
							: 'badge-success',
					]"
					>{{ unscheduledWorksTotal }}</span
				>
			</h2>
			<div class="flex-fill">
				<div class="btn-group">
					<button
						type="button"
						class="btn btn-sm btn-success dropdown-toggle waves-effect waves-themed"
						data-toggle="dropdown"
						aria-haspopup="true"
						aria-expanded="false"
					>
						Notify assigned employees
					</button>
					<div class="dropdown-menu" style="">
						<a
							class="dropdown-item"
							href="notify_all"
							@click.prevent="notifyAll"
							>Notify all (including notified)</a
						>
						<div class="dropdown-divider"></div>
						<a
							class="dropdown-item"
							href="notify_unnotified"
							@click.prevent="notifyUnnotofied"
							>Notify only unnotified</a
						>
					</div>
				</div>
			</div>
			<div class="panel-toolbar">
				<select
					class="custom-select custom-select-sm"
					v-model="filter_trucks"
				>
					<option :value="null">Truck filter disabled</option>
					<option value="unsigned">Without assigned trucks</option>
					<option
						v-for="(v, k) in trucks"
						:key="k"
						v-bind:value="v.id"
					>
						{{ v.title }}
					</option>
				</select>
			</div>
			<div class="panel-toolbar ml-2">
				<select
					class="custom-select custom-select-sm"
					v-model="filter_work_type"
				>
					<option :value="null">Any service type</option>
					<option
						v-for="(v, k) in uniqueWorksTypes"
						:key="k"
						v-bind:value="v.id"
					>
						{{ v.title }}
					</option>
				</select>
			</div>
			<div class="panel-toolbar ml-2">
				<div
					class="d-flex position-relative ml-auto"
					style="max-width: 9rem"
				>
					<i
						class="fal fa-search position-absolute pos-left fs-lg px-2 py-1 mt-1 fs-xs"
					></i>
					<input
						v-model="filter_order_id"
						type="text"
						class="form-control form-control-sm pl-5"
						placeholder="Filter by Order ID"
					/>
				</div>
			</div>
		</div>
		<div class="panel-container">
			<div class="panel-content">
				<div class="crew-works d-flex works-horizontal">
					<div v-if="loading" class="d-flex justify-content-center">
						<div class="spinner-border" role="status">
							<span class="sr-only">Loading...</span>
						</div>
					</div>
					<template v-else>
						<works-panel
							v-for="v in worksFilter"
							:key="`${v.id}`"
							:id="v.id"
							:originRecord="v"
							placement="crews"
							:can-manage="canManageSlots"
						></works-panel>

						<!-- Скрытая область с виртуальными сотрудниками для работы реактивности -->
						<div class="moveToSchedule-crew d-none">
							<works-panel
								v-for="v in virtualWorks('crews')"
								:key="v.randomRef"
								:id="v.id"
								:position="v.position"
								:originRecord="v"
								:data-employee-id="v.employee_id"
								placement="crews"
								:can-manage="canManageSlots"
							></works-panel>
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

const WorksPanel = () =>
	import(/* webpackChunkName: "DispatchWorkPanel" */ './WorkPanel');

export default {
	name: 'WorksCrewsTopPanel',
	components: {
		WorksPanel,
	},
	props: {
		canManage: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			filter_trucks: null,
			filter_work_type: null,
			filter_order_id: null,
			canManageSlots: this.canManage === '1',
		};
	},
	computed: {
		unscheduledWorksTotal() {
			const calculated = Object.values(this.works).reduce(function (
				sum,
				item
			) {
				return sum + (item.employees - item.dispatch_employees.length);
			},
			0);
			return calculated - this.$store.state.dispatch.hiddenWorks > 0
				? calculated - this.$store.state.dispatch.hiddenWorks
				: 0;
		},
		// Отфильтрованный список заказов
		worksFilter() {
			return Object.values(this.works)
				.filter((item) => {
					return (
						item.start_date ==
						this.$store.state.dispatch.dispatchDay
					);
				})
				.filter((item) => {
					// Фильтровать по типам работ
					return this.filter_work_type &&
						!item.work_types_keys.includes(this.filter_work_type)
						? false
						: true;
				})
				.filter((item) => {
					// Фильтровать по номеру заказа
					return this.filter_order_id &&
						item.order.id
							.toString()
							.indexOf(this.filter_order_id) === -1
						? false
						: true;
				})
				.filter((item) => {
					// Фильтровать по тракам
					if (this.filter_trucks && parseInt(this.filter_trucks))
						return item.dispatch_trucks_ids.includes(
							this.filter_trucks
						);
					else if (this.filter_trucks === 'unsigned')
						return item.dispatch_employees.length < item.employees;

					return true;
				});
		},
		...mapGetters({
			works: 'dispatch/getWorks',
			trucks: 'dispatch/getActiveTrucks',
			virtualWorks: 'dispatch/virtualWorks',
			uniqueWorksTypes: 'dispatch/getUniqueWorksTypes',
		}),
	},
	mounted() {
		this.$store.dispatch('dispatch/isWorksLoaded').then(() => {
			this.loading = false;
			setTimeout(() => this.moveToSchedule(), 700); // FIXME Промиса похоже не достаточно
		});
	},
	methods: {
		// Есть ранее связанные Сотрудники с задачей, переносим на расписание
		moveToSchedule() {
			$('.moveToSchedule-crew .panel-tag').each(function () {
				let el = $(this),
					employeeId = el.data('employee-id'),
					start = el.data('start'),
					duration = el.data('duration');

				let line = $(
					`.gantt-crews div[data-employee-id="${employeeId}"] .gantt__row-bars`
				);
				if (line && employeeId) {
					let cell = $(
						`<div class="cell crew-work c-start-${start} c-span-${duration}"></div>`
					);
					line.append(cell.append(el));
				}
			});
		},
		notifyAll(e) {
			const fpDate =
				$('#dispatch_date')[0]._flatpickr.latestSelectedDateObj;
			//console.log(moment(fpDate));
			//return false;
			return this.notifyRequest({
				date: moment(fpDate).format('YYYY-MM-DD'),
				type: 'all',
			})
				.then(() => {
					window.location.reload(true);
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				});
		},
		notifyRequest(data) {
			$('#page-spinner').removeClass('d-none');
			const fpDate =
				$('#dispatch_date')[0]._flatpickr.latestSelectedDateObj;
			return new Promise((resolve, reject) => {
				axios
					.post('/dispatch/notifyAll', data)
					.then((resp) => {
						if (resp.data.success === true) {
							if (resp.data.msgs.length > 0)
								for (const msg of resp.data.msgs)
									App.Forms.showAlert('warning', msg);
							App.Forms.showAlert(
								'success',
								resp.data.count + ' notifications sent'
							);
							$('#page-spinner').removeClass('d-none');
							setTimeout(() => {
								resolve();
							}, 3000);

							// this.$store.dispatch('dispatch/initDispatchWorks', {
							//     currentDate: moment(fpDate).format('YYYY-MM-DD')
							// }).then(() => {
							//     $('#page-spinner').addClass('d-none');
							// })
							//     .catch((error) => {
							//         App.Forms.simpleErrors(error);
							//     })
						} else if (resp.data.success === false) {
							reject(resp.data.msg);
							// $('#page-spinner').addClass('d-none');
						}
					})
					.catch((error) => {
						reject(error.response.data);
						// App.Forms.simpleErrors(error.response.data);
					});
			});
		},
		notifyUnnotofied(e) {
			const fpDate =
				$('#dispatch_date')[0]._flatpickr.latestSelectedDateObj;
			return this.notifyRequest({
				date: moment(fpDate).format('YYYY-MM-DD'),
				type: 'unnotofied',
			})
				.then(() => {
					window.location.reload(true);
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
					$('#page-spinner').addClass('d-none');
				});
		},
	},
};
</script>

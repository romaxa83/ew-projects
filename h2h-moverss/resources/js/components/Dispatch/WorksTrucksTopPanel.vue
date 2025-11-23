<template>
	<div class="row" :hidden="hidePanel">
		<div class="col-lg-12">
			<div class="panel">
				<div class="panel-hdr">
					<h2>
						Unscheduled Jobs {{ demo }}
						<span
							class="badge badge-pill badge-danger fw-400 l-h-n"
							>{{ unscheduledWorksTotal }}</span
						>
					</h2>
					<div class="panel-toolbar">
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
						<div
							class="works-container d-flex works-horizontal moveToSchedule-truck"
						>
							<div
								v-if="loading"
								class="frame-wrap position-absolute w-100 h-100 opacity-50"
							>
								<div
									class="w-100 d-flex justify-content-center align-items-center"
								>
									<div
										class="spinner-border text-info position-absolute"
										style="top: 30%"
										role="status"
									>
										<span class="sr-only">Loading...</span>
									</div>
								</div>
							</div>
							<template v-else>
								<works-panel
									v-for="v in virtualWorks('trucks')"
									:key="v.randomRef"
									:id="v.id"
									:position="v.position"
									:originRecord="v"
									:data-truck-id="v.truck_id"
									:class="{ 'hide-cell': v.hide }"
									:can-manage="canManageSlots"
								></works-panel>
							</template>
						</div>
					</div>
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
	name: 'WorksTrucksTopPanel',
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
			filter_work_type: null,
			filter_order_id: null,
			canManageSlots: this.canManage === '1',
			hidePanel: this.canManage !== '1',
		};
	},
	computed: {
		demo() {
			console.log(this.test, typeof this.test);
			return 'x';
		},
		// Фильтрация работает добавлением класса hide-cell который скрывает значение только сверху
		unscheduledWorks() {
			let vm = this;
			return this.virtualWorks('trucks')
				.filter((item) => {
					return (
						!item.scheduled &&
						item.start_date == vm.$store.state.dispatch.dispatchDay
					);
				})
				.map((item) => {
					let hide = true;

					// Фильтровать по типам работ
					hide =
						hide &&
						vm.filter_work_type &&
						!item.work_types_keys.includes(vm.filter_work_type)
							? false
							: true;

					// Фильтровать по номеру заказа
					if (hide) {
						hide =
							vm.filter_order_id &&
							item.order.id
								.toString()
								.indexOf(vm.filter_order_id) === -1
								? false
								: true;
					}

					item.hide = !hide;
					return item;
				});
		},
		unscheduledWorksTotal() {
			return this.virtualWorks('trucks').filter((item) => {
				return (
					!+item.truck_id &&
					item.start_date == this.$store.state.dispatch.dispatchDay
				);
			}).length;
		},
		...mapGetters({
			virtualWorks: 'dispatch/virtualWorks',
			uniqueWorksTypes: 'dispatch/getUniqueWorksTypes',
		}),
	},
	mounted() {
		this.$store.dispatch('dispatch/isWorksLoaded').then(() => {
			this.loading = false;
			window.Dispatch.init();
			setTimeout(() => {
				this.moveToSchedule();
				removeTitleSpinner();
			}, 700); // FIXME Промиса похоже не достаточно
		});

		function removeTitleSpinner() {
			const spinner = document.getElementById('dispatch-title-spinner');
			if (spinner) {
				spinner.remove();
			}
		}
	},
	methods: {
		// Есть ранее связанные Сотрудники с задачей, переносим на расписание
		moveToSchedule() {
			$('.moveToSchedule-truck .panel-tag').each(function () {
				let el = $(this),
					truckId = el.data('truck-id'),
					start = el.data('start'),
					duration = el.data('duration');

				let line = $(
					`.gantt-trucks div[data-truck-id="${truckId}"] .gantt__row-bars`
				);
				if (line && truckId) {
					let cell = $(
						`<div class="cell truck-work c-start-${start} c-span-${duration}"></div>`
					);
					line.append(cell.append(el));
				}
			});
		},
	},
};
</script>

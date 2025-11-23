<template>
	<div class="row">
		<div class="col-lg-5 col-xl-4">
			<div class="row">
				<div class="col-md-6 col-lg-12">
					<a class="hidden-anchor" id="client"></a>
					<OrderClient ref="client" />
				</div>
				<div class="col-md-6 col-lg-12">
					<OverviewOrder
						:data-sources="dataSources"
						:can-clone="canManageOrder"
						:can-edit="canManageOrder"
					/>
				</div>

				<div v-if="communicationIsOpen" class="col-lg-12">
					<a class="hidden-anchor" id="works"></a>
					<OrderWorks
						:can-add-services="canManageOrder"
						:can-manage-item="canManageOrder"
						@recalculate="recalculate"
					/>
				</div>
				<div v-if="communicationIsOpen" class="col-lg-12">
					<a class="hidden-anchor" id="waypoints"></a>
					<OrderWaypoints
						:can-manage-waypoints="canManageOrder"
						@recalculate="recalculate"
					/>
				</div>
				<div v-if="communicationIsOpen" class="col-lg-12">
					<a class="hidden-anchor" id="estimate"></a>
					<OrderEstimate
						v-if="!loading"
						:can-manage="canManageOrder"
						:moveTypes="dataSources.moveTypes"
						@recalculate="recalculate"
						:processing="processing_calc"
					/>
				</div>
				<div v-if="communicationIsOpen" class="col-lg-12">
					<OrderCalculation
						ref="OrderCalculation"
						v-if="!loading"
						:can-manage="canManageOrder"
						:processing.sync="processing_calc"
					/>
				</div>
			</div>
		</div>

		<div class="col-lg-7 col-xl-8">
			<div
				class="row"
				:class="{ 'sticky-panel': orderCommunications == 'expanded' }"
			>
<!--				<div v-if="showCPv1" class="col-sm-12">-->
<!--					<CommunicationPanel />-->
<!--				</div>-->
				<div v-if="showCPv2" class="col-sm-12">
					<CommunicationPanel :v2="true" />
				</div>
			</div>
			<div class="row">
				<div v-if="!communicationIsOpen" class="col-xl-7 col-lg-12">
					<OrderWorks
						:can-add-services="canManageOrder"
						:can-manage-item="canManageOrder"
						@recalculate="recalculate"
					/>
					<OrderWaypoints
						:can-manage-waypoints="canManageOrder"
						@recalculate="recalculate"
					/>
				</div>

				<div v-if="!communicationIsOpen" class="col-xl-5 col-lg-12">
					<OrderEstimate
						v-if="!loading"
						:can-manage="canManageOrder"
						:moveTypes="dataSources.moveTypes"
						@recalculate="recalculate"
						:processing="processing_calc"
					/>
					<OrderCalculation
						ref="OrderCalculation"
						v-if="!loading"
						:can-manage="canManageOrder"
						:processing.sync="processing_calc"
					/>
				</div>
			</div>
		</div>
		<div class="col-12" v-if="canViewChangelog">
			<Changelog
				:changelog="changelog"
				:can-view-employee-card="canViewEmployeeCard"
				@refetch-changelog="refetchChangelog"
			/>
		</div>
	</div>
</template>

<script>
import CommunicationPanel from '@components/Order/TabOverview/CommunicationPanel';
import { BNav, BNavItem, VBScrollspy } from 'bootstrap-vue';
import { mapGetters } from 'vuex';
import Changelog from '../Changelog/Changelog';
import OrderEstimate from './TabOverview/Estimate'; // const OrderWorks = () => import(/* webpackChunkName: "OrderTabWorks" */ './TabOverview/Works');
import OrderWorks from './TabOverview/Works';

let order_id = document.getElementById('order_id').textContent;

const OverviewOrder = () =>
	import(/* webpackChunkName: "OverviewOrder" */ './TabOverview/Order');
const Communications = () =>
	import(
		/* webpackChunkName: "OrderCommunications" */ './TabOverview/Communications'
	);
const OrderClient = () =>
	import(/* webpackChunkName: "OrderClient" */ './TabOverview/Client');

const OrderWaypoints = () =>
	import(
		/* webpackChunkName: "OrderTabWaypoints" */ './TabOverview/Waypoints'
	);
const OrderCalculation = () =>
	import(
		/* webpackChunkName: "OrderTabOverviewCalculation" */ './TabOverview/Calculation'
	);
const OrderPanelNotes = () =>
	import(/* webpackChunkName: "OrderPanelNotes" */ './TabNotes/Notes');

export default {
	name: 'OrderOverview',
	components: {
		Communications,
		OrderEstimate,
		OverviewOrder,
		OrderClient,
		OrderWorks,
		OrderWaypoints,
		OrderCalculation,
		OrderPanelNotes,
		CommunicationPanel,
		BNav,
		BNavItem,
		Changelog,
	},
	data() {
		return {
			panelStatus: null,
			loading: true,
			processing_calc: false,
			notes_loaded: false, // lazy load
			showCPv1: true,
			showCPv2: true,
		};
	},
	computed: {
		firstStage() {
			if (
				this.session?.order?.status_id &&
				this.session?.order?.status_id == 1
			)
				return true;
			return null;
		},
		orderCommunications() {
			if (this.$store.state.order.forcePanelInterface)
				return this.$store.state.order.forcePanelInterface;
			if (this.firstStage) return 'collapsed';
			return 'expanded';
		},
		dataSources() {
			return {
				managers: window.order_managers,
				sources: this.$store.state.order.dataSources.sources,
				divisions: this.$store.state.order.dataSources.divisions,
				moveSizes: this.$store.state.order.dataSources.moveSizes,
				moveTypes: this.$store.state.order.dataSources.moveTypes,
			};
		},
		changelog() {
			return {
				loading: this.loading,
				...this.$store.state.order.changelog,
			};
		},
		notesRecords() {
			let records = this.notes
				.slice() // Хак, отвязываемся от объекта Vuex
				.filter((record) => {
					return record.is_pinned;
				})
				.map((item) => {
					item.type = 'note';
					return item;
				});

			// Если есть таски
			if (this.tasks.length) {
				let tasks = this.tasks.slice().map((item) => {
					item.type = 'task';
					return item;
				});

				records = records.concat(tasks);
			}

			return records;
		},
		...mapGetters({
			notes: 'order/notes',
			tasks: 'order/tasks',
			session: 'getSession',
		}),
		canManageOrder() {
			return this.$store.state.order.permissions.canManageOrder;
		},
		canViewChangelog() {
			return this.$store.state.order.permissions.canViewChangelog;
		},
		canViewEmployeeCard() {
			return this.$store.state.order.permissions.canViewEmployeeCard;
		},
		communicationIsOpen() {
			return this.orderCommunications === 'expanded';
		},
	},
	mounted() {
		Promise.all([
			this.getInfoStatuses(),
			this.$store.dispatch('getSession'),
		]).then(() => {
			this.loading = false;
		});

		this.getNotes();
	},
	methods: {
		// scrollIntoView(event) {
		//     event.preventDefault()
		//     const href = event.target.getAttribute('href')
		//     const el = href ? document.querySelector(href) : null
		//     if (el) {
		//         this.$refs.scrollspycontent.scrollTop = el.offsetTop
		//     }
		// },
		// Пушим что надо переобновить данные с хранилища
		bindReInitEstimate() {
			this.$refs.OrderCalculation.initData();
		},
		getInfoStatuses() {
			return this.$store
				.dispatch('order/fetchDatasources', {
					order_id,
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				});
			//
			// return axios
			//     .post('/orders/info-statuses', {
			//         order_id,
			//     })
			//     .then(resp => {
			//         if (resp.data.success === true) {
			//             this.dataSources.sources = resp.data.dataSources.sources;
			//             this.dataSources.divisions = resp.data.dataSources.divisions;
			//             this.dataSources.moveSizes = resp.data.dataSources.moveSizes;
			//             this.dataSources.moveTypes = resp.data.dataSources.moveTypes;
			//
			//             this.$store.dispatch('order/setStatusList', {
			//                 records: resp.data.status.records,
			//                 routes: resp.data.status.routes,
			//                 prev_status: resp.data.status.prev_status,
			//             });
			//         } else {
			//             App.Forms.simpleErrors(resp.data);
			//         }
			//     })
			//     .catch(error => {
			//         App.Forms.simpleErrors(error.response.data);
			//     });
		},
		getNotes() {
			axios
				.post('/orders/notes', {
					order_id,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store.dispatch(
							'order/updateNotes',
							resp.data.records
						);
						this.notes_loaded = true;
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				});
		},
		recalculate() {
			this.processing_calc = true;
			this.$refs.OrderCalculation.sendUpdate();
		},
		refetchChangelog(params) {
			this.$store.dispatch('order/refetchChangelog', params);
		},
	},
	directives: {
		'b-scrollspy': VBScrollspy,
	},
};
</script>

<style scoped>
.sticky-panel {
	position: sticky;
	top: 114px;
}
</style>

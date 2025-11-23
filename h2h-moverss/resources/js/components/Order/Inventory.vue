<template>
	<div class="row" v-if="!loading">
		<div class="col-lg-8 col-xl-8 order-lg-1 order-xl-1">
            <OrderTabInventoryNew
                v-if="v2"
                ref="inventory"
                :can-manage="canManage"
                :is_changed.sync="changed"
            />
			<OrderTabInventory
                v-if="!v2"
				ref="inventory"
				:can-manage="canManage"
				:is_changed.sync="changed"
			/>
			<Changelog
				v-if="canViewChangelog"
				:changelog="changelog"
				:can-view-employee-card="canViewEmployeeCard"
				@refetch-changelog="refetchChangelog"
			/>
		</div>

		<div class="col-lg-4 col-xl-4 order-lg-2 order-xl-3">
			<OrderTabSizing
				ref="sizing"
				:can-manage="canManage"
				:is_changed="changed"
				:component-id="2"
			/>
			<OrderTabExtras :can-manage="canManage" ref="extra" />
		</div>
	</div>
</template>

<script>
import Changelog from '@components/Changelog/Changelog.vue';

const OrderTabInventory = () =>
	import(
		/* webpackChunkName: "OrderTabInventory" */ './TabInventory/Inventory'
	);
const OrderTabInventoryNew = () =>
    import(
        /* webpackChunkName: "OrderTabInventory" */ './TabInventory/InventoryNew'
        );
const OrderTabSizing = () =>
	import(
		/* webpackChunkName: "OrderTabInventorySizing" */ './TabInventory/Sizing'
	);
const OrderTabExtras = () =>
	import(
		/* webpackChunkName: "OrderTabInventoryExtras" */ './TabInventory/Extras'
	);

export default {
	name: 'Inventory',
	components: {
		Changelog,
		OrderTabInventory,
        OrderTabInventoryNew,
		OrderTabSizing,
		OrderTabExtras,
	},
    props: {
        v2: {
            type: Boolean,
            default: false,
        },
    },
	data() {
		return {
			loading: true,
			changed: false,
		};
	},
	mounted() {
		this.$store.dispatch('getSession').then(() => {
			this.loading = false;
		});
	},
	computed: {
		changelog() {
			return {
				loading: this.loading,
				...this.$store.state.order.changelog,
			};
		},
		canManage() {
			return this.$store.state.order.permissions.canManageOrder;
		},
		canViewChangelog() {
			return this.$store.state.order.permissions.canViewChangelog;
		},
		canViewEmployeeCard() {
			return this.$store.state.order.permissions.canViewEmployeeCard;
		},
	},
	methods: {
		proxyExtrasModal(id = null) {
			this.$refs.extra.clickModal(id);
		},
		proxyToggleAuto() {
			this.$refs.sizing.toggleAuto();
		},
		refetchChangelog(params) {
			this.$store.dispatch('order/refetchChangelog', params);
		},
		beforeUnloadHandler(event) {
			event.preventDefault();
			event.returnValue = true;
		},
	},
	watch: {
		changed(newValue) {
            window.isInventoryChanged = newValue;

			if (newValue) {
				window.addEventListener(
					'beforeunload',
					this.beforeUnloadHandler
				);
			} else {
				window.removeEventListener(
					'beforeunload',
					this.beforeUnloadHandler
				);
			}
		},
	},
};
</script>

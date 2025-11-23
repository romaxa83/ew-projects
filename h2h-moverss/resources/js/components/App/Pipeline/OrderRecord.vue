<template>
	<li>
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
		<div class="card">
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap bg-white"
			>
				<div class="fs-xs mr-auto pr-3 text-muted">
					<a target="_blank" :href="'/orders/' + record.id"
						>Order #{{ record.id }}</a
					>
				</div>
				<div class="d-flex position-relative pr-2 fs-xs text-muted">
					{{ localDT(record.timestamp) }}
				</div>
			</div>
			<div class="card-body fs-sm py-2">
				<div class="d-flex">
					<div>{{ clientName }}</div>
				</div>
				<div class="mt-1 d-flex">
					<order-status-dropdown
						:order_id="record.id"
						:orderStatusId="orderStatusID"
						:statuses="statusesToDropdown"
						interface="pipeline"
						size="xs"
						@updating="updating"
						@updated="updated"
						:loading="loading"
					/>
					<div
						v-if="hasTasks"
						class="ml-auto cursor-pointer"
						style="font-size: 0.5rem"
						:style="{ color: tasksColor }"
						:title="tasksTitle"
					>
						<i class="fa fa-circle"></i>
					</div>
				</div>
			</div>
		</div>
	</li>
</template>

<script>
import formatDateTime from '@/filters/formatDateTime.filter';
import OrderStatusDropdown from '@components/Order/TabOverview/OrderStatusDropdown';

export default {
	name: 'OrderRecord',
	props: ['record'],
	computed: {
		tasksColor() {
			if (this.hasOverdueTasks) return '#ea1212';
			if (this.hasInworkTasks) return '#24cb59';
			//if()
			return '#b1b2b8';
		},
		tasksTitle() {
			if (this.hasOverdueTasks) return 'Has overdued tasks';
			if (this.hasTasks) return 'Has tasks';
			//if()
			return 'No tasks';
		},
		hasTasks() {
			if (this.hasOverdueTasks || this.hasInworkTasks) return true;
			return false;
		},
		hasOverdueTasks() {
			if (this.record.tasks_overdue.length) return true;
			return null;
		},
		hasInworkTasks() {
			if (this.record.tasks_inwork.length) return true;
			return null;
		},
		timezone() {
			return this.$store.state.ordersPipeline.timezone;
		},
		clientName() {
			if (this.record.client) {
				return this.record.client.name + ' ' + this.record.client.lname;
			}
			return '[No client]';
		},
		orderStatusID() {
			return this.record.status_id ? this.record.status_id : 1;
		},
		statuses() {
			return this.$store.state.ordersPipeline.statuses;
		},
		statusesToDropdown() {
			return {
				records: this.$store.state.ordersPipeline.statuses,
				routes: this.$store.state.ordersPipeline.statusRoutes,
			};
		},
	},
	data: () => ({
		loading: false,
	}),
	methods: {
		updating() {
			this.loading = true;
		},
		updated() {
			this.loading = false;
		},
		localDT(timestamp) {
			return formatDateTime(timestamp, this.timezone);
		},
	},
	components: {
		OrderStatusDropdown,
	},
};
</script>

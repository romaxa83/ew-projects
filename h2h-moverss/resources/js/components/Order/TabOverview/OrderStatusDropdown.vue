<template>
	<div class="d-flex">
		<b-dropdown variant="white" :size="size" class="rounded">
			<template #button-content>
				<template v-if="loading">
					<span
						class="spinner-border spinner-border-sm"
						role="status"
						aria-hidden="true"
					></span>
					Loading...
				</template>
				<template v-if="!loading && currentStatus">
					<i
						class="fas fa-circle pr-2"
						:style="{
							color: currentStatus ? currentStatus.color : '#FFF',
						}"
					></i>
					{{ currentStatus.title }}
					<!--                    <i v-if="!loading" class="ni ni-energy mr-1"></i> {{ currentStatus.title }}-->
				</template>
			</template>
			<template v-if="!loading && statusRoutes">
				<b-dropdown-item-button
					v-for="(status_id, k) in statusRoutes"
					@click="changeStatus(status_id)"
					:key="k"
				>
					<i
						class="fas fa-circle pr-2"
						:style="{ color: statuses.records[status_id].color }"
					></i>
					{{ statuses.records[status_id].title }}
				</b-dropdown-item-button>
			</template>
			<!--        <b-dropdown-item-button v-for="(value, key) in mode.list" @click="toggleDropdown(key)" :key="key">{{ value.title }}</b-dropdown-item-button>-->
			<!--            <b-dropdown-item-button>Email</b-dropdown-item-button>-->
			<!--            <b-dropdown-item-button>Task</b-dropdown-item-button>-->
		</b-dropdown>
		<button
			v-if="interface == 'communicationPanel'"
			@click="changeStatus(statuses.prev_status, 1)"
			class="btn btn-danger ml-1"
			title="Rollback"
			:disabled="!statuses.prev_status"
		>
			<i class="fas fa-undo-alt"></i>
		</button>
	</div>
</template>

<script>
import {
	BDropdown,
	BLink,
	BDropdownItem,
	BDropdownItemButton,
} from 'bootstrap-vue';
import { axiosPromise } from '@/helpers/axiosPromise';

export default {
	name: 'OrderStatusDropdown',
	data: () => ({
		// loading: true,
	}),
	props: [
		'order_id',
		'orderStatusId',
		'interface',
		'loading',
		'statuses',
		'size',
	],
	computed: {
		currentStatus() {
			if (this.statuses.records)
				return this.statuses.records[this.orderStatusId];
			return null;
		},
		statusRoutes() {
			return this.statuses.routes[this.orderStatusId] ?? [];
		},
	},
	methods: {
		changeStatus(status_id, is_roll_back = 0) {
			this.$emit('updating');
			axiosPromise(
				axios.post(`/orders/${this.order_id}/order/set-status`, {
					order_id: this.order_id,
					old_status: this.currentStatus.id,
					status_id,
					is_roll_back,
				})
			)
				.then((data) => {
					if (this.interface === 'communicationPanel') {
						this.$store.commit(
							'order/setStatusPrev',
							+data.prev_status
						);
						this.$store.commit('setOrderStatus', status_id);

						if (data.hasOwnProperty('reload_works')) {
							this.$store.dispatch('initSession', {
								id: this.order_id,
							});
						}
					} else if (this.interface === 'pipeline') {
						this.$store.commit('ordersPipeline/updateOrderStatus', {
							orderID: this.order_id,
							oldStatusID: this.currentStatus.id,
							newStatusID: status_id,
						});
					}

					this.$emit('updated');
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});

			return false;
		},
	},
	components: {
		BDropdown,
		BDropdownItem,
		BDropdownItemButton,
		BLink,
	},
};
</script>

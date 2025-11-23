<template>
	<li class="d-flex inbound-block">
		<div
			class="card card-zoom-hover"
			:class="{ 'border border-primary': record.selected }"
		>
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap"
			>
				<div class="mr-1"><i class="fas fa-phone"></i></div>
				<div class="fs-xs pr-3 text-muted l-h-n">
					<b>Call {{ callLine }}</b>
				</div>
				<div
					class="ml-auto d-flex position-relative pr-2 fs-xs text-muted"
				>
					{{ datetime }}
				</div>
			</div>
			<div class="card-body fs-sm py-2">
				<template v-if="department !== 'sales'">
					<div>
						Client name: {{ clientRequest.client_name || '---' }}
					</div>
					<div class="mt-1">
						Call back at: {{ clientRequest.call_back_at || '---' }}
					</div>
				</template>
				<template v-else>
					<div>
						Client name: {{ clientRequest.client_name || '---' }}
					</div>
					<div class="mt-1">
						Pickup location:
						{{ clientRequest.pickup_location || '---' }}
					</div>
					<div class="mt-1">
						Pickup stairs: {{ clientRequest.pickup_stairs || '---' }}
					</div>
					<div class="mt-1">
						Delivery location:
						{{ clientRequest.delivery_location || '---' }}
					</div>
					<div class="mt-1">
						Delivery stairs:
						{{ clientRequest.delivery_stairs || '---' }}
					</div>
				</template>
			</div>
		</div>
	</li>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
	name: 'Concierge',
	props: ['record', 'datetime'],
	computed: {
		...mapGetters({
			orderID: 'getOrderId',
		}),
		item() {
			return this.record.item;
		},
		clientRequest() {
			return this.record.item.client_request || {};
		},
		department() {
			return this.clientRequest.department_type || '';
		},
		orderID() {
			if (this.record.orderID) return this.record.orderID;
			return null;
		},
		callLine() {
			if (this.department === 'sales') {
				return 'to: Sales';
			} else if (this.department === 'claims') {
				return 'to: Claims';
			} else if (this.department === 'customer_service') {
				return 'to: Customer service';
			}
			return '';
		},
		duration() {
			return moment.utc(+this.item.duration * 1000).format('mm:ss');
		},
	},
};
</script>

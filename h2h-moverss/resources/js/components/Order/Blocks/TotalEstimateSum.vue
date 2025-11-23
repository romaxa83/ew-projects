<template>
	<span v-if="calculatedTotal" class="badge badge-success ml-1 fw-500">{{
		totalValue
	}}</span>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
	name: 'TotalEstimateSum',
	computed: {
		totalValue() {
			if (this.calculatedTotal && this.calculatedTotal.value)
				return this.calculatedTotal.value;
			return '';
		},
		...mapGetters({
			calculatedTotal: 'order/getCalculatedTotal',
		}),
	},
};
</script>

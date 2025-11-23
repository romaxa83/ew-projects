<template>
	<div>
		<ul class="pipeline-list">
			<order-record
				v-for="(record, key) of sortedGroupRecords"
				:record="record"
				:key="key"
			/>
		</ul>
	</div>
</template>

<script>
import OrderRecord from '@components/App/Pipeline/OrderRecord';

export default {
	name: 'OrderCol',
	props: ['group'],
	computed: {
		sortDirection() {
			return this.$store.state.ordersPipeline.filters.orderByCreated;
		},
		ordersRecords() {
			return this.$store.state.ordersPipeline.orders;
		},
		sortedGroupRecords() {
			if (this.ordersRecords[this.group.id])
				return this.ordersRecords[this.group.id].records
					.slice()
					.sort(this.compare);
			return [];
			//     return this.records.slice().sort((a, b) => +b.timestamp - a.timestamp)
		},
	},
	methods: {
		compare(a, b) {
			if (this.sortDirection == 'desc') return +b.timestamp - a.timestamp;
			else if (this.sortDirection == 'asc')
				return +a.timestamp - b.timestamp;
		},
	},
	components: {
		OrderRecord,
	},
};
</script>

<style lang="scss">
.pipeline-list {
	list-style-type: none;
	position: relative;
	padding: 0;

	& > li {
		margin: 20px 0;
	}
}
</style>

<template>
	<li class="fs-sm text-center">
		{{ datetime }}.
		<a class="fs-md fw-500" :href="'/orders/' + item.id" target="_blank"
			>Order #{{ item.id }}</a
		>
		created by {{ author }}
		<!--        <span class="badge badge-secondary">{{ fromValue }}</span> to-->
		<!--        <span class="badge bg-white border border-secondary text-secondary">{{ toValue }}</span>-->
	</li>
</template>

<script>
export default {
	name: 'OrderCreateEvent',
	props: ['record', 'datetime'],
	computed: {
		// fromValue() {
		//     return this.record.update.from.title;
		// },
		// toValue() {
		//     return this.record.update.to.title;
		// },
		item() {
			return this.record.item;
		},
		// attribute() {
		//     return this.item.type[0].toUpperCase() + this.item.type.slice(1);
		//     // return this.item.type;
		// },
		author() {
			if (this.item.manager?.employee)
				return (
					this.item.manager?.employee?.name +
					' ' +
					this.item.manager?.employee?.l_name
				);
			return this.item.manager?.name;
		},
	},
};
</script>

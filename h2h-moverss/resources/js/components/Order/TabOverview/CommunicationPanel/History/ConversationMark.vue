<template>
	<li class="fs-sm text-center">
		{{ datetime }}. {{ author }} marked conversation as
		<b>{{ markTypeText }}</b>
		<!--        <span class="badge badge-secondary">{{ fromValue }}</span> to-->
		<!--        <span class="badge bg-white border border-secondary text-secondary">{{ toValue }}</span>-->
	</li>
</template>

<script>
export default {
	name: 'ConversationMark',
	props: ['record', 'datetime'],
	computed: {
		// fromValue() {
		//     return this.record.update.from.title;
		// },
		// toValue() {
		//     return this.record.update.to.title;
		// },
		markTypeText() {
			if (this.item.type == 'read') return 'answered';
		},
		item() {
			return this.record.item;
		},
		// attribute() {
		//     return this.item.type[0].toUpperCase() + this.item.type.slice(1);
		//     // return this.item.type;
		// },
		author() {
			if (this.item.user?.employee)
				return (
					this.item.user?.employee?.name +
					' ' +
					this.item.user?.employee?.l_name
				);
			return this.item.user?.name;
		},
	},
};
</script>

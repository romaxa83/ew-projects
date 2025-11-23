<template>
	<li v-if="fromValue && toValue" class="fs-sm text-center">
		{{ datetime }}. {{ author }} changed "{{ attribute }}" from
		<span class="badge badge-secondary">{{ fromValue }}</span> to
		<span class="badge bg-white border border-secondary text-secondary">{{
			toValue
		}}</span>
	</li>
</template>

<script>
export default {
	name: 'AttributeChange',
	props: ['record', 'datetime'],
	computed: {
		fromValue() {
			return this.record.update?.from.title ?? '';
		},
		toValue() {
			return this.record.update?.to.title ?? '';
		},
		item() {
			return this.record.item;
		},
		attribute() {
			return this.item.type[0].toUpperCase() + this.item.type.slice(1);
			// return this.item.type;
		},
		author() {
			if (this.item.author?.employee)
				return (
					this.item.author?.employee?.name +
					' ' +
					this.item.author?.employee?.l_name
				);
			return this.item.author?.name;
		},
	},
};
</script>

<template>
	<td>{{ formatted }}</td>
</template>

<script>
export default {
	name: 'DetailField',
	props: ['value'],
	computed: {
		formatted() {
			let value = this.value;
			if (typeof value === 'string') {
				if (value.length === 0) {
					return '\u{2014}';
				}
				value = value.replaceAll('_', ' ');
				return value.charAt(0).toUpperCase() + value.slice(1);
			}
			return String(value);
		},
	},
};
</script>

<style scoped>
td {
	font-weight: 500;
}
</style>

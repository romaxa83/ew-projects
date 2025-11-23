<template>
	<td>
		<pre v-if="withPre">{{ formatted }}</pre>
		<div v-else>{{ formatted }}</div>
	</td>
</template>

<script>
export default {
	name: 'DetailValue',
	props: ['value'],
	computed: {
		withPre() {
			return (
				typeof this.value === 'object' &&
				(this.formatted.startsWith('{') ||
					this.formatted.startsWith('['))
			);
		},
		formatted() {
			const value = this.value;
			if (
				(typeof value === 'string' && value.length === 0) ||
				value === null ||
				value === undefined
			) {
				return '\u{2014}';
			}
			if (typeof value === 'object' && value !== null) {
				return JSON.stringify(value, null, 2);
			}
			return String(value);
		},
	},
};
</script>

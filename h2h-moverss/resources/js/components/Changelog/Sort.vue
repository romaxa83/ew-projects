<template>
	<div class="form-group">
		<div
			class="input-group flex-nowrap has-tooltip"
			data-toggle="tooltip"
			data-placement="top"
			data-animation="false"
			title="Sort by"
		>
			<div class="input-group-prepend">
				<span class="input-group-text">
					<i class="fal fa-sort-alt fs-xl"></i>
				</span>
			</div>
			<select
				v-model="sortType"
				class="form-control"
				aria-label="Sort by"
			>
				<option
					v-for="option in options"
					:key="option.value"
					:value="option.value"
				>
					{{ option.label }}
				</option>
			</select>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Sort',
	props: ['value'],
	data() {
		return {
			selected: this.value,
			options: [
				{ value: 'desc', label: 'From new to old' },
				{ value: 'asc', label: 'From old to new' },
			],
		};
	},
	computed: {
		sortType: {
			get() {
				return this.selected;
			},
			set(value) {
				this.selected = value;
				this.$emit('sort-change', value);
			},
		},
	},
};
</script>

<style scoped>
td {
	font-weight: 500;
}
</style>

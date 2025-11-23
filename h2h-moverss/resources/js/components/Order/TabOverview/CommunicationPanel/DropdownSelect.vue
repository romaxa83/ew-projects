<template>
	<b-dropdown :text="buttonText" class="m-md-2">
		<b-dropdown-item-button
			v-for="(item, index) in items"
			@click="select(index)"
			:key="index"
			>{{ item.text }}</b-dropdown-item-button
		>
		<!--            <b-dropdown-item-button>Email</b-dropdown-item-button>-->
		<!--            <b-dropdown-item-button>Task</b-dropdown-item-button>-->
	</b-dropdown>
</template>

<script>
import { BDropdown, BDropdownItemButton } from 'bootstrap-vue';

export default {
	name: 'DropdownSelect',
	props: ['value', 'items'],
	data: () => ({
		current: 0,
	}),
	computed: {
		buttonText() {
			if (this.items && this.items.length)
				return this.items[this.current].value;
			return '';
		},
	},
	methods: {
		select(index) {
			this.current = index;
			this.$emit('input', index);
		},
	},
	components: {
		BDropdown,
		BDropdownItemButton,
	},
};
</script>

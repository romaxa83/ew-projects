<template>
	<b-dropdown
		:dropup="dropup"
		:text="buttonText"
		variant="primary"
		class="m-md-2 rounded"
	>
		<b-dropdown-item-button
			v-for="(value, key) in mode.list"
			@click="toggleDropdown(key)"
			:key="key"
			>{{ value.title }}</b-dropdown-item-button
		>
		<!--            <b-dropdown-item-button>Email</b-dropdown-item-button>-->
		<!--            <b-dropdown-item-button>Task</b-dropdown-item-button>-->
	</b-dropdown>
</template>

<script>
import {
	BDropdown,
	BLink,
	BDropdownItem,
	BDropdownItemButton,
	BDropdownDivider,
	BDropdownText,
	BButton,
} from 'bootstrap-vue';

export default {
	name: 'InputInterfaceDropdown',
	props: ['mode'],
	computed: {
		dropup() {
			if (this.mode.current == 'email') return false;
			return true;
		},
		buttonText() {
			return this.mode.list[this.mode.current].title;
		},
	},
	methods: {
		toggleDropdown(key) {
			this.mode.current = key;
			// this.$emit('toggle');
		},
	},
	components: {
		BDropdown,
		BDropdownItem,
		BButton,
		BDropdownItemButton,
		BLink,
	},
};
</script>

<style>
.dropmenu-z {
	z-index: 8;
}
</style>

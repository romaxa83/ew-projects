<template>
	<button
		type="button"
		class="button"
		:class="{ active: isActive }"
		@click="changeTab"
	>
		<span>{{ label }}</span>
	</button>
</template>

<script>
export default {
	name: 'TabButton',
	props: {
		label: {
			type: String,
			required: true,
		},
		tabsValue: {
			type: String,
			required: true,
		},
		value: {
			type: String,
			required: true,
		},
	},
	methods: {
		changeTab() {
			this.$emit('change-tab', this.value);
		},
	},
	computed: {
		isActive() {
			return this.tabsValue === this.value;
		},
	},
};
</script>

<style scoped lang="scss">
.button {
	flex-grow: 1;
	background: none;
	border-width: 0;
	margin: 0;
	padding: 0;
	font-weight: 500;
	min-height: 45px;
	display: flex;
	justify-content: center;
	align-items: center;
	position: relative;
	border-bottom: 1px solid #0000001a;
	color: #666666;
	transition: color 0.2s ease, background-color 0.2s ease;

	&:hover {
		background-color: #1111110a;
	}

	&:active {
		background-color: #1111;
	}

	&:focus-visible {
		box-shadow: inset 0 0 0 1px #4679cc;
	}

	&::after {
		content: '';
		position: absolute;
		bottom: -1px;
		left: 50%;
		width: 56px;
		margin-left: -28px;
		pointer-events: none;
		background-color: transparent;
		transition: inherit;
		height: 1px;
	}

	&.active {
		color: #333;

		&::after {
			background-color: #4679cc;
		}
	}
}
</style>

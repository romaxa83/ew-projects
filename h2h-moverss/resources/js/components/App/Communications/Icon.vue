<template>
	<svg viewBox="0 0 12 12">
		<use :href="getIconPath(icon)" />
	</svg>
</template>

<script>
export default {
	name: 'Icon',
	data() {
		return {
			svgSpritePath: '/images/communications-sprite.svg?cache=3',
		};
	},
	props: {
		icon: {
			type: String,
			required: true,
		},
	},
	methods: {
		getIconPath(icon) {
			return `${this.svgSpritePath}#${icon}`;
		},
	},
};
</script>

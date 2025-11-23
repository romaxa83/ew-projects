<template>
	<div :title="title" class="stat">
		<div class="icon" :data-status="status">
			<Icon :icon="status" />
		</div>
		<div class="fs-md text">{{ qty }}</div>
	</div>
</template>

<script>
import Icon from '../Icon.vue';

export default {
	name: 'Stat',
	components: { Icon },
	props: {
		stat: {
			type: Object,
			required: true,
		},
	},
	computed: {
		title() {
			return this.stat.label;
		},
		status() {
			return this.stat.status;
		},
		qty() {
			return this.stat.qty;
		},
	},
};
</script>

<style lang="scss" scoped>
.stat {
	width: 33.3%;
	flex-basis: 33.3%;
	flex-shrink: 0;
	text-align: center;
}

.icon {
	width: 24px;
	height: 24px;
	border-radius: 4px;
	background: var(--icon-bg);
	margin: 0 auto 2px;
	display: flex;
	align-items: center;
	justify-content: center;

	&[data-status='on-call'] {
		--icon-bg: #cfe2ff;
	}

	&[data-status='online'] {
		--icon-bg: #d1e7dd;
	}

	&[data-status='offline'] {
		--icon-bg: #f8d7da;
	}

	svg {
		width: 12px;
		height: 12px;
	}
}

.text {
	color: #495057;
}
</style>

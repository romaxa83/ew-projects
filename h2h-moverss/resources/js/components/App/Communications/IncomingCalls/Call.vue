<template>
	<div class="call border-bottom">
		<div class="caller">
			<div
				class="name fs-xs"
				:class="{ 'known-caller': !!record.caller.id }"
			>
				{{ record.caller.name }}
			</div>
			<div class="phone fs-nano text-body">{{ record.caller.phone }}</div>
		</div>
		<div class="waiting">
			<div class="label fw-500">Waiting for</div>
			<Duration
				class="fs-xs duration"
				:start-timestamp-in-seconds="record.startTimestampInSeconds"
			/>
		</div>
	</div>
</template>

<script>
import Duration from '../Duration.vue';

export default {
	name: 'Call',
	props: {
		record: {
			type: Object,
			required: true,
		},
	},
	components: {
		Duration,
	},
};
</script>

<style lang="scss" scoped>
.call {
	padding: 12px 0;
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	color: #868e96;
}

.known-caller {
	color: #5a87d2;
}

.phone {
	line-height: 1em;
}

.waiting {
	text-align: right;

	.label {
		font-size: 10.6px;
		line-height: 12.42px;
	}
}
</style>

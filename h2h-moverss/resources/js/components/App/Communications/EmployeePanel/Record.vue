<template>
	<div class="record border-bottom">
		<div class="name fw-500 fs-md">{{ name }}</div>
		<div class="info">
			<div class="icon">
				<Icon :icon="status" />
			</div>
			<div v-if="hasCallClient" class="status-label">
				<div class="fs-nano on-call-desc">On call with</div>
				<div class="fs-xs responder">{{ callClient }}</div>
			</div>
			<div v-else-if="hasCallPhoneNumber" class="status-label">
				<div class="fs-nano on-call-desc">On call with</div>
				<div class="fs-xs">{{ callPhoneNumber | formatPhone }}</div>
			</div>
			<div v-else class="status-label">
				{{ statusLabel }}
			</div>
			<Duration
				v-if="hasCallDuration"
				class="fs-xs duration"
				:start-timestamp-in-seconds="callStartedAt"
			/>
		</div>
	</div>
</template>

<script>
import formatPhone from '@/filters/formatPhone.filter';
import Duration from '../Duration.vue';
import Icon from '../Icon.vue';

export default {
	name: 'Record',
	components: { Icon, Duration },
	filters: {
		formatPhone,
	},
	props: {
		record: {
			type: Object,
			required: true,
		},
	},
	computed: {
		name() {
			return this.record.name;
		},
		status() {
			return this.record.status;
		},
		hasCallPhoneNumber() {
			return !!this.record.callDetails?.number;
		},
		callPhoneNumber() {
			return this.record.callDetails.number;
		},
		hasCallClient() {
			return !!this.record.callDetails?.client;
		},
		callClient() {
			return this.record.callDetails.client.name;
		},
		hasCallDuration() {
			return !!this.record.callDetails?.startedAt;
		},
		callStartedAt() {
			return this.record.callDetails.startedAt;
		},
		statusLabel() {
			return this.record.statusLabel;
		},
	},
};
</script>

<style lang="scss" scoped>
.record {
	padding: 14px 16px;
}

.name {
	color: #5a87d2;
	margin-bottom: 12px;
	padding: 0 0 0 2px;
	line-height: 1;
}

.info {
	display: flex;
	align-items: safe center;
	gap: 8px;
}

.icon {
	width: 12px;
	height: 12px;
	line-height: 12px;
	position: relative;
	flex-shrink: 0;
	margin-bottom: 2px;

	svg {
		width: 100%;
		height: 100%;
	}
}

.status-label {
	flex-grow: 1;
}

.on-call-desc {
	line-height: 1;
}

.responder {
	color: #5a87d2;
}

.duration {
	flex-shrink: 0;
	color: #868e96;
}
</style>

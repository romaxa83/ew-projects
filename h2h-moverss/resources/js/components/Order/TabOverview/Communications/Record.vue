<template>
	<li :class="{ 'mb-2': section === 'short', 'mt-2': section === 'short' }">
		<button
			class="btn btn-sm btn-default btn-icon activity-timeline-icon rounded-circle js-waves-off"
		>
			<i
				:class="
					'fas ' +
					(v.section === 'gmail' ? 'fa-mail-bulk' : 'fa-envelope')
				"
				:title="v.section"
			></i>
		</button>
		<div class="card" v-if="v.section === 'messages'">
			<div
				v-if="section === 'short'"
				class="card-header bg-white py-2 pr-2 d-flex align-items-center flex-wrap"
				:class="{ blink: isRecentlyAdded }"
			>
				<div class="mr-auto">{{ v.miscs.text }}.</div>
				<div class="d-flex position-relative pr-2 fs-xs text-muted">
					{{ v.created_at | formatDate }}
				</div>
			</div>
			<template v-else>
				<div
					class="card-header bg-white py-2 pr-2 d-flex align-items-center flex-wrap"
				>
					<div class="fs-xs mr-auto pr-3">
						Email sent to <b>{{ v.miscs.to }}</b>
					</div>
					<div class="d-flex position-relative pr-2 fs-xs text-muted">
						{{ v.created_at | formatDate }} by
						{{ v.user_id | managerName }}
					</div>
				</div>
				<div class="card-body fs-xs py-2">
					{{ v.miscs.text }}.
					<span v-html="mailEvents"></span>
				</div>
			</template>
		</div>
		<div class="card" v-else-if="v.section === 'gmail'">
			<div
				v-if="section === 'short'"
				class="card-header bg-white py-2 pr-2 d-flex align-items-center flex-wrap"
				:class="{ blink: isRecentlyAdded }"
			>
				Subject: {{ v.subject ? v.subject : 'none' }}
				<div
					class="d-flex position-relative ml-auto pr-2 fs-xs text-muted"
				>
					{{ v.created_at | formatDate }}
				</div>
			</div>
			<template v-else>
				<div
					class="card-header bg-white py-2 pr-2 d-flex align-items-center flex-wrap"
				>
					<div class="fs-xs mr-auto">
						<span v-if="v.miscs.from"
							><i class="fas fa-arrow-right"></i> From
							{{ v.miscs.from.email }}</span
						>
						<span v-else-if="v.miscs.to"
							><i class="fas fa-arrow-left"></i> To
							{{ v.miscs.to.email }}</span
						>
					</div>
					<div class="d-flex position-relative pr-2 fs-xs text-muted">
						{{ v.created_at | formatDate }} by
						{{ v.user_id | managerName }}
					</div>
				</div>
				<div class="card-body fs-xs py-2">
					Subject: {{ v.subject ? v.subject : 'none' }}
				</div>
			</template>
		</div>
	</li>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';

export default {
	name: 'CommunicationsRecord',
	filters: {
		formatDate,
		managerName,
	},
	props: {
		now: {
			type: Object,
			required: true,
		},
		section: {
			type: String,
			required: true,
		},
		v: {
			type: Object,
			required: true,
		},
	},
	computed: {
		isRecentlyAdded() {
			let created_at = moment
				.utc(this.v.created_at, 'YYYY-MM-DD HH:mm:ss')
				.local()
				.add(10, 'seconds');
			return created_at.isSameOrAfter(this.now);
		},
		mailEvents() {
			let statuses = '';

			Object.keys(this.v.miscs.events).forEach((k) => {
				if (k === 'open') {
					statuses +=
						'<span class="badge badge-info">Email viewed</span> ';
				} else if (k === 'click') {
					statuses +=
						'<span class="badge badge-success">Client opened confirmation page</span> ';
				} else if (k === 'unsub') {
					statuses +=
						'<span class="badge badge-danger">Unsubscribed</span> ';
				} else if (k === 'spam') {
					statuses +=
						'<span class="badge badge-danger">Marked as SPAM</span> ';
				} else if (k === 'blocked') {
					statuses +=
						'<span class="badge badge-danger">Blocked by mail service</span> ';
				} else if (k === 'bounce') {
					statuses +=
						'<span class="badge badge-danger">Delivery failed</span> ';
				}
			});

			return statuses;
		},
	},
};
</script>

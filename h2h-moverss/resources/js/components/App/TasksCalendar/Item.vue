<template>
	<div
		class="item border cursor-pointer"
		:title="`#${record.id}`"
		@click="viewTask(record.id)"
	>
		<div class="d-flex justify-content-between">
			<div
				class="ext-id"
				v-if="
					record.miscs.relation &&
					record.miscs.relation.type &&
					record.miscs.relation.type === 'order'
				"
			>
				<span class="text-capitalize">
					{{ record.miscs.relation.type }}:
					<a
						:href="record.miscs.href"
						v-text="`#${record.miscs.relation.id}`"
					></a>
				</span>
			</div>
			<span v-if="record.client" class="client-name fs-xs">
				{{ record.client.name + ' ' + record.client.lname }}
				<span v-if="record.client.value.type === 'email'">
					(${record.client.value.value})
				</span>
				<span v-else-if="record.client.value.type === 'phone'">
					{{ record.client.value.value | formatPhone }}
				</span>
			</span>
		</div>
		<div class="text" v-text="record.title"></div>

		<div class="date">
			<span v-text="record.fullDueDate"></span>,
			<span v-show="record.executor_id !== record.user_id">
				from <span class="user" v-text="author"></span>
			</span>
			to <span class="user" v-text="executor"></span>
		</div>

		<div class="d-flex mt-1">
			<div class="type">
				<span
					v-if="record.type_id"
					class="mr-3"
					:style="{ color: types[record.type_id].color }"
				>
					<i class="mr-2" :class="types[record.type_id].icon"></i>
					{{ types[record.type_id].title }}
				</span>
			</div>
			<div class="status-inline">
				<span class="badge" :class="statusBadgeClass">{{
					statuses[record.status_id].title
				}}</span>
			</div>
		</div>
	</div>
</template>

<script>
import formatPhone from '@/filters/formatPhone.filter';
import { mapGetters } from 'vuex';

export default {
	name: 'AppTasksCalendarItem',
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
		author() {
			return this.users[this.record.user_id].name;
		},
		executor() {
			return this.users[this.record.executor_id].name;
		},
		statusBadgeClass() {
			let cssClass =
				'badge-' + this.statuses[this.record.status_id].class;
			return {
				[cssClass]: true,
			};
		},
		type() {},
		...mapGetters({
			users: 'appTasks/users',
			statuses: 'appTasks/statuses',
			types: 'appTasks/types',
		}),
	},
	methods: {
		viewTask(id) {
			this.$root.$refs.tasks.openViewModal({ id });
		},
	},
};
</script>

<style lang="scss" scoped>
.item {
	margin-bottom: 6px;
	padding: 5px 10px;

	.user,
	.type {
		font-weight: bold;
	}

	.ext-id {
		font-size: 0.9em;
	}

	.client-name {
		font-size: 1.1em;
	}

	.date {
		font-size: 0.9em;
	}

	.text {
		width: 95%;
		display: inline-block;
		white-space: nowrap;
		overflow: hidden !important;
		text-overflow: ellipsis;
	}
}
</style>

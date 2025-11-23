<template>
	<div
		:style="computedStyle"
		class="panel-tag mb-1 cursor-pointer"
		@click="openRecord"
		:title="'Task #' + record.id"
	>
		<span class="hide-on-closed"
			><i :class="icon" class="fas"></i> {{ record.title }}</span
		>
		<div v-if="record.due_date" class="task-dates">
			<i v-text="time"></i>
			<strong class="margin">
				<span class="show-on-closed">{{ shortDueDate }}</span>
				<div class="hide-on-closed">
					{{ record.fullDueDate }}
					<span class="badge" :class="statusBadgeClass">{{
						statuses[record.status_id].title
					}}</span>
				</div>
			</strong>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
	name: 'AppTasksItem',
	props: {
		index: {
			type: Number,
			required: true,
		},
		record: {
			type: Object,
			required: true,
		},
	},
	computed: {
		computedStyle() {
			let borderColor = '#4dd7fa',
				backgroundColor = '#fff',
				borderWidth = '3px',
				dt = this.record.dueDate,
				now = moment();

			if (this.status_id === 2) {
				// Отклоненная
				borderColor = '#f7f6f6';
				backgroundColor = '#aaa';
			} else {
				if (dt < now) {
					// Просроченная
					borderColor = '#ff3823';
					backgroundColor = '#ffc3ae';
					borderWidth = '5px';
				} else if (dt.isSame(now, 'day')) {
					// на сегодня
					borderColor = '#ffd783';

					if (now.clone().add(30, 'minutes') > dt) {
						// На сегодня осталось менее 30 минут
						borderColor = '#ff3823';
						backgroundColor = '#fefac0';
					}
				}
			}

			return {
				'border-color': borderColor,
				'background-color': backgroundColor,
				'border-left-width': borderWidth,
			};
		},
		icon() {
			return {
				'fa-user': this.record.author.id === this.record.executor.id,
				'fa-external-link-square-alt': !this.isIFullHolder,
			};
		},
		isIFullHolder() {
			return this.record.author.id === this.record.executor.id;
		},
		shortDueDate() {
			return this.record.dueDate.format('MM/DD');
		},
		statusBadgeClass() {
			let cssClass =
				'badge-' + this.statuses[this.record.status_id].class;
			return {
				[cssClass]: true,
			};
		},
		time() {
			return this.record.dueDate.format('h:mm A');
		},
		...mapGetters({
			statuses: 'appTasks/statuses',
		}),
	},
	methods: {
		openRecord() {
			this.$emit('openRecord', {
				index: this.index,
				id: this.record.id,
			});
		},
	},
};
</script>

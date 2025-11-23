<template>
	<div class="wrapper">
		<div class="item" :data-action="action">
			<div class="action-icon" aria-hidden="true">
				<svg width="10" height="10" viewBox="0 0 10 10">
					<use :href="actionIcon" />
				</svg>
			</div>
			<div class="body">
				<div class="action-name fw-500">
					{{ actionName }}
				</div>
				<div class="fs-lg">
					<span class="entity fw-500">
						{{ entity | normalizeEntity }}
					</span>
					{{ wasDid }}
					<span v-if="byClient">
						by client
						<strong class="fw-500 client">
							{{ clientName }}
						</strong>
					</span>
					<span v-else-if="byEmployee">
						by&nbsp;
						<a
							v-if="canViewEmployeeCard"
							:href="employeeLink"
							class="fw-500"
						>
							{{ employeeName }}
						</a>
						<strong class="fw-500" v-else>{{
							employeeName
						}}</strong>
					</span>
				</div>
				<div class="details">
					<div
						class="detail fw-500"
						v-for="(detail, i) in details"
						:key="`:${i}-${detail.field}:`"
					>
						<span class="mr-1 text-capitalize">
							{{ detail.field | fieldName }}:
						</span>
						<span v-if="showOldValue(detail.old)">
							{{ detail.old | fieldValue }}
						</span>
						<svg
							v-if="showLineIcon(detail.old, detail.new)"
							width="16"
							height="16"
							viewBox="0 0 16 16"
						>
							<use :href="linesIcon" />
						</svg>
						<span v-if="showNewValue(detail.new)">
							{{ detail.new | fieldValue }}
						</span>
					</div>
				</div>
			</div>
			<div class="date">
				{{ date }}
			</div>
		</div>
	</div>
</template>

<script>
import { ChangelogActions } from '@/services/changelog';
import { DateService, dateService } from '@/services/date';
import { Fragment } from 'vue-fragment';
import ItemDetails from './Details';

export default {
	name: 'Item',
	props: {
		item: {
			type: Object,
			required: true,
		},
		canViewEmployeeCard: {
			type: Boolean,
			required: true,
		},
		svgSpritePath: {
			type: String,
			required: true,
		},
	},
	filters: {
		normalizeEntity(entity) {
			return String(entity)
				.replace(/\(\)/g, '')
				.replace(/\s\s+/g, ' ')
				.replace(/\s,/g, ',');
		},
		fieldName(name) {
			const key = String(name);
			switch (key) {
				case 'division':
					return 'Branch';
				default:
					return formatKey(key);
			}

			function formatKey(key) {
				return key.replace(/_/g, ' ');
			}
		},
		fieldValue(value) {
			if (
				(typeof value === 'string' && value.length === 0) ||
				value === null ||
				value === undefined
			) {
				return 'None';
			}
			if (typeof value === 'object' && value !== null) {
				return JSON.stringify(value, null, 2);
			}
			const val = normalize(String(value));
			return val.charAt(0).toUpperCase() + val.slice(1);

			function normalize(val) {
				switch (val) {
					case 'true':
						return 'on';
					case 'false':
						return 'off';
					default:
						return val.replace(/\s,/g, ',');
				}
			}
		},
	},
	computed: {
		byClient() {
			return !!(this.item.is_client_activity && this.item.client);
		},
		clientName() {
			if (this.byClient) {
				const { first_name = '', last_name = '' } = this.item.client;
				if (first_name || last_name) {
					return `${first_name} ${last_name}`;
				} else {
					return 'Unknown';
				}
			}
			return false;
		},
		byEmployee() {
			return !!this.item.user?.employee;
		},
		employeeLink() {
			if (this.byEmployee) {
				const id = this.item.user.employee.id;
				return id ? `/company/employees/${id}` : '';
			}
			return '';
		},
		employeeName() {
			if (this.byEmployee) {
				const { first_name = '', last_name = '' } =
					this.item.user.employee;
				return first_name || last_name
					? `${first_name} ${last_name}`
					: 'Unknown';
			}
			return 'Unknown';
		},
		date() {
			const date = DateService.fromTimestamp(this.item.created_at);
			return date.format({ preset: 'changelog' });
		},
		action() {
			return this.item.action || 'unknown';
		},
		actionName() {
			return this.action;
		},
		actionIcon() {
			return `${this.svgSpritePath}#${getIcon(this.action)}`;

			function getIcon(action) {
				switch (action) {
					case ChangelogActions.Created:
						return 'asterisk';
					case ChangelogActions.Updated:
						return 'pencil';
					case ChangelogActions.Deleted:
						return 'trash';
					case ChangelogActions.Cloned:
						return 'copy';
					default:
						return 'unknown';
				}
			}
		},
		entity() {
			return this.item.entity || 'unknown';
		},
		wasDid() {
			switch (this.action) {
				case ChangelogActions.Created:
					return 'was created ';
				case ChangelogActions.Updated:
					return 'was updated ';
				case ChangelogActions.Deleted:
					return 'was deleted ';
				case ChangelogActions.Cloned:
					return 'was cloned ';
				default:
					return '';
			}
		},
		by() {
			return 'by ';
		},
		details() {
			return this.item.details || [];
		},
		linesIcon() {
			return `${this.svgSpritePath}#lines`;
		},
	},
	methods: {
		showNewValue(value) {
			if (this.action === ChangelogActions.Cloned) return !!value;
			return this.action !== ChangelogActions.Deleted;
		},
		showOldValue(value) {
			if (this.action === ChangelogActions.Cloned) return !!value;
			return this.action !== ChangelogActions.Created;
		},
		showLineIcon(oldValue, newValue) {
			return this.showOldValue(oldValue) && this.showNewValue(newValue);
		},
	},
	components: {
		Fragment,
		ItemDetails,
	},
};
</script>

<style scoped lang="scss">
.wrapper {
	margin: 0 -1rem;
	padding: 0.5rem 1rem;

	&:first-child {
		margin-top: -0.5rem;
	}

	&:hover {
		background-color: #f5f5f5;
	}
}

.item {
	--action-color: gray;
	padding-left: 32px;
	position: relative;
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	color: var(--color-text-secondary);

	&[data-action='created'] {
		--action-color: #479f76;
	}

	&[data-action='updated'] {
		--action-color: #fd9843;
	}

	&[data-action='deleted'] {
		--action-color: #e35d6a;
	}

	&[data-action='cloned'] {
		--action-color: #8383fd;
	}
}

.body {
	flex-grow: 1;
}

.action-icon {
	position: absolute;
	left: 0;
	top: 0;
	width: 20px;
	height: 20px;
	border-radius: 4px;
	background: var(--action-color);
	display: flex;
	align-items: center;
	justify-content: center;

	svg {
		color: #fff;
		pointer-events: none;
	}
}

.action-name {
	text-transform: capitalize;
	color: var(--action-color);
	margin-bottom: 4px;
}

.entity {
	color: var(--color-text-primary);
	letter-spacing: 0.1px;
}

.client {
	color: var(--color-text-primary);
}

.details {
	margin-top: 4px;
	display: flex;
	flex-wrap: wrap;
}

.detail {
	display: inline-flex;
	align-items: center;
	justify-content: flex-start;
	margin-right: 0.5rem;

	&:not(:last-child)::after {
		content: ';';
	}

	svg {
		margin: 0 4px;
		fill: none;
	}
}

.field {
	color: var(--color-text-primary);
	text-transform: capitalize;
}

a:not([href]) {
	color: inherit;
	cursor: default;
}
</style>

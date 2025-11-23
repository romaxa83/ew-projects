<template>
	<div>
		<template v-for="v in sortedTags">
			<div
				v-if="
					showTagAuthor &&
					(v.pivot?.employee_name || v.pivot?.attached_at)
				"
				class="d-flex flex-wrap align-items-center mb-1"
			>
				<button
					type="button"
					class="btn btn-xs btn-secondary waves-effect waves-themed mr-1"
					:style="style(v)"
				>
					<i class="fas mr-1" :class="icon(v.icon)"></i>
					{{ v.title }}
				</button>
				<span class="fs-xs text-dark mr-1">-</span>
				<span class="fs-xs text-dark">
					{{ v.pivot.employee_name }},
					{{ v.pivot.attached_at | formatDate('L') }}
				</span>
			</div>
			<button
				v-else
				type="button"
				class="btn btn-xs mb-1 btn-secondary waves-effect waves-themed mr-1"
				:style="style(v)"
			>
				<i class="fas mr-1" :class="icon(v.icon)"></i>
				{{ v.title }}
			</button>
		</template>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';

export default {
	name: 'ClientTags',
	props: {
		tags: {
			type: Array,
			required: true,
		},
		showTagAuthor: {
			type: Boolean,
		},
	},
	filters: {
		formatDate,
	},
	computed: {
		sortedTags() {
			return Object.values(this.tags)
				.slice()
				.sort((a, b) => a.sort - b.sort);
		},
	},
	methods: {
		icon(icon) {
			return icon ? `fa-${icon}` : 'fa-tag';
		},
		style(v) {
			let color = v.color ? v.color : '#6c757d';
			return {
				'background-color': color,
				'border-color': color,
			};
		},
	},
};
</script>

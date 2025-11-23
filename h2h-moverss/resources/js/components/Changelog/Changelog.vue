<template>
	<article class="panel">
		<header class="panel-hdr">
			<h2>
				Changelog
				<span
					v-if="showSpinner"
					role="status"
					aria-hidden="true"
					class="spinner-border spinner-border-sm ml-2"
				/>
			</h2>
			<div class="panel-toolbar" v-if="showSort">
				<sort :value="sort" @sort-change="refetchSort" />
			</div>
		</header>

		<div class="panel-container" v-if="!loading">
			<div class="panel-content">
				<div :class="contentClass">
					<div v-if="hasItems">
						<item
							v-for="(item, index) in items"
							:key="`${item.audit_id}-${item.created_at}-${index}`"
							:item="item"
							:can-view-employee-card="canViewEmployeeCard"
							:svg-sprite-path="svgSpritePath"
						/>
					</div>
					<div v-else>
						<div class="alert alert-info" role="alert">
							There are no changelog entries here yet
						</div>
					</div>
					<div
						class="fetched-content-spinner align-items-center d-flex justify-content-center"
					>
						<div class="spinner-border" role="status" />
					</div>
				</div>
				<div v-if="renderShowMoreButton" class="refetch-container">
					<button
						type="button"
						class="btn btn-outline-primary refetch-button"
						@click="refetchAll"
					>
						<svg width="16" height="16" viewBox="0 0 16 16">
							<use :href="showMoreIcon" />
						</svg>
						Show more
					</button>
				</div>
				<div v-if="renderShowLessButton" class="refetch-container">
					<button
						type="button"
						class="btn btn-outline-primary refetch-button"
						@click="refetchLess"
					>
						<svg width="16" height="16" viewBox="0 0 16 16">
							<use :href="showLessIcon" />
						</svg>
						Show less
					</button>
				</div>
			</div>
		</div>
	</article>
</template>

<script>
import Pagination from '@/components/Pagination/Pagination';
import Item from './Item';
import Sort from './Sort';

export default {
	name: 'Changelog',
	data() {
		return {
			svgSpritePath: '/images/changelog-sprite.svg?cache=5',
		};
	},
	props: {
		changelog: {
			type: Object,
			required: true,
		},
		canViewEmployeeCard: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		items() {
			return this.changelog.data;
		},
		sort() {
			return this.changelog.sort;
		},
		loading() {
			return this.changelog.loading;
		},
		showSpinner() {
			return this.loading || this.changelog.refetching;
		},
		showSort() {
			return !this.loading && this.hasItems;
		},
		hasItems() {
			return this.items.length > 0;
		},
		contentClass() {
			return {
				'fetched-content': true,
				refetching: this.changelog.refetching,
			};
		},
		renderShowMoreButton() {
			return !this.changelog.all && this.changelog.hasMore;
		},
		showMoreIcon() {
			return `${this.svgSpritePath}#show-more`;
		},
		renderShowLessButton() {
			return this.changelog.all;
		},
		showLessIcon() {
			return `${this.svgSpritePath}#show-less`;
		},
	},
	methods: {
		refetchSort(value) {
			this.$emit('refetch-changelog', { sort: value });
		},
		refetchAll() {
			this.$emit('refetch-changelog', { all: true });
		},
		refetchLess() {
			this.$emit('refetch-changelog', { less: true });
		},
	},
	components: {
		Item,
		Pagination,
		Sort,
	},
};
</script>

<style scoped>
.fetched-content-spinner {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: #fffa;
	opacity: 0;
	visibility: hidden;
	transition: 0.3s ease opacity, 0.3s ease visibility;
}

.fetched-content.refetching .fetched-content-spinner {
	opacity: 1;
	visibility: visible;
}

.refetch-container {
	text-align: center;
	padding: 16px 0 8px;
}

.refetch-button {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	max-width: 320px;
	margin: 0 auto;
	gap: 8px;
}
</style>

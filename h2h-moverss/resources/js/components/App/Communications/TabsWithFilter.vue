<template>
	<div class="w-100 position-sticky bg-white filter-window">
		<div class="tabs-loading pt-2" v-if="tabsLoading">
			<div class="fw-500">Loading...</div>
		</div>
		<div v-else class="tabs pt-2">
			<TabButton
				v-for="tab in tabs"
				:key="tab.value"
				:tabs-value="currentTabsValue"
				:value="tab.value"
				:label="tab.label"
				@change-tab="changeTab"
			/>
		</div>
		<div class="filter-form my-2">
			<div class="search input-group">
				<div class="input-group-prepend">
					<span
						class="input-group-text border-bottom-0 border-top-0 border-left-0 border-right-0 border-left-0 py-1 pr-0 bg-transparent"
					>
						<i class="fal fa-search"></i
					></span>
				</div>
				<input
					type="text"
					v-model="searchValue"
					placeholder="Search customer or Lead #"
					class="form-control form-control-sm border-left-0 border-bottom-0 border-top-0 border-left-0 border-right-0 border-left-0"
				/>
			</div>
			<div class="line"></div>
			<button
				type="button"
				class="filter-opener fs-nano"
				@click="toggleFilters"
			>
				<i class="fal fa-sliders-h"></i><span class="pl-1">Filter</span>
			</button>
		</div>
		<div
			class="mt-2 border-top"
			:class="{ 'mb-2': showFilterWindow }"
		></div>
		<div
			class="bg-white w-100 border-bottom"
			:class="{ 'd-none': !showFilterWindow }"
		>
			<CommunicationFiltersNew
				@clear-filters="clearFilters"
				@apply-filters="applyFilters"
			/>
		</div>
	</div>
</template>

<script>
import { debounce } from '@/helpers/functions';
import CommunicationFiltersNew from './CommunicationFiltersNew.vue';
import TabButton from './TabButton.vue';

export default {
	name: 'TabsWithFilters',
	data() {
		return {
			// TODO this duplicates the search value in the store.
			// Need to refactor for one source of truth.
			// Problem with debounced `watch` implementation below
			searchValue:
				new URL(window.location.href).searchParams.get('searchTerm') ||
				'',
		};
	},
	props: {
		showFilterWindow: {
			type: Boolean,
			required: true,
		},
	},
	methods: {
		toggleFilters() {
			const show = !this.showFilterWindow;
			this.$emit('toggle-filter-window', show);
		},
		changeTab(value) {
			this.$emit('tab-change', value);
		},
		clearFilters() {
			if (this.searchValue === '') {
				this.$emit('refetch-communications', null);
			} else {
				this.searchValue = ''; // this set triggers the watcher, no need to emit
			}
		},
		applyFilters() {
			this.$emit('refetch-communications', null);
		},
	},
	computed: {
		tabs() {
			return this.$store.state.communicationsFlow.filterParams
				.entityOptions;
		},
		tabsLoading() {
			return this.tabs.length === 0;
		},
		currentTabsValue() {
			return this.$store.state.communicationsFlow.filters.entity;
		},
	},
	watch: {
		searchValue: debounce(function (value) {
			if (String(value).length == 0 || String(value).length >= 3) {
				this.$emit('search-change', value);
			}
		}, 1200),
	},
	components: { CommunicationFiltersNew, TabButton },
};
</script>

<style scoped lang="scss">
.filter-window {
	top: 0;
	z-index: 3;
	border-top-left-radius: 4px;
}

.tabs {
	display: flex;
	flex-direction: row;
}

.filter-form {
	display: flex;
	align-items: center;

	& .search {
		flex-grow: 1;
	}

	& .filter-opener {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 0 14px;
		border-width: 0;
		background: none;
		margin: 0;
		height: 26px;
		line-height: 26px;

		&:hover {
			background-color: #1111110a;
		}

		&:active {
			background-color: #1111;
		}

		&:focus-visible {
			box-shadow: inset 0 0 0 1px #4679cc;
		}
	}

	& .line {
		flex-shrink: 0;
		width: 1px;
		background-color: #dee2e6;
		margin: 0;
		height: 26px;
	}
}

.tabs-loading {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 0 15px;
	height: 45px;
	border-bottom: 1px solid #0000001a;
}
</style>

<template>
	<div class="row">
		<div class="col-xl-12">
			<div class="panel">
				<div
					class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
					:class="{ 'd-none': !loading }"
				>
					<div class="d-flex justify-content-center">
						<div
							class="spinner-border text-info position-absolute"
							style="top: 30%"
							role="status"
						>
							<span class="sr-only">Loading...</span>
						</div>
					</div>
				</div>
				<div class="panel-hdr">
					<h2>Orders Pipeline</h2>
					<div class="panel-toolbar">
						<div class="form-label fs-sm mr-2">OrderBy</div>
						<select
							class="custom-select custom-select-sm"
							v-model="orderByCreated"
						>
							<option value="desc">Created Desc</option>
							<option value="asc">Created Asc</option>
						</select>
					</div>
				</div>
				<div class="panel-container show">
					<div
						class="panel-content border-bottom border-bottom-left-radius-0 border-bottom-right-radius-0"
					>
						<div class="row">
							<div class="col-sm-6 col-lg-4 mb-2">
								<div class="form-group">
									<label class="form-label">Manager</label>
									<vue-select2
										v-model="filterManager"
										:config="configSelect2()"
									>
										<option
											v-for="m of managers"
											:value="m.id"
										>
											{{ m.name }}
										</option>
									</vue-select2>
								</div>
							</div>
							<div class="col-sm-4 col-lg-3 mb-2">
								<div class="form-group">
									<label class="form-label">Branch</label>
									<vue-select2
										v-model="filterDivision"
										:config="configSelect2()"
									>
										<option
											v-for="d of divisions"
											:value="d.id"
										>
											{{ d.title }}
										</option>
									</vue-select2>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-container show">
					<div class="panel-content bg-faded">
						<div
							ref="topScroll"
							class="top-scroll bg-faded pb-2 d-flex pipeline-cols-container w-100"
							@scroll.passive="handleScroll"
						>
							<div
								class="pipeline-col"
								v-for="group of statusGroups"
								:key="group.id"
							>
								<div
									class="p-2 bg-primary-500 rounded overflow-hidden position-relative text-white"
								>
									<div class="">
										<h4 class="d-block l-h-n m-0 fw-500">
											{{ group.title }}
											<small class="m-0 l-h-n"
												>{{
													group.ordersCount
												}}
												orders</small
											>
										</h4>
									</div>
									<i
										class="ni ni-energy position-absolute pos-right pos-bottom opacity-15 mb-n4 mr-n4"
										style="font-size: 6rem"
									></i>
								</div>
							</div>
						</div>
						<div
							ref="content"
							class="d-flex pipeline-cols-container w-100 bottom-scroll"
							@scroll.passive="handleScroll"
						>
							<div
								class="pipeline-col"
								v-for="group of statusGroups"
								:key="group.id"
							>
								<order-col
									v-if="ordersRecordsCount"
									:group="group"
								></order-col>
								<!--                                    <ul v-if="ordersRecords[group.id]" class="pipeline-list">-->
								<!--                                        <order-record v-for="(record, key) of ordersRecords[group.id].records" :record="record" :key="key"/>-->
								<!--                                    </ul>-->
							</div>
						</div>
						<infinite-loading
							v-if="!loading"
							:distance="200"
							@infinite="infiniteHandler"
						>
							<div slot="no-more">
								<h3 class="text-muted mt-3">No more orders</h3>
							</div>
							<div slot="no-results">
								<h3 class="text-muted mt-3">No more orders</h3>
							</div>
						</infinite-loading>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import OrderCol from '@components/App/Pipeline/OrderCol';
import VueSelect2 from '@components/VueSelect2';
// import infiniteScroll from 'vue-infinite-scroll'
import InfiniteLoading from 'vue-infinite-loading';

export default {
	name: 'AppOrdersPipeline',
	data: () => ({
		loading: true,
		// recordsLoading: false
	}),
	computed: {
		managers() {
			return this.$store.state.ordersPipeline.managers;
		},
		divisions() {
			return this.$store.state.ordersPipeline.divisions;
		},
		filterDivision: {
			get() {
				return this.$store.state.ordersPipeline.filters.divisions;
			},
			set(value) {
				this.$store.commit(
					'ordersPipeline/updateFilterDivision',
					value
				);
			},
		},
		filterManager: {
			get() {
				return this.$store.state.ordersPipeline.filters.manager;
			},
			set(value) {
				this.$store.commit('ordersPipeline/updateFilterManager', value);
			},
		},
		orderByCreated: {
			get() {
				return this.$store.state.ordersPipeline.filters.orderByCreated;
			},
			set(value) {
				this.$store.commit(
					'ordersPipeline/updateFilterOrderByCreated',
					value
				);
			},
		},
		allLoaded() {
			// if (!this.loading)
			return (
				this.$store.state.ordersPipeline.totalLoaded >=
				this.$store.state.ordersPipeline.totalOrders
			);
			// return false;
		},
		ordersRecordsCount() {
			return Object.keys(this.ordersRecords).length;
		},
		ordersRecords() {
			return this.$store.state.ordersPipeline.orders;
		},
		statusGroups() {
			return Object.values(this.$store.state.ordersPipeline.groups);
		},
		statusGroupsIds() {
			return this.statusGroups.map((item) => item.id);
		},
		sortDirection() {
			return this.$store.state.ordersPipeline.filters.orderByCreated;
		},
	},
	methods: {
		configSelect2() {
			return {
				placeholder: 'No matter',
				multiple: true,
				allowClear: true,
			};
		},
		infiniteHandler($state) {
			// this.recordsLoading = true;

			this.$store.dispatch('ordersPipeline/fetchOrders').then(() => {
				if (this.allLoaded) $state.complete();
				else $state.loaded();
			});
			// console.log($state)
		},
		handleScroll(event) {
			if (event.target._prevClass === 'content') {
				this.$refs.topScroll.scrollLeft = this.$refs.content.scrollLeft;
			} else {
				this.$refs.content.scrollLeft = this.$refs.topScroll.scrollLeft;
			}
		},
	},
	mounted() {
		this.$store.dispatch('ordersPipeline/fetchSettings').then(() => {
			this.loading = false;
		});
	},
	watch: {
		filterManager() {
			this.loading = true;
			this.$store
				.dispatch('ordersPipeline/fetchOrders', { reload: true })
				.then(() => (this.loading = false));
		},
		filterDivision() {
			this.loading = true;
			this.$store
				.dispatch('ordersPipeline/fetchOrders', { reload: true })
				.then(() => (this.loading = false));
		},
		orderByCreated() {
			this.loading = true;
			this.$store
				.dispatch('ordersPipeline/fetchOrders', { reload: true })
				.then(() => (this.loading = false));
		},
	},
	components: {
		OrderCol,
		InfiniteLoading,
		VueSelect2,
	},
};
</script>

<style lang="scss">
.pipeline-cols-container {
	flex-direction: row;
	-webkit-flex-direction: row;
	overflow-x: auto;
	width: 100%;
	align: center;
	//width: 1200px;
	&::-webkit-scrollbar {
		width: 6px;
		height: 10px;
	}

	&::-webkit-scrollbar-track {
		//border-radius: 10px;
		background: rgba(0, 0, 0, 0.1);
	}

	&::-webkit-scrollbar-thumb {
		//border-radius: 10px;
		background: rgba(0, 0, 0, 0.2);
	}

	&::-webkit-scrollbar-thumb:hover {
		background: rgba(0, 0, 0, 0.25);
	}

	&::-webkit-scrollbar-thumb:active {
		background: rgba(0, 0, 0, 0.9);
	}
}

.bottom-scroll {
	&::-webkit-scrollbar {
		width: 6px;
		height: 1px;
	}
}

.top-scroll {
	position: sticky;
	top: 70px;
	z-index: 2;
}

.pipeline-col {
	padding-right: 0.5rem;
	padding-left: 0.5rem;
	//width: 350px;
	//width:350px;
	max-width: 450px;
	min-width: 350px;
	flex: 0 0 25%;
	//flex: 0 0 25%;

	&:first-child {
		margin-left: auto;
	}

	&:last-child {
		margin-right: auto;
	}
}
</style>

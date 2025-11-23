<template>
	<div class="panel block-order">
		<div class="panel-hdr">
			<h2 class="mr-0" style="flex: none">
				Order props
				<span
					v-show="loading || processing"
					class="spinner-border spinner-border-sm ml-1"
					role="status"
					aria-hidden="true"
				></span>
			</h2>
			<div v-if="canClone" class="panel-toolbar ml-auto flex-fill">
				<button
					@click="cloneOrder"
					class="btn btn-warning ml-auto shadow-0 waves-effect waves-themed"
					data-toggle="tooltip"
					title="Make a copy"
				>
					<span
						v-show="cloning"
						class="spinner-border spinner-border-sm mr-1"
						role="status"
						aria-hidden="true"
					></span>
					<i class="fas fa-copy"></i> Clone order
				</button>
			</div>
		</div>
		<div v-if="!loading" class="panel-container collapse show">
			<div class="panel-content">
				<div
					v-show="processing"
					class="frame-wrap position-absolute w-100 h-100 opacity-50"
					style="z-index: 10"
				>
					<div
						class="w-100 d-flex justify-content-center align-items-center"
					>
						<div
							class="spinner-border text-info position-absolute"
							style="top: 30%"
							role="status"
						>
							<span class="sr-only">Loading...</span>
						</div>
					</div>
				</div>
				<template v-if="!loading">
					<div class="fs-sm">Created: {{ createdAt }}</div>
					<div class="form-group mt-2 mb-1">
						<div class="input-group">
							<div class="input-group-prepend">
								<label
									class="input-group-text min-wi"
									for="division_id"
									>Branch</label
								>
							</div>
							<input
								type="hidden"
								v-model.number="division_id"
								id="division_id"
								:disabled="!canEdit"
							/>
							<select
								v-model.number="division_id"
								class="custom-select"
								v-if="canEdit"
							>
								<option
									v-for="v in dataSources.divisions"
									:key="v.id"
									v-bind:value="v.id"
									disabled
								>
									{{ v.title }}
								</option>
							</select>
							<div class="form-control" v-else>
								{{ currentDivisionTitle }}
							</div>
						</div>
					</div>
					<div class="form-group mt-2 mb-1">
						<div class="d-flex">
							<div
								class="input-group-text min-wi border-right-0 border-top-right-radius-0 border-bottom-right-radius-0"
							>
								<label class="mb-0">Manager name</label>
							</div>
							<div v-if="canEdit" class="flex-fill">
								<vue-select2
									v-if="dataSources.managers"
									v-model.number="user_id"
									setclass="form-control"
									:config="{
										width: '100%',
										containerCssClass:
											'select2-left-radius-0',
									}"
								>
									<option :value="null">None</option>
									<option
										v-for="(v, key) in dataSources.managers"
										:value="key"
									>
										{{ v.name }}
									</option>
								</vue-select2>
							</div>
							<div class="form-control" v-else>
								{{ currentManagerName }}
							</div>
						</div>
					</div>
					<div class="form-group mt-2 mb-1">
						<div class="input-group">
							<div class="input-group-prepend">
								<label
									class="input-group-text min-wi"
									for="move_size_id"
									>Size</label
								>
							</div>
							<select
								v-if="canEdit"
								v-model.number="move_size_id"
								class="custom-select"
								id="move_size_id"
							>
								<option :value="null">None</option>
								<option
									v-for="v in dataSources.moveSizes"
									:key="v.id"
									v-bind:value="v.id"
								>
									{{ v.title }}
								</option>
							</select>
							<div v-else class="form-control">
								{{ currentMoveSize }}
							</div>
						</div>
					</div>
					<div class="form-group mt-2 mb-1">
						<div class="input-group">
							<div class="input-group-prepend">
								<label
									class="input-group-text min-wi"
									for="o_type"
									>Type</label
								>
							</div>
							<select
								v-if="canEdit"
								v-model="type"
								class="custom-select"
								id="o_type"
							>
								<option :value="null">None</option>
								<option value="house">House</option>
								<option value="apartment">Apartment</option>
								<option value="storage">Storage</option>
								<option value="business">Business</option>
							</select>
							<div v-else class="form-control">
								{{ currentType }}
							</div>
						</div>
					</div>
					<div class="form-group mt-2 mb-1">
						<div class="d-flex">
							<div
								class="input-group-text min-wi border-right-0 border-top-right-radius-0 border-bottom-right-radius-0"
							>
								<label class="mb-0">Source<sup>*</sup></label>
							</div>
							<div v-if="canEdit" class="flex-fill">
								<vue-select2
									v-if="dataSources.sources"
									v-model.number="source_id"
									setclass="form-control"
									:config="{
										width: '100%',
										containerCssClass:
											'select2-left-radius-0',
									}"
								>
									<option :value="null">None</option>
									<option
										v-for="v in dataSources.sources"
										:value="v.id"
									>
										{{ v.title }}
									</option>
								</vue-select2>
							</div>
							<div v-else class="form-control">
								{{ currentSource }}
							</div>
						</div>
					</div>
					<div class="form-group mt-2 mb-1">
						<div v-if="canEdit" class="input-group">
							<div class="input-group-prepend">
								<label
									class="input-group-text min-wi"
									for="o_tags"
									>Tags</label
								>
							</div>
							<multiselect
								v-model="selectedTags"
								id="o_tags"
								track-by="id"
								label="title"
								placeholder="Type to search"
								tagPosition="bottom"
								class="form-control"
								:multiple="true"
								:show-no-results="false"
								:show-no-options="false"
								:options="tags"
							>
								<template slot="option" slot-scope="props">
									<i
										:class="
											'fas mr-1 fa-' +
											(props.option.icon
												? props.option.icon
												: 'tag')
										"
										:style="{ color: props.option.color }"
									></i>
									<span class="option__title">{{
										props.option.title
									}}</span>
								</template>
							</multiselect>
						</div>
						<div v-else>
							<hr class="divider" />
							<template v-if="selectedTags.length > 0">
								Tags:
								<div v-for="tag in selectedTags">
									<i
										:class="
											'fas mr-1 fa-' +
											(tag.icon ? tag.icon : 'tag')
										"
										:style="{ color: tag.color }"
									></i>
									<span class="option__title">{{
										tag.title
									}}</span>
								</div>
							</template>
							<div v-else>Tags: None</div>
						</div>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';
import { AxiosHelper } from '@/helpers/axiosHelper';
import VueSelect2 from '@components/VueSelect2';
import cloneDeep from 'lodash.clonedeep';
import Multiselect from 'vue-multiselect';
import { mapGetters } from 'vuex';

window.timer = window.timer || false;
let order_id = document.getElementById('order_id').textContent;

export default {
	name: 'OverviewOrder',
	components: {
		VueSelect2,
		Multiselect,
	},
	filters: {
		formatDate,
	},
	props: {
		dataSources: {
			type: Object,
			required: true,
		},
		canClone: {
			type: Boolean,
			required: true,
		},
		canEdit: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			processing: false,
			cloning: false,
			hash: null,
			created_at: null,
			created_at_local_tz: null,
			user_id: null,
			source_id: null,
			move_size_id: null,
			division_id: null,
			type: null,
			selectedTags: [],
		};
	},
	computed: {
		tags() {

			let types = this.$store.state.session?.types?.tags ?? {};

			return Object.values(types)
				.slice()
				.sort((a, b) => a.sort - b.sort);
		},
		createdAt() {
			if (this.created_at_local_tz)
				return formatDate(
					this.created_at_local_tz,
					'll, [at] h:mm a',
					'YYYY-MM-DD HH:mm:ss',
					true
				);
			// formatDate(value, toFormat = 'll, [at] h:mm a', fromFormat = 'YYYY-MM-DD HH:mm:ss', notFromUtc = false)
			return '';
		},
		valuesChanged() {
			return [
				this.division_id,
				this.move_size_id,
				this.source_id,
				this.user_id,
				this.type,
				this.selectedTags,
			]
				.filter(function (el) {
					return el !== null;
				})
				.join();
		},
		currentDivisionTitle() {
			const division = this.dataSources.divisions?.[this.division_id];
			return division?.title || 'Unknown';
		},
		currentManagerName() {
			const manager = this.dataSources.managers?.[this.user_id];
			return manager?.name || 'None';
		},
		currentMoveSize() {
			const size = this.dataSources.moveSizes?.[this.move_size_id];
			return size?.title || 'None';
		},
		currentType() {
			return this.type || 'None';
		},
		currentSource() {
			const sources = this.dataSources.sources || [];
			const source = sources.find((s) => s.id === this.source_id);
			return source?.title || 'None';
		},
		currentTags() {
			return this.selectedTags.map((tag) => tag.title).join(', ');
		},
		...mapGetters({
			order_id: 'getOrderId',
			notes: 'order/notes',
			dataSourcesLoading: 'order/dataSourcesLoading',
		}),
	},
	watch: {
		valuesChanged(new_v, old_v) {
			// Смотрим если какое-то из полей менялось, оптравляем отложенный запрос на апдейт
			if (old_v) {
				this.processing = true;
				// clearTimeout(window.timer);
				// window.timer = setTimeout(() => {
				this.sentUpdate();
				// }, 1000);
			}
		},
	},
	mounted() {
		this.$store.dispatch('getSession').then(({ order }) => {
			this.hash = order.hash;
			this.created_at = order.created_at;
			this.created_at_local_tz = order.created_at_local_tz;
			this.user_id = order.user_id;
			this.source_id = order.source_id;
			this.move_size_id = order.move_size_id;
			this.division_id = order.division_id;
			this.type = order.type;
			this.selectedTags = cloneDeep(order.tags);
			this.loading = false;

			if (!this.source_id) this.chooseOrderSourceModal();
		});
	},
	methods: {
		cloneOrder() {
			if (this.cloning) return;

			this.cloning = true;
			AxiosHelper({
				url: '/orders/copy',
				data: {
					order_id: this.order_id,
				},
			}).then(({ href, msg }) => {
				App.Forms.showAlert('success', msg);

				setTimeout(() => (window.location.href = href), 800);
			});
		},
		async sentUpdate() {
			return AxiosHelper({
				url: '/orders/' + order_id + '/order',
				data: {
					user_id: this.user_id,
					source_id: this.source_id,
					move_size_id: this.move_size_id,
					division_id: this.division_id,
					type: this.type,
					selectedTags: this.selectedTags,
				},
			}).finally(() => (this.processing = false));
		},
		async chooseOrderSourceModal() {
			const inputOptions = new Promise((resolve) => {
				this.dataSourcesLoading.promise().then(() => {
					let inputOptions = {};
					for (let [i, v] of this.dataSources.sources.entries()) {
						inputOptions[v.id] = v.title;
					}

					resolve(inputOptions);
				});
			});

			let html = '';
			if (this.notes) {
				const first_note = this.notes[0] ?? null;
				if (
					first_note &&
					first_note.is_pinned &&
					!first_note.user_id &&
					first_note.text.includes('Source')
				) {
					html +=
						'<div class="card card-zoom-hover position-relative overflow-hidden border border-primary">' +
						'<div class="card-header py-2 pr-2 d-flex align-items-center flex-wrap bg-primary-300">' +
						'<div class="fs-xs mr-auto pr-3"><i class="fas fa-map-pin mr-2"></i> <b>First note of the order:</b></div>' +
						'</div>' +
						'<div class="card-body fs-sm py-2">' +
						first_note.text.replace(
							'Source',
							'<b class="red">Source</b>'
						) +
						'</div>' +
						'</div>' +
						'</div>';
				}
			}

			await Swal.fire({
				title: 'Choose the order Source',
				html,
				input: 'select',
				inputOptions: inputOptions,
				inputPlaceholder: 'Select source',
				allowOutsideClick: false,
				allowEscapeKey: false,
				inputValidator: async (value) => {
					return new Promise((resolve) => {
						if (parseInt(value)) {
							this.source_id = value;

                            // sendUpdate is triggered by "watcher"
							// this.sentUpdate().then(() => resolve());
                            resolve()
						} else {
							resolve('You must choose the source');
						}
					});
				},
			});
		},
	},
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
.min-wi {
	min-width: 125px;
}

.select2-left-radius-0 {
	border-top-left-radius: 0 !important;
	border-bottom-left-radius: 0 !important;
}

.multiselect {
	padding: 0;
	min-height: 39px;
	height: 39px !important;
}

.multiselect__tags {
	min-height: 35px;
	font-size: 0.75rem;
	border: none;
}

.multiselect__tag {
	padding: 3px 26px 3px 10px;
	margin-bottom: 3px;
}

.swal2-select {
	margin: 20px 25px;
	width: 90%;
}

.red {
	color: red;
}
</style>

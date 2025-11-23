<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>
				<span
					v-show="processing"
					class="spinner-border spinner-border-sm mr-1"
					role="status"
					aria-hidden="true"
				></span>
				Sizing
			</h2>
			<div v-if="canManage" class="panel-toolbar">
				<div class="custom-control d-flex custom-switch">
					<input
						:id="'sizing-switch-inventory-' + componentId"
						type="checkbox"
						class="custom-control-input"
						v-model="sizing_is_auto"
                        :disabled="processing"
					/>
					<label
                        @click.prevent="processing ? undefined : toggleAuto()"
						class="custom-control-label fw-500"
						:for="'sizing-switch-inventory-' + componentId"
						>Auto</label
					>
				</div>
			</div>
		</div>
		<div class="panel-container show">
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
				<div class="row">
					<div class="col">
						<div class="input-group bg-white shadow-inset-2">
							<div class="input-group-prepend">
								<label
									:for="'inventories_vol-' + componentId"
									class="input-group-text"
								>
									CuFT
								</label>
							</div>
							<input
								:id="'inventories_vol-' + componentId"
								type="text"
								class="form-control text-left pr-0"
								v-model.number="sizing_volume"
								placeholder="Volume"
								:disabled="!!sizing_is_auto"
							/>
							<input
								:id="'inventories_wei-' + componentId"
								type="text"
								class="form-control text-right pl-0"
								v-model.number="sizing_weight"
								placeholder="Weight"
								:disabled="!!sizing_is_auto"
							/>
							<div class="input-group-append">
								<label
									:for="'inventories_wei-' + componentId"
									class="input-group-text"
								>
									lb
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="mt-0">
					<table class="table m-0">
						<tbody>
							<tr v-for="(v, i) in formatRecords" :key="v.id">
								<td
									class="fs-sm"
									:class="{ 'border-top-0': !i }"
								>
									{{ v.title }}
								</td>
								<td
									class="text-right"
									:class="{ 'border-top-0': !i }"
								>
									{{ v.volume }}
									<span class="text-muted fs-xs fw-800"
										>CuFT</span
									>
								</td>
								<td
									class="text-right"
									:class="{ 'border-top-0': !i }"
								>
									{{ v.weight }}
									<span class="text-muted fs-xs fw-800"
										>lb</span
									>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr style="background-color: #f5fcff">
								<th class="fs-md fw-400 py-2">Total</th>
								<th class="text-right py-2">
									{{ totalVolume }}
									<span class="fs-xs fw-800">CuFT</span>
								</th>
								<th class="text-right py-2">
									{{ totalWeight }}
									<span class="fs-xs fw-800">lb</span>
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

window.timer = window.timer || false;
let order_id = document.getElementById('order_id').textContent;

export default {
	name: 'Sizing',
	props: {
		componentId: {
			type: Number,
			required: true,
		},
		is_changed: {
			type: Boolean,
			required: false,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		sizing_is_auto: {
			get() {
				return this.inventories.sizing_is_auto;
			},
			set(value) {
				this.$store.commit('order/setSizingIsAuto', value);
			},
		},
		sizing_volume: {
			get() {
				return this.inventories.sizing_volume;
			},
			set(value) {
				this.$store.commit('order/setSizingVolume', value);
			},
		},
		sizing_weight: {
			get() {
				return this.inventories.sizing_weight;
			},
			set(value) {
				this.$store.commit('order/setSizingWeight', value);
			},
		},
		totalVolume() {
			return this.formatRecords.reduce(function (sum, v) {
				return sum + v.volume;
			}, 0);
		},
		totalWeight() {
			return this.formatRecords.reduce(function (sum, v) {
				return sum + v.weight;
			}, 0);
		},
		autoSizingChanged() {
			if (this.inventories.sizing_is_auto) {
				return this.totalVolume + this.totalWeight;
			}
		},
		...mapGetters({
			inventories: 'order/inventories',
			formatRecords: 'order/inventoriesFormat',
			processing: 'order/inventoriesProcessing',
		}),
	},
	watch: {
		autoSizingChanged(val) {
			if (val) {
				this.$store.commit('order/setSizingVolume', this.totalVolume);
				this.$store.commit('order/setSizingWeight', this.totalWeight);
			}
		},
		sizing_volume(new_v, old_v) {
			// Отменяем запрос на апдейт
			if (this.sizing_is_auto || parseFloat(new_v) === parseFloat(old_v))
				return true;

			clearTimeout(window.timer);
			window.timer = setTimeout(() => {
				this.sentUpdate();
			}, 1000);
		},
		sizing_weight(new_v, old_v) {
			if (this.sizing_is_auto || parseFloat(new_v) === parseFloat(old_v))
				return true;

			clearTimeout(window.timer);
			window.timer = setTimeout(() => {
				this.sentUpdate();
			}, 1000);
		},
	},
	mounted() {
		this.$store.dispatch('getSession');
		this.initMasks();
	},
	methods: {
		initMasks() {
			Inputmask({
				alias: 'numeric',
				digits: 2,
				digitsOptional: true,
				clearMaskOnLostFocus: false,
				placeholder: '0',
				allowMinus: false,
				// jitMasking: true
			}).mask($('#inventories_vol-' + this.componentId)[0]);

			Inputmask({
				alias: 'numeric',
				digits: 2,
				digitsOptional: true,
				clearMaskOnLostFocus: false,
				placeholder: '0',
				allowMinus: false,
				// jitMasking: true
			}).mask($('#inventories_wei-' + this.componentId)[0]);
		},
		async sentUpdate() {
			// Если Inventory изменен, вначале сохраняем его
			if (this.is_changed) {
				await new Promise((resolve, reject) =>
					this.$parent.$refs.inventory.saveChanges({
						resolve,
						reject,
					})
				);
			}

			this.$store.commit('order/setInventoryProcessing', true);
			return axios
				.post('/orders/' + order_id + '/order/save-sizing', {
					order_id,
					sizing_is_auto: this.sizing_is_auto,
					sizing_volume: this.sizing_volume,
					sizing_weight: this.sizing_weight,
					auto_sizing_volume: this.totalVolume,
					auto_sizing_weight: this.totalWeight,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.$root.$refs.overview.recalculate();
						this.$store.dispatch(
							'order/updateInventory',
							resp.data.record
						);
					} else {
						throw {
							response: {
								data: resp.data,
							},
						};
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				})
				.finally(() => {
                    this.$store.commit('order/setInventoryProcessing', false);
                });
		},
		toggleAuto() {
			this.sizing_is_auto = !this.sizing_is_auto;
			this.sentUpdate().catch(
				() => (this.sizing_is_auto = !this.sizing_is_auto)
			);
		},
	},
};
</script>

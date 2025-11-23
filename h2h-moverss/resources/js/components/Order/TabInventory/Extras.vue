<template>
	<div class="panel extra-panel">
		<div class="panel-hdr">
			<h2>
				<span
					v-show="updating"
					class="spinner-border spinner-border-sm mr-1"
					role="status"
					aria-hidden="true"
				></span>
				Extra
			</h2>
			<div class="panel-toolbar">
				<button
					class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed"
					@click="clickModal()"
				>
					<i class="fal fa-plus"></i> Add Extra
				</button>
			</div>
		</div>
		<div class="panel-container show">
			<div class="panel-content pt-0">
				<div class="mt-0">
					<table class="table m-0">
						<thead>
							<tr>
								<th class="border-0" colspan="2">Additional</th>
								<th class="border-0 text-right">Total</th>
								<th class="border-0"></th>
							</tr>
							<tr v-for="v in customsExtras" :key="v.id">
								<td class="fs-sm" colspan="2">
									<a
										@click.prevent="clickModal()"
										href="#"
										class="extras-link"
									>
										<u v-text="v.title"></u>
									</a>
								</td>
								<td class="text-right fw-700">
									{{ v.price | currencyFilter }}
								</td>
								<td class="text-right fw-700">
									<a
										href="#"
										@click.prevent="
											removeService('customs', v.id)
										"
									>
										<i
											class="color-danger-800 fas fa-times"
										></i>
									</a>
								</td>
							</tr>
							<tr>
								<th class="border-0">
									Extra materials and services
								</th>
								<th class="border-0 text-right">Qty</th>
								<th class="border-0 text-right">Total</th>
								<th class="border-0"></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="v in materials" :key="v.id">
								<td class="fs-sm">
									<a
										@click.prevent="
											clickModal(v.material_id)
										"
										href="#"
										class="extras-link"
									>
										<u v-text="v.title"></u>
									</a>
								</td>
								<td class="text-right">
									<div class="d-flex flex-wrap-reverse">
										<div
											v-if="
												v.need_packing ||
												v.need_unpacking
											"
											class="pl-1 pr-0 flex-fill fs-xs text-right"
										>
											<span
												v-if="v.need_packing"
												title="Packing"
											>
												<i class="fas fa-inbox-in"></i>
												<span class="text-muted pl-1">{{
													v.packing_price
														| currencyFilter
												}}</span>
											</span>
											<span
												v-if="v.need_unpacking"
												title="Unpacking"
											>
												<i
													class="pl-1 fas fa-inbox-out"
												></i>
												<span class="text-muted pl-1">{{
													v.unpacking_price
														| currencyFilter
												}}</span>
											</span>
										</div>
										<div
											class="pl-1 pl-0 flex-fill fs-xs pl-2"
										>
											{{ v.qty }} x
											{{ v.price | currencyFilter }}
										</div>
									</div>
								</td>
								<td class="text-right fw-700">
									{{ subTotal(v) | currencyFilter }}
								</td>
								<td class="text-right fw-700">
									<a
										href="#"
										@click.prevent="
											removeService('materials', v.id)
										"
									>
										<i
											class="color-danger-800 fas fa-times"
										></i>
									</a>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr style="background-color: #f5fcff">
								<th colspan="4" class="text-right py-2 fw-700">
									<span
										v-show="updating"
										class="spinner-border spinner-border-sm mr-1"
										role="status"
										aria-hidden="true"
									></span>
									{{
										(totalCustomExtras + totalMaterials)
											| currencyFilter
									}}
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<extras-modal
			v-if="openModal"
			:open-id="open_id"
			:can-manage="canManage"
		></extras-modal>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

const ExtrasModal = () =>
	import(/* webpackChunkName: "OrderExtrasModal" */ './Extras/ExtrasModal');
import currencyFilter from '@/filters/currency.filter';

export default {
	name: 'Extras',
	components: {
		ExtrasModal,
	},
	filters: {
		currencyFilter,
	},
	data() {
		return {
			openModal: false,
			open_id: null,
			updating: false,
		};
	},
	props: {
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		...mapGetters({
			materials: 'order/materials',
			customsExtras: 'order/customsExtras',
			totalCustomExtras: 'order/totalCustomExtras',
			totalMaterials: 'order/totalMaterials',
			orderID: 'getOrderId',
		}),
	},
	methods: {
		clickModal(id = null) {
			this.open_id = id;
			if (!this.openModal) this.openModal = true;
			else {
				$('#extras-modal-right').modal('show');
				// cell.remove();
				// $('#extras-modal-right').insertAfter('#js-page-content');
			}
		},
		subTotal(v) {
			let packing_price =
					v.need_packing && parseFloat(v.packing_price)
						? parseFloat(v.packing_price)
						: 0,
				unpacking_price =
					v.need_unpacking && parseFloat(v.unpacking_price)
						? parseFloat(v.unpacking_price)
						: 0;

			return (
				(parseFloat(v.price) + packing_price + unpacking_price) * v.qty
			);
		},
		async removeService(type, id) {
			if (this.updating) return;

			this.updating = true;
			let payload = {
				records: this.materials,
				custom_records: this.customsExtras,
			};

			if (type === 'customs') {
				payload.custom_records = payload.custom_records
					.slice()
					.filter((item) => item.id !== id);
			} else {
				payload.records = payload.records
					.slice()
					.filter((item) => item.id !== id);
			}

			await this.$store.dispatch('order/saveMaterials', payload);
			this.updating = false;
			this.$root.$refs.overview.recalculate();
		},
	},
};
</script>

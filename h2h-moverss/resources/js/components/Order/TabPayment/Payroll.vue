<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>Payments log</h2>
			<div v-if="canManage" class="panel-toolbar">
				<button
					class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed"
					@click="paymentModal()"
				>
					<i class="fal fa-plus"></i> Payment
				</button>
				<button
					class="btn btn-primary btn-sm waves-effect waves-themed"
					@click="openChargeModal()"
				>
					<i class="fal fa-plus"></i> Virtual Terminal
				</button>
			</div>
		</div>
		<div class="panel-container collapse show">
			<div class="panel-content pt-2">
				<div
					v-if="loading"
					class="frame-wrap position-absolute w-100 h-100 opacity-50"
				>
					<div
						class="w-100 d-flex justify-content-center align-items-center"
					>
						<div
							class="spinner-border text-info position-absolute"
							style="top: 50%"
							role="status"
						>
							<span class="sr-only">Loading...</span>
						</div>
					</div>
				</div>
				<div v-else>
					<table class="table table-striped m-0">
						<thead class="thead-themed">
							<tr>
								<th>#</th>
								<th>Date</th>
								<th>Username</th>
								<th>Method</th>
								<th>Description</th>
								<th>
									<span
										v-show="processingToggle"
										class="spinner-border spinner-border-sm mr-2"
										role="status"
										aria-hidden="true"
									></span>
									In total
								</th>
								<th>Amount</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="v in records" :key="v.id">
								<th scope="row" v-text="v.id"></th>
								<td>
									{{ v.created_at | formatDate }}
								</td>
								<td>{{ v.user_id | managerName }}</td>
								<td
									v-text="v.account ? v.account.title : null"
								></td>
								<td v-html="shortDescription(v)"></td>
								<td v-if="canManage" class="d-inline-flex">
									<div class="custom-control custom-checkbox">
										<input
											type="checkbox"
											class="custom-control-input"
											:id="`cb_${v.id}`"
											:checked="v.in_total_sum"
											@click.prevent="
												toggleInTotalSum(v.id)
											"
										/>
										<label
											class="custom-control-label"
											:for="`cb_${v.id}`"
										></label>
									</div>
								</td>
								<td v-else>
									{{
										v.in_total_sum
											? 'Included'
											: 'Not included'
									}}
								</td>
								<td>{{ v.amount | currencyFilter }}</td>
							</tr>
						</tbody>
						<tfoot>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th>Total Paid:</th>
							<th>{{ total | currencyFilter }}</th>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<charge-modal v-if="chargeModal"></charge-modal>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';
import managerName from '@/filters/managerName.filter';
import { DateService } from '@/services/date';

const ChargeModal = () =>
	import(/* webpackChunkName: "OrderChargeModal" */ './ChargeModal');

export default {
	name: 'PaymentPayroll',
	components: { ChargeModal },
	filters: {
		managerName,
		currencyFilter,
		formatDate(dateStr) {
			return new DateService(dateStr).format({ preset: 'payments' });
		},
	},
	props: {
		loading: {
			type: Boolean,
			required: true,
		},
		processingToggle: {
			type: Boolean,
			required: true,
		},
		records: {
			type: Array,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			chargeModal: false,
		};
	},
	computed: {
		total() {
			return this.records.reduce(function (sum, item) {
				return sum + (item.in_total_sum ? parseFloat(item.amount) : 0);
			}, 0);
		},
	},
	watch: {
		loading() {
			this.$nextTick(() => {
				let popover = $('[data-toggle="popover"]');

				popover.popover({ html: true });
				popover.on('show.bs.popover', function () {
					$($(this).data('bs.popover').getTipElement()).css(
						'max-width',
						'700px'
					);
					$('html').on('mouseup', function (e) {
						if ($(e.target)[0].className.indexOf('popover') == -1) {
							$('.popover').each(function () {
								$(this).popover('hide');
							});
						}
					});
				});
			});
		},
	},
	methods: {
		openChargeModal() {
			if (!this.chargeModal) {
				this.chargeModal = true;
			} else $('#modal-order-charge-modal').modal('show');
		},
		paymentModal() {
			$('#payment-modal').modal('show');
		},
		shortDescription(v) {
			let txt = '-';

			if (v.description) {
				if (v.description.length >= 35) {
					txt = v.description.substring(0, 25) + '...';

					let text = v.description.replace(/([^>])\n/g, '$1<br/>');

					txt +=
						'<button type="button" class="btn btn-outline-primary btn-xs btn-icon rounded-circle waves-effect waves-themed" data-toggle="popover"' +
						'                            data-trigger="click" data-placement="top"' +
						'                            data-content="' +
						text +
						'"' +
						'                            data-original-title="Description">?' +
						'               </button>';
				} else {
					txt = v.description;
				}
			}

			return txt;
		},
		async toggleInTotalSum(payment_id) {
			if (this.processingToggle) return;

			await this.$emit('toggleInTotal', payment_id);
		},
	},
};
</script>

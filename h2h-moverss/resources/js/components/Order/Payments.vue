<template>
	<div class="row">
		<div class="col-lg-8 col-xl-8 order-lg-1 order-xl-1">
			<PaymentPayroll
				:loading="loading"
				:records="records"
				:processing-toggle="processingToggle"
				:can-manage="canManage"
				@toggleInTotal="toggleInTotal"
			/>

			<PayrollEmployee
				v-if="payroll && canViewPayroll"
				:payroll="payroll"
                :can-view-employee-card="canViewEmployeeCard"
			/>

			<Changelog
				v-if="canViewChangelog"
				:changelog="changelog"
				:can-view-employee-card="canViewEmployeeCard"
				@refetch-changelog="refetchChangelog"
			/>
		</div>

		<div class="col-lg-4 col-xl-4 order-lg-2 order-xl-3">
			<div class="panel">
				<div class="panel-hdr">
					<h2>Calculation</h2>
				</div>
				<div class="panel-container show">
					<div class="panel-content px-1">
						<div class="container">
							<CalculationTable />
						</div>
					</div>
				</div>
			</div>
		</div>

		<payment-modal
			v-if="!loading"
			:accounts="accounts"
			@addPayment="addPayment"
		></payment-modal>
	</div>
</template>

<script>
import Changelog from '@/components/Changelog/Changelog';
import { AxiosHelper } from '@/helpers/axiosHelper';
import PaymentModal from './TabPayment/Modal';
import PaymentPayroll from './TabPayment/Payroll';
import PayrollEmployee from './TabPayment/PayrollEmployee';

let order_id = document.getElementById('order_id').textContent;

const CalculationTable = () =>
	import(
		/* webpackChunkName: "CalculationTable" */ './TabOverview/Calculation/Table'
	);

export default {
	name: 'Payments',
	components: {
		PaymentPayroll,
		PayrollEmployee,
		PaymentModal,
		CalculationTable,
		Changelog,
	},
	data() {
		return {
			loading: true,
			processingToggle: false,
			records: null,
			accounts: null,
		};
	},
	mounted() {
		let vm = this;

		// console.log('ORDER id', order_id);
		// console.log('PAY', this.loading, this.processingToggle, this.records, this.accounts);

		// Костыль, смотрим что нам надо подгрузить данные по табу
		$('#order-tabs').on('shown.bs.tab', function () {
			let tab = $('.active', this).attr('href').replace('#tab-', '');
			if (!vm.is_loaded && tab === 'payments') {
				vm.getPayments();
			}
		});
	},
	computed: {
		payroll() {
			console.log(
				'Payroll into Payments component',
				this.$store.state.order.payroll
			);
			return this.$store.state.order.payroll;
		},
		canViewPayroll() {
			return this.payroll.meta?.actions?.can_view || false;
		},
		changelog() {
			return {
				loading: this.loading,
				...this.$store.state.order.changelog,
			};
		},
		canManage() {
			return this.$store.state.order.permissions.canManageOrder;
		},
		canViewChangelog() {
			return this.$store.state.order.permissions.canViewChangelog;
		},
		canViewEmployeeCard() {
			return this.$store.state.order.permissions.canViewEmployeeCard;
		},
	},
	methods: {
		addPayment(record, promise = null) {
			axios
				.post('/orders/payments/create', {
					order_id,
					...record,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.records = resp.data.records;
						this.$root.$refs.overview.recalculate();
						if (promise) promise.resolve();
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
					if (promise) promise.reject(error.response.data);
				})
				.finally(() => (this.loading = false));
		},
		getPayments() {
			this.loading = true;
			AxiosHelper({
				url: '/orders/payments',
				data: {
					order_id,
				},
			})
				.then(({ records, accounts }) => {
					this.records = records;
					this.accounts = accounts;
				})
				.finally(() => (this.loading = false));
		},
		toggleInTotal(payment_id) {
			this.processingToggle = true;

			return AxiosHelper({
				url: '/orders/payments/toggle-in-total',
				data: {
					order_id,
					payment_id,
				},
			})
				.then(({ records }) => {
					this.records = records;
					this.$root.$refs.overview.recalculate();
				})
				.finally(() => (this.processingToggle = false));
		},
		refetchChangelog(params) {
			this.$store.dispatch('order/refetchChangelog', params);
		},
	},
};
</script>

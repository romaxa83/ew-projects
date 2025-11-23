<template>
	<form autocomplete="off" @keydown.enter.prevent="">
		<h5 class="frame-heading">Payment Method</h5>
		<div class="frame-wrap">
			<div class="custom-control custom-radio custom-control-inline">
				<input
					type="radio"
					class="custom-control-input"
					id="payment_method_1"
					v-model="form.type"
					value="card_charge"
				/>
				<label class="custom-control-label" for="payment_method_1"
					>Charge a Credit Card</label
				>
			</div>
			<!--            <div class="custom-control custom-radio custom-control-inline">-->
			<!--                <input type="radio" class="custom-control-input" id="payment_method_2" v-model="form.type"-->
			<!--                       value="card_refund">-->
			<!--                <label class="custom-control-label" for="payment_method_2">Refund a Credit Card</label>-->
			<!--            </div>-->
			<!--            <div class="custom-control custom-radio custom-control-inline">-->
			<!--                <input type="radio" class="custom-control-input" id="payment_method_3" v-model="form.type"-->
			<!--                       value="bank">-->
			<!--                <label class="custom-control-label" for="payment_method_3">Charge a Bank Account ? Надо ли</label>-->
			<!--            </div>-->
		</div>

		<div class="padding">
			<div class="row">
				<div class="col-sm-7">
					<div class="card">
						<div class="card-header">
							<strong>Payment/Auth Info</strong>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label for="amount">Amount</label><br />
										<div class="input-group">
											<div class="input-group-prepend">
												<span class="input-group-text"
													>$</span
												>
											</div>
											<input
												ref="amountValue"
												class="form-control"
												type="text"
												v-model="form.amount"
											/>
										</div>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label for="amount">Order ID</label>
										<div
											class="pt-2 text-dark"
											v-text="order_id"
										></div>
									</div>
								</div>
								<div class="col-sm-2">
									<div class="form-group">
										<label for="inTotal">Included</label>
										<div class="input-group">
											<div
												class="custom-control custom-checkbox"
											>
												<input
													type="checkbox"
													class="custom-control-input"
													id="inTotal"
													v-model="form.in_total"
												/>
												<label
													class="custom-control-label"
													for="inTotal"
												></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row mt-2">
								<div class="form-group col-sm-6">
									<label for="c_number"
										>Credit Card Number</label
									>
									<div class="input-group">
										<input
											class="form-control"
											id="c_number"
											type="text"
											v-model="form.card.number"
											placeholder="0000 0000 0000 0000"
											autocomplete="none"
										/>
									</div>
								</div>
								<div class="form-group col-sm-4">
									<label for="expire"
										>Expire Date
										<small>(Month/Year)</small></label
									>
									<input
										class="form-control"
										id="expire"
										type="text"
										v-model="form.card.expire"
									/>
								</div>
								<div class="col-sm-2">
									<div class="form-group">
										<label for="cvv">CVV/CVC</label>
										<input
											class="form-control"
											id="cvv"
											type="text"
											v-model="form.card.cvv"
										/>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-5">
					<h5 class="frame-heading mt-4">Client</h5>
					<client interface="payments" />
					<h5 class="frame-heading mt-4">Addresses</h5>
					<p v-for="(address, k) in addresses" :key="k">
						<i class="fas fa-map-pin mr-2"></i>
						{{ address.address }}
						<span
							v-if="
								address.zip &&
								!address.address.includes(address.zip)
							"
							v-text="address.zip"
						></span>
					</p>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-7">
				<h5 class="frame-heading mt-4">Customer Billing Info</h5>
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="form-label" for="first_name"
								>First Name</label
							>
							<input
								type="text"
								id="first_name"
								class="form-control"
								v-model="form.client.first_name"
								maxlength="50"
							/>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="form-label" for="last_name"
								>Last Name</label
							>
							<input
								type="text"
								id="last_name"
								class="form-control"
								v-model="form.client.last_name"
								maxlength="50"
							/>
						</div>
					</div>
					<div class="col-sm-12 mt-2 mb-2">
						<div class="form-group">
							<label class="form-label" for="address"
								>Address</label
							>
							<input
								type="text"
								id="address"
								class="form-control"
								v-model="form.client.address"
								maxlength="60"
							/>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="form-label" for="zip">Zip</label>
							<input
								type="text"
								id="zip"
								class="form-control"
								v-model="form.client.zip"
								maxlength="20"
							/>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="form-label" for="email">Email</label>
							<input
								type="email"
								id="email"
								class="form-control"
								v-model="form.client.email"
							/>
						</div>
					</div>
				</div>

				<div class="d-flex mt-4">
					<div class="progress w-75 mt-2">
						<div
							class="progress-bar"
							:class="{ 'bg-success': progress === 100 }"
							role="progressbar"
							:style="{ width: progress + '%' }"
							:aria-valuenow="progress"
							aria-valuemin="0"
							aria-valuemax="100"
						></div>
					</div>
					<button
						type="button"
						:disabled="progress !== 100"
						class="btn btn-primary waves-effect waves-themed ml-auto"
						@click="submit"
					>
						<span
							v-show="loading"
							class="spinner-border spinner-border-sm"
							role="status"
							aria-hidden="true"
						></span>
						Submit
					</button>
				</div>
			</div>
		</div>
	</form>
</template>

<script>
import { InputNumber } from 'ant-design-vue';
import 'ant-design-vue/lib/input-number/style/index.css';
import Client from '@components/Order/TabOverview/Client';
import { mapGetters } from 'vuex';
import { AxiosHelper } from '@/helpers/axiosHelper';

export default {
	name: 'AuthorizeOnlineCharge',
	components: {
		Client,
		InputNumber,
	},
	props: {
		branch_id: {
			type: Number,
		},
		order_id: {
			type: Number,
		},
	},
	data() {
		return {
			loading: false,
			form: this.emptyForm(),
		};
	},
	computed: {
		...mapGetters({
			waypoints: 'order/waypoints',
		}),
		addresses() {
			if (this.waypoints.records) return this.waypoints.records;
			return [];
		},
		progress() {
			return this.progressCardCharge();
		},
	},
	mounted() {
		this.initMask();
	},
	methods: {
		emptyForm() {
			let q = null,
				order_id = null,
				branch_id = null;
			if (this.order_id) {
				q = {
					id: this.order_id,
					text: `Order #${this.order_id}`,
				};
				order_id = this.order_id;
				branch_id = this.branch_id;
			}

			return {
				type: 'card_charge',
				amount: 0,
				in_total: false,
				branch_id,
				order_id,
				q,
				card: {
					number: null,
					expire: null,
					cvv: null,
				},
				client: {
					first_name: null,
					last_name: null,
					address: null,
					zip: null,
					email: null,
				},
			};
		},
		initMask() {
			Inputmask({
				alias: 'numeric',
				digits: 0,
				min: 1,
				max: 9999,
				digitsOptional: false,
				clearMaskOnLostFocus: false,
				allowMinus: false,
			}).mask('#amount');

			Inputmask({
				mask: [
					'4999 9999 9999 9{1,4}',
					'5999 9999 9999 9999',
					'6999 9999 9999 9999',
					'3999 999999 99999',
				],
				greedy: false,
				keepStatic: false,
				placeholder: '*',
				clearIncomplete: true,
			}).mask('#c_number');

			Inputmask({
				alias: 'datetime',
				inputFormat: 'mm/yy',
				// min: moment().add(1, 'M').format('MM/YY'),
				// max: moment().add(10, 'Y').format('MM/YY'),
				clearIncomplete: true,
			}).mask('#expire');

			Inputmask({
				mask: '9{3,4}',
				keepStatic: true,
				placeholder: '000',
				clearIncomplete: true,
			}).mask('#cvv');

			Inputmask({
				mask: '99999',
				keepStatic: true,
				placeholder: '00000',
				clearIncomplete: true,
			}).mask('#zip');

			Inputmask({
				alias: 'numeric',
				digits: 2,
				min: 0,
				max: 99999.99,
				digitsOptional: true,
				clearMaskOnLostFocus: false,
				placeholder: '0',
				rightAlign: false,
				allowMinus: true,
				// onBeforePaste: function (pastedValue, opts) {
				//     // clear all commas
				//     // console.log('before', pastedValue)
				//     // let after =  pastedValue.replace(/^([0-9\,\.]+)([\.,]+)([0-9]+)$/, "$1 $3").replace(/[\.\,]/, "").replace(" ", ".");
				//     // console.log('after', after)
				//     return pastedValue.replace(",", ".");
				// },
			}).mask(this.$refs.amountValue);
		},
		progressCardCharge() {
			let progress = 0;
			if (this.form.amount >= 1) progress++;
			if (this.form.order_id) progress++;

			if (this.form.card.number) progress++;
			if (this.form.card.expire) progress++;
			if (this.form.card.cvv) progress++;

			if (
				this.form.client.first_name &&
				this.form.client.first_name.length > 2
			)
				progress++;
			if (this.form.client.last_name) progress++;
			if (this.form.client.address) progress++;
			if (this.form.client.zip) progress++;
			if (this.form.client.email && this.form.client.email.length > 6)
				progress++;

			return progress * 10;
		},
		async submit() {
			this.loading = true;

			let data = await AxiosHelper({
				url: '/authorize/process-payment',
				data: this.form,
			});

			App.Forms.showAlert('success', data.msg);

			this.form = this.emptyForm();

			if (this.order_id) {
                VueApp.$refs.overview.recalculate();
                VueApp.$refs.payments.getPayments(); // Обновляем окно оплат
            }

			this.loading = false;
		},
	},
};
</script>

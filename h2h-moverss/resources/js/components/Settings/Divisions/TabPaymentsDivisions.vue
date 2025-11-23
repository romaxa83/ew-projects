<template>
	<div
		class="tab-pane fade"
		:id="`divisions_${record.id}-tab-4`"
		role="tabpanel"
	>
		<h4>Payment methods:</h4>
		<hr />
		<div class="row" v-for="(v, i) in PaymentAccounts">
			<div class="col-md-8 col-lg-4 mb-4">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								<div
									class="custom-control custom-checkbox"
									title="Status"
								>
									<input
										type="checkbox"
										class="custom-control-input"
										:id="`account_${record.id}_${i}_a`"
										v-model="v.is_active"
									/>
									<label class="custom-control-label"></label>
								</div>
							</div>
							<div
								class="input-group-text text-success d-none d-md-block"
							>
								<i class="fal fa-heading"></i>
							</div>
						</div>
						<input
							type="text"
							class="form-control"
							:placeholder="v.id ? 'Title' : 'Add new account'"
							@focusin="paymentAddEmpty"
							@focusout="paymentAddEmpty"
							@keyup="paymentAddEmpty"
							v-model="v.title"
						/>

						<button
							type="button"
							class="btn btn-xs btn-secondary waves-effect waves-themed cursor-default w-80 text-center order"
						>
							<div class="d-flex align-items-center flex-fill">
								<div
									v-show="i"
									class="flex-fill move-icon fal fa-arrow-up cursor-pointer"
									@click="arrowUp(i)"
								></div>
								<div
									v-show="i !== totalPaymentAccounts"
									class="move-icon flex-fill fal fa-arrow-down cursor-pointer"
									:class="{
										'ml-2': i !== totalPaymentAccounts && i,
									}"
									@click="arrowDown(i)"
								></div>
							</div>
						</button>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-lg-4 mb-4">
				<div class="form-group">
					<button
						@click="paymentRemoveItem(i)"
						type="button"
						class="btn btn-danger waves-effect waves-themed"
					>
						<span class="fal fa-times mr-1"></span> Delete
					</button>
				</div>
			</div>
		</div>

		<h4>Authorize Account:</h4>
		<hr />

		<div class="row mb-4">
			<div class="col-4">
				<div class="form-group">
					<label class="form-label" :for="`account_${record.id}_t_pa`"
						>Authorize account name</label
					>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								<div
									class="custom-control custom-checkbox"
									title="Status"
								>
									<input
										type="checkbox"
										class="custom-control-input"
										:id="`account_${record.id}_authorize_a`"
										v-model="record.authorize.active"
									/>
									<label
										class="custom-control-label"
										:for="`account_${record.id}_authorize_a`"
									></label>
								</div>
							</div>
							<div
								class="input-group-text text-success d-none d-md-block"
							>
								<i class="fal fa-heading"></i>
							</div>
						</div>
						<input
							type="text"
							class="form-control"
							placeholder="Account title"
							v-model="record.authorize.title"
						/>
					</div>
				</div>
			</div>
			<div class="col-4">
				<div class="form-group">
					<label
						class="form-label"
						:for="`account_${record.id}_t_login`"
						>Authorize Login</label
					>
					<input
						:id="`account_${record.id}_t_login`"
						type="text"
						class="form-control"
						placeholder="Account login"
						v-model="record.authorize.login"
					/>
				</div>
			</div>
			<div class="col-4">
				<div class="form-group">
					<label class="form-label" :for="`account_${record.id}_t_k`"
						>Authorize Transaction Key</label
					>
					<input
						:id="`account_${record.id}_t_k`"
						type="text"
						class="form-control"
						placeholder="Account transactionKey"
						v-model="record.authorize.transactionKey"
					/>
				</div>
			</div>
		</div>

		<div class="row mb-4">
			<div class="col-4">
				<div class="form-group">
					<label class="form-label" :for="`account_${record.id}_t_pa`"
						>Create Authorize transaction with payment method</label
					>
					<select
						v-model="record.authorize.payment_account_id"
						class="custom-select"
						:id="`account_${record.id}_t_pa`"
					>
						<option :value="null">Not selected</option>
						<option
							v-for="v in existsPaymentAccounts"
							:key="v.id"
							v-bind:value="v.id"
						>
							{{ v.title }}
						</option>
					</select>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';

export default {
	name: 'TabPaymentsDivisions',
	filters: {
		formatDate,
	},
	props: {
		index: {
			type: Number,
			required: true,
		},
		record: {
			type: Object,
			required: true,
		},
	},
	computed: {
		PaymentAccounts() {
			return this.record.payment_accounts.sort((a, b) => a.sort - b.sort);
		},
		existsPaymentAccounts() {
			return this.record.payment_accounts
				.slice()
				.filter((item) => item.id);
		},
		totalPaymentAccounts() {
			return this.record.payment_accounts.length - 1;
		},
	},
	methods: {
		arrowDown(index) {
			this.$emit('paymentsArrowDown', {
				index,
				main_index: this.index,
			});
		},
		arrowUp(index) {
			this.$emit('paymentsArrowUp', {
				index,
				main_index: this.index,
			});
		},
		paymentAddEmpty() {
			this.$emit('paymentAddEmpty');
		},
		paymentRemoveItem(i) {
			let exists = this.PaymentAccounts[i].id > 0;
			if (!exists) {
				this.$emit('paymentRemoveItem', i);
				return;
			}

			Swal.fire({
				title: 'Are you sure?',
				text: 'Remove this item',
				icon: 'warning',
				showCancelButton: true,
				reverseButtons: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, do it!',
			}).then((result) => {
				if (result.value === true) {
					this.$emit('paymentRemoveItem', i);
				}
			});
		},
	},
};
</script>

<style scoped>
.w-80 {
	width: 80px;
}
.move-icon {
	height: 35px;
	padding-top: 12px;
}
</style>

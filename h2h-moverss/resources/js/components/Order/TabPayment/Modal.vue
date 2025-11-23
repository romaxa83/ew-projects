<template>
	<div
		class="modal fade"
		id="payment-modal"
		tabindex="-1"
		role="dialog"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Add payment</h4>
					<button
						type="button"
						class="close"
						data-dismiss="modal"
						aria-label="Close"
					>
						<span aria-hidden="true"
							><i class="fal fa-times"></i
						></span>
					</button>
				</div>
				<div class="modal-body">
					<form>
						<div class="form-group">
							<label for="p_modal_account_id" class="form-label"
								>Payment methods<sup>*</sup></label
							>
							<select
								v-model.number="account_id"
								id="p_modal_account_id"
								type="text"
								class="form-control"
							>
								<option value="null">- Select method</option>
								<option
									v-for="v in accounts"
									:key="v.id"
									v-bind:value="v.id"
								>
									{{ v.title }}
								</option>
							</select>
						</div>
						<div class="form-group">
							<label for="p_modal_description" class="form-label"
								>Description</label
							>
							<textarea
								v-model="description"
								class="form-control"
								id="p_modal_description"
								rows="8"
								placeholder="Optional"
							></textarea>
						</div>
						<div class="form-group">
							<label for="p_modal_amount" class="form-label"
								>Amount</label
							>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">$</span>
								</div>
								<input
									id="p_modal_amount"
									ref="amountValue"
									class="form-control"
									type="text"
									v-model="amount"
								/>
							</div>
						</div>
						<div class="form-group">
							<label for="inTotal" class="form-label"
								>Included</label
							>
							<div class="input-group">
								<div class="custom-control custom-checkbox">
									<input
										type="checkbox"
										class="custom-control-input"
										id="inTotal"
										v-model="in_total"
									/>
									<label
										class="custom-control-label"
										for="inTotal"
									></label>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<div class="mr-auto">
						<button
							type="button"
							class="btn btn-secondary"
							data-dismiss="modal"
						>
							Close
						</button>
					</div>
					<div>
						<button
							@click="submit"
							type="button"
							class="btn btn-primary"
						>
							<span
								v-show="loading"
								class="spinner-border spinner-border-sm"
								role="status"
								aria-hidden="true"
							></span>
							Make payment
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'PaymentModal',
	props: {
		accounts: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			loading: false,
			account_id: null,
			description: null,
			amount: null,
			in_total: true,
		};
	},
	mounted() {
		Inputmask({
			alias: 'numeric',
			digits: 2,
			min: -1000000,
			max: 1000000,
			digitsOptional: true,
			clearMaskOnLostFocus: false,
			placeholder: '0',
			rightAlign: false,
			allowMinus: true,
		}).mask(this.$refs.amountValue);
	},
	methods: {
		submit() {
			if (!this.account_id) {
				App.Forms.showAlert('error', 'Error', 'Choose method');
				return;
			}
			if (!parseInt(this.amount)) {
				App.Forms.showAlert('error', 'Error', 'Incorrect amount');
				return;
			}

			this.loading = true;
			let record = {
				account_id: this.account_id,
				description: this.description,
				amount: this.amount,
				in_total: this.in_total,
			};
			new Promise((resolve, reject) =>
				this.$emit('addPayment', record, { resolve, reject })
			)
				.then(
					() => {
						this.account_id = null;
						this.description = null;
						this.amount = null;
						this.in_total = true;

						$('#payment-modal').modal('hide');
					},
					() => {}
				)
				.finally(() => {
					this.loading = false;
				});
		},
	},
};
</script>

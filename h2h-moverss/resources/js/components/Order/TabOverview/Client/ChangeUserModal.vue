<template>
	<div
		class="modal fade"
		id="client-choose-modal"
		role="dialog"
		aria-hidden="true"
	>
		<div
			class="modal-dialog modal-lg modal-dialog-centered"
			role="document"
		>
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title h4">Choose Client</h5>
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
					<div class="container">
						<div class="modal-body">
							<input type="hidden" v-model.lazy="newClient.id" />
							<input
								type="hidden"
								v-model.lazy="newClient.force"
							/>

							<div class="form-group mb-3">
								<label class="form-label" for="button-addon4"
									>Current Client:</label
								>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"
											><i class="fal fa-user fs-xl"></i
										></span>
									</div>
									<input
										v-model="currentClient"
										id="button-addon4"
										type="text"
										class="form-control"
										disabled
									/>
								</div>
							</div>
							<hr />

							<h3 class="mt-4 mb-4">Create new Client</h3>
							<div class="row mb-3">
								<div class="col">
									<div class="form-group">
										<label
											class="form-label"
											for="newClient_name"
											>First Name <sup>*</sup></label
										>
										<input
											id="newClient_name"
											type="search"
											autocomplete="off"
											v-model="newClient.name"
											ref="email"
											:disabled="disabledInput"
											@keyup.tab="changeFocus('lname')"
											class="form-control client-autocomplete"
											placeholder="First Name"
										/>
									</div>
								</div>
								<div class="col">
									<div class="form-group">
										<label
											class="form-label"
											for="newClient_lname"
											>Last Name</label
										>
										<input
											id="newClient_lname"
											type="search"
											autocomplete="off"
											v-model="newClient.lname"
											ref="lname"
											:disabled="disabledInput"
											@keyup.tab="changeFocus('phone')"
											class="form-control client-autocomplete"
											placeholder="Last Name"
										/>
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col">
									<div class="form-group">
										<label
											class="form-label"
											for="newClient_phone"
											>Phone</label
										>
										<input
											id="newClient_phone"
											type="search"
											autocomplete="off"
											class="form-control client-autocomplete"
											v-model="newClient.phone"
											ref="phone"
											@keyup.tab="changeFocus('email')"
											:disabled="disabledInput"
											placeholder="Phone"
										/>
									</div>
								</div>
								<div class="col">
									<div class="form-group">
										<label
											class="form-label"
											for="newClient_email"
											>Email</label
										>
										<input
											id="newClient_email"
											type="search"
											autocomplete="off"
											class="form-control client-autocomplete"
											v-model="newClient.email"
											ref="email"
											:disabled="disabledInput"
											@keyup.enter="submit"
											placeholder="Email"
										/>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button
						type="button"
						class="btn btn-secondary mr-auto"
						data-dismiss="modal"
					>
						Close
					</button>
					<button
						v-show="showHideBtn"
						@click="reset()"
						type="button"
						class="btn btn-info"
						name="reset"
					>
						Reset contact
					</button>
					<button
						@click="submit()"
						type="button"
						class="btn btn-primary"
						:disabled="!showHideBtn"
					>
						<span
							v-show="loading"
							class="spinner-border spinner-border-sm"
							role="status"
							aria-hidden="true"
						></span>
						{{ loading ? 'Saving' : 'Save' }} changes
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

let order_id = document.getElementById('order_id').textContent;

export default {
	name: 'OrderClientChangeUserModal',
	data() {
		return {
			loading: false,
			showHideBtn: false,
			disabledInput: false,
			newClient: this.emptyClient(),
		};
	},
	computed: {
		currentClient() {
			if (this.showHideBtn)
				return !this.newClient.id
					? `Create: ${this.newClient.name} ${this.newClient.lname}`
					: `Change to: [${this.newClient.id}] ${this.newClient.name} ${this.newClient.lname}`;

			return this.currentClientID
				? `[${this.currentClientID}] ${this.client.name} ${this.client.lname}`
				: 'Not set';
		},
		newClientName() {
			return (this.newClient.name + ' ' + this.newClient.lname).trim();
		},
		...mapGetters({
			client: 'clients/record',
			currentClientID: 'clients/clientId',
		}),
	},
	watch: {
		newClientName(val) {
			if (val) {
				this.showHideBtn = true;
			}
		},
	},
	mounted() {
		$('#client-choose-modal').on('shown.bs.modal', () => {
			this.initData();
			this.$refs.email.focus();
		});
	},
	methods: {
		changeFocus(toRef) {
			this.$refs[toRef].focus();
		},
		emptyClient() {
			return {
				id: null,
				name: '',
				lname: '',
				phone: null,
				email: null,
				force: false,
			};
		},
		initData() {
			this.newClient = this.emptyClient();
			App.Order.clientAutocomplete(this);

			Inputmask({ mask: '(999) 999-9999' }).mask('#newClient_phone');
			// Inputmask("email", {jitMasking: true}).mask('#newClient_email');
			Inputmask('email').mask('#newClient_email');
		},
		reset() {
			this.newClient = this.emptyClient();
			this.showHideBtn = false;
			this.disabledInput = false;
		},
		submit() {
			axios
				.post('/orders/change-client', {
					order_id,
					...this.newClient,
				})
				.then(async (resp) => {
					if (resp.data.success === true) {
						await this.$store.dispatch(
							'clients/fetchClient',
							resp.data.client_id
						);
						this.$store.commit(
							'order/changeClientId',
							resp.data.client_id
						);

						$('#client-choose-modal').modal('hide');
						this.reset();
					} else {
						throw {
							response: {
								data: resp.data,
							},
						};
					}
				})
				.catch((error) => {
					let cloneExists = false;
					Object.values(error.response.data.errors).forEach(function (
						item,
						k
					) {
						let key = Object.keys(error.response.data.errors)[k];
						if (key === 'custom_phone' || key === 'custom_email') {
							cloneExists = true;
						}
					});

					// Есть кастомные сообщения
					if (cloneExists) {
						Swal.fire({
							target: document.getElementById(
								'client-choose-modal'
							),
							title: 'Create new client?',
							text: 'Client is exists with this phone or Email',
							icon: 'warning',
							showCancelButton: true,
							reverseButtons: true,
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#d33',
							confirmButtonText: 'Yes, do it!',
						}).then((result) => {
							if (result.value === true) {
								this.newClient.force = true;
								this.submit();
							}
						});
					} else {
						App.Forms.simpleErrors(error.response.data);
					}
				});
		},
	},
};
</script>

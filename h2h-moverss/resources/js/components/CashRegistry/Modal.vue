<template>
	<div
		class="modal fade"
		id="operation-modal"
		tabindex="-1"
		role="dialog"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Add Operation</h4>
					<button
						type="button"
						class="close"
						data-dismiss="modal"
						aria-label="Close"
					>
						<span aria-hidden="true">
							<i class="fal fa-times"></i>
						</span>
					</button>
				</div>
				<div class="modal-body">
					<form>
						<div class="form-group">
							<label
								for="p_modal_operation_type"
								class="form-label"
							>
								Operation Type<sup>*</sup>
							</label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="fal fa-money-check-alt"></i>
									</span>
								</div>
								<vue-select2
									v-if="types.loaded"
									v-model="form.type"
									:config="types.config"
									class="select-control"
									id="p_modal_operation_type"
								>
									<option
										v-for="type of types.list"
										:key="type.id"
										:value="type.id"
									>
										{{ type.name }}
									</option>
								</vue-select2>
								<div v-else class="form-control">
									Loading...
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="p_modal_employee_id" class="form-label">
								Foreman<sup>*</sup>
							</label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<svg
											width="16"
											height="16"
											viewBox="0 0 17 17"
										>
											<use
												:href="`${this.svgSpritePath}#person`"
											/>
										</svg>
									</span>
								</div>
								<vue-select2
									v-if="foremans.loaded"
									v-model="form.employee_id"
									:config="foremans.config"
									class="select-control"
									id="p_modal_employee_id"
								>
									<option
										v-for="foreman of foremans.list"
										:key="foreman.id"
										:value="foreman.id"
									>
										{{ foreman.name }}
									</option>
								</vue-select2>
								<div v-else class="form-control">
									Loading...
								</div>
							</div>
						</div>
						<div
							class="form-group"
							v-if="form.type === 'cash_transfer'"
						>
							<label
								for="p_modal_to_employee_id"
								class="form-label"
							>
								To Foreman<sup>*</sup>
							</label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<svg
											width="16"
											height="16"
											viewBox="0 0 17 17"
										>
											<use
												:href="`${this.svgSpritePath}#person`"
											/>
										</svg>
									</span>
								</div>
								<vue-select2
									v-if="foremans.loaded"
									v-model="form.to_employee_id"
									:config="toForemanConfig"
									class="select-control"
									id="p_modal_to_employee_id"
								>
									<option
										v-for="foreman of foremans.list"
										:key="foreman.id"
										:value="foreman.id"
									>
										{{ foreman.name }}
									</option>
								</vue-select2>
								<div v-else class="form-control">
									Loading...
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="p_modal_date" class="form-label">
								Date/Time<sup>*</sup>
							</label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="fal fa-calendar"></i>
									</span>
								</div>
								<input
									id="p_modal_date"
									class="form-control datetime-control"
									type="text"
									v-model="form.insert_at"
									ref="insertAt"
									placeholder="Select Date/Time"
								/>
							</div>
						</div>
						<div class="form-group">
							<label for="p_modal_sum" class="form-label">
								Sum<sup>*</sup>
							</label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="fal fa-dollar-sign"></i>
									</span>
								</div>
								<input
									id="p_modal_sum"
									ref="sumValue"
									class="form-control"
									type="text"
									v-model="form.sum"
								/>
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
							Apply
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { AxiosHelper } from '@/helpers/axiosHelper';
import VueSelect2 from '@components/VueSelect2.vue';
import { fixBsModal } from '@/fix-bs-modal';
import moment from 'moment';

export default {
	name: 'OperationModal',
	components: { VueSelect2 },
	props: {},
	data() {
		return {
			loading: false,
			form: {
				employee_id: null,
				insert_at: null,
				sum: null,
				type: null,
				to_employee_id: null,
			},
			foremans: {
				list: [],
				loaded: false,
				config: {
					placeholder: 'Foreman',
					allowClear: false,
					minimumResultsForSearch: 10,
				},
			},
			types: {
				list: [],
				loaded: false,
				config: {
					placeholder: 'Operation Type',
					allowClear: false,
					minimumResultsForSearch: 10,
				},
			},
			svgSpritePath: '/images/foreman-cash-sprite.svg?cache=1',
			fpInstance: null,
		};
	},
	mounted() {
		this.initInputs();

		$('#operation-modal').on('shown.bs.modal', async () => {
			if (!this.types.loaded) {
				this.loadTypes();
			}
			if (!this.foremans.loaded) {
				await this.loadForemans();
			}

			fixBsModal();
		});
	},
	computed: {
		toForemanConfig() {
			return {
				...this.foremans.config,
				placeholder: 'To Foreman',
			};
		},
	},
	methods: {
		async loadForemans() {
			await AxiosHelper({
				url: '/reports/foreman-cash-report/foremans',
			})
				.then(({ data }) => {
					this.foremans.list = Object.entries(data).map(
						([key, value]) => ({
							id: key,
							name: value,
						})
					);
				})
				.finally(() => {
					this.foremans.loaded = true;
				});
		},
		loadTypes() {
			axios
				.get('/cash-registry/operation-types')
				.then(({ data }) => {
					this.types.list = Object.entries(
						data.records?.for_form || {}
					).map(([key, value]) => ({
						id: key,
						name: value,
					}));
				})
				.finally(() => {
					this.types.loaded = true;
				});
		},
		initInputs() {
			Inputmask({
				alias: 'numeric',
				digits: 2,
				digitsOptional: true,
				clearMaskOnLostFocus: false,
				placeholder: '0',
				rightAlign: false,
				allowMinus: false,
			}).mask(this.$refs.sumValue);

			this.fpInstance = window.flatpickr(this.$refs.insertAt, {
				enableTime: true,
				dateFormat: 'Y-m-d H:i:S',
				altInput: true,
				altFormat: 'M j, Y h:i K',
				minDate: moment().subtract(30, 'days').toDate(),
				maxDate: 'today',
				minuteIncrement: 5,
				rangesOnly: false,
			});
		},
		submit() {
			const isCashTransferType = this.form.type === 'cash_transfer';

			if (!this.form.type) {
				App.Forms.showAlert('error', 'Error', 'Choose operation type');
				return;
			}
			if (!this.form.employee_id) {
				App.Forms.showAlert('error', 'Error', 'Choose foreman');
				return;
			}
			if (isCashTransferType) {
				if (!this.form.to_employee_id) {
					App.Forms.showAlert(
						'error',
						'Error',
						'Choose foreman recipient'
					);
					return;
				}
				if (
					this.form.employee_id &&
					this.form.to_employee_id &&
					this.form.employee_id === this.form.to_employee_id
				) {
					App.Forms.showAlert(
						'error',
						'Error',
						'Foreman recipient must be different'
					);
					return;
				}
			}
			if (!this.form.insert_at) {
				App.Forms.showAlert('error', 'Error', 'Choose date/time');
				return;
			}
			if (!parseInt(this.form.sum)) {
				App.Forms.showAlert(
					'error',
					'Error',
					'Incorrect sum, should be greater then 0'
				);
				return;
			}

			this.loading = true;
			let record = {
				employee_id: this.form.employee_id,
				to_employee_id: isCashTransferType
					? this.form.to_employee_id
					: undefined,
				insert_at: moment(this.form.insert_at).format(
					'YYYY-MM-DD HH:mm:ss'
				),
				sum: this.form.sum,
				type: this.form.type,
			};
			new Promise((resolve, reject) =>
				this.$emit('addOperation', record, { resolve, reject })
			)
				.then(
					() => {
						this.form = {
							employee_id: null,
							insert_at: null,
							sum: null,
							type: null,
							to_employee_id: null,
						};

						$('#operation-modal').modal('hide');
						this.fpInstance?.clear();
						App.Forms.showAlert('success', 'Operation added');
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

<style lang="scss">
.select-control {
	flex: 1 1;

	.select2-selection {
		border-top-left-radius: 0;
		border-bottom-left-radius: 0;
	}
}

.select2-container {
	z-index: 2060;

	&.select2-container--open,
	&.select2-container--focus {
		.select2-selection--single,
		.select2-dropdown {
			border-color: #4679cc !important;
		}

		.select2-search--dropdown {
			&::before {
				color: #4679cc;
			}
		}

		.select2-results__option--highlighted[aria-selected] {
			background-color: #4679cc;
		}
	}
}

.input-group-text {
	min-width: 2.94rem;
	justify-content: center;
}

.datetime-control {
	background-color: transparent !important;
	margin-left: 0 !important;
}
</style>

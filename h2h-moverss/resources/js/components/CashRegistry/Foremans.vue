<template>
	<div>
		<div class="row align-items-center mb-3">
			<div class="col">
				<h1 class="subheader-title">Cash Registry</h1>
			</div>
			<div class="col-auto d-flex">
				<button
					type="button"
					class="btn btn-outline-primary waves-effect waves-themed"
					@click="operationModal()"
				>
					Add Operation
				</button>
			</div>
		</div>

		<div class="table-container">
			<table class="table table-bordered m-0">
				<thead>
					<tr>
						<th width="50%">Name</th>
						<th width="50%">Cash on hands, $</th>
					</tr>
				</thead>
			</table>
			<table class="table table-hover table-bordered table-total m-0">
				<tbody>
					<tr>
						<td width="50%" class="text-lg">TOTAL</td>
						<td width="50%" class="text-lg">
							{{ total !== null ? formatNumber(total) : '--' }}
						</td>
					</tr>
				</tbody>
			</table>
			<table class="table table-hover table-bordered table-content m-0">
				<tbody>
					<tr v-for="record of records">
						<td width="50%">
							<a
								:href="`/reports/foreman-cash-report?type=processed&user=${record.employee_id}`"
								class="fw-500"
								target="_blank"
							>
								{{ record.employee.name }}
								{{ record.employee.l_name }}
							</a>
						</td>
						<td width="50%" class="fw-500">
							{{ formatNumber(record.cash_on_hand) }}
						</td>
					</tr>
				</tbody>
			</table>

			<div
				v-if="loading"
				class="frame-wrap position-absolute w-100 h-100 opacity-50"
			>
				<div
					class="w-100 d-flex justify-content-center align-items-center"
				>
					<div
						class="spinner-border text-info position-absolute"
						role="status"
					>
						<span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>
		</div>

		<operation-modal @addOperation="addOperation" />
	</div>
</template>

<script>
import OperationModal from './Modal';

export default {
	name: 'CashRegistryForemans',
	components: {
		OperationModal,
	},
	data() {
		return {
			loading: false,
			records: [],
			total: null,
		};
	},
	mounted() {
		this.loadData();
	},
	methods: {
		formatNumber(num) {
			return Number(num).toLocaleString('en-US', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			});
		},
		loadData() {
			this.loading = true;
			axios
				.get('/cash-registry/get-foremans')
				.then((resp) => {
					if (resp.data.success === true) {
						this.records = resp.data.records || [];
						this.total = resp.data.meta?.total ?? null;
					} else {
						throw {
							response: {
								data: resp.data,
							},
						};
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				})
				.finally(() => {
					this.loading = false;
				});
		},
		addOperation(record, promise = null) {
			axios
				.post('/cash-registry/add-operation', {
					...record,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.records = resp.data.records;
						this.total = resp.data.meta?.total ?? null;
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
		operationModal() {
			$('#operation-modal').modal('show');
		},
	},
};
</script>

<style lang="scss">
.text-lg {
	font-size: 1rem;
	font-weight: 700;
}

.frame-wrap.position-absolute {
	top: 0;
	z-index: 2;

	.spinner-border {
		top: 50%;
	}
}

.table-container {
	position: relative;
	min-height: 17.65rem;
}

.table-total {
	position: sticky;
	top: 70px;
	z-index: 2;
	background-color: #fff;
}

.table-content {
	border-top: 0;

	tr {
		&:first-of-type {
			td {
				border-top: 0;
			}
		}
	}
}
</style>

<template>
	<div>
		<div class="row align-items-center mb-3">
			<div class="col">
				<h1 class="subheader-title">Cash Registry History</h1>
			</div>
			<div class="col-auto d-flex flex-wrap align-items-center">
				<UiLoadingButton
					theme="outline-primary"
					:loading="exporting"
					:disabled="!exportAvailable"
					@click="exportFile"
				>
					Export
				</UiLoadingButton>
			</div>
		</div>

		<div class="row filter-row mb-3">
			<div class="col filter-col">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">
							<i class="fal fa-calendar"></i>
						</span>
					</div>
					<input
						type="text"
						class="form-control datetime-control"
						placeholder="Period"
						ref="dateRange"
					/>
				</div>
			</div>
			<div class="col filter-col">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">
							<i class="fal fa-money-check-alt"></i>
						</span>
					</div>
					<vue-select2
						v-if="types.loaded"
						v-model="filter.type"
						:config="types.config"
						class="select-control"
					>
						<option
							v-for="type of types.list"
							:key="type.id"
							:value="type.id"
						>
							{{ type.name }}
						</option>
					</vue-select2>
					<div v-else class="form-control">Loading...</div>
				</div>
			</div>
			<div class="col filter-col">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">
							<svg width="16" height="16" viewBox="0 0 17 17">
								<use :href="`${this.svgSpritePath}#person`" />
							</svg>
						</span>
					</div>
					<vue-select2
						v-if="foremans.loaded"
						v-model="filter.employee"
						:config="foremans.config"
						class="select-control"
					>
						<option
							v-for="foreman of foremans.list"
							:key="foreman.id"
							:value="foreman.id"
						>
							{{ foreman.name }}
						</option>
					</vue-select2>
					<div v-else class="form-control">Loading...</div>
				</div>
			</div>
			<div class="col col-auto">
				<UiLoadingButton
					type="submit"
					theme="success"
					:loading="loading"
					:disabled="loading || resetting"
					@click="loadData(1)"
				>
					Apply
				</UiLoadingButton>
			</div>
			<div class="col col-auto">
				<UiLoadingButton
					type="submit"
					theme="secondary"
					:loading="resetting"
					:disabled="loading || resetting"
					@click="resetFilter"
				>
					Reset
				</UiLoadingButton>
			</div>
		</div>

		<div class="position-relative">
			<table class="table table-hover table-striped table-bordered m-0">
				<thead>
					<tr>
						<th width="200px">Date/Time</th>
						<th width="300px">Name</th>
						<th width="300px">Operation type</th>
						<th width="300px">Foreman</th>
						<th width="150px">Sum, $</th>
					</tr>
				</thead>
				<tbody>
					<tr
						v-for="item of data"
						:style="{
							color: operationColors[item.type] ?? undefined,
						}"
					>
						<td>{{ formatDate(item.insert_at) }}</td>
						<td>{{ item.executor.name }}</td>
						<td>{{ getTypeLabel(item.type) }}</td>
						<td>{{ item.foreman.name }}</td>
						<td class="fw-500">{{ formatNumber(item.sum) }}</td>
					</tr>
					<tr
						v-if="data.length === 0 && !loading"
						class="empty-result"
					>
						<td colspan="5">No data available in table</td>
					</tr>
				</tbody>
			</table>

			<div v-if="paginate.total" class="row mt-3">
				<div class="col-sm-12 col-md-5">
					<p class="mb-0">
						Showing {{ paginate.from }} to {{ paginate.to }} of
						{{ paginate.total }} entries
					</p>
				</div>
				<div class="col-sm-12 col-md-7">
					<pagination
						:data="paginate"
						align="right"
						:show-disabled="true"
						@pagination-change-page="loadData"
					/>
				</div>
			</div>

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
	</div>
</template>

<script>
import pagination from 'laravel-vue-pagination';
import moment from 'moment';
import { AxiosHelper } from '@/helpers/axiosHelper';
import { BFormRadioGroup } from 'bootstrap-vue';
import UiLoadingButton from '@ui/Button/LoadingButton.vue';
import VueSelect2 from '@components/VueSelect2.vue';
import { download } from '@/helpers/download';
import { DateService } from '@/services/date';

export default {
	name: 'CashRegistryOperations',
	components: {
		VueSelect2,
		UiLoadingButton,
		BFormRadioGroup,
		pagination,
	},
	data() {
		return {
			loading: false,
			resetting: false,
			exporting: false,
			paginate: {},
			filter: {
				type: null,
				startDate: null,
				endDate: null,
				employee: null,
			},
			foremans: {
				list: [],
				loaded: false,
				config: {
					placeholder: 'Foreman',
					allowClear: true,
					minimumResultsForSearch: 10,
				},
			},
			types: {
				list: [],
				loaded: false,
				config: {
					placeholder: 'Operation Type',
					allowClear: true,
					minimumResultsForSearch: 10,
				},
			},
			svgSpritePath: '/images/foreman-cash-sprite.svg?cache=1',
			fpInstance: null,
		};
	},
	async mounted() {
		this.loading = true;
		await this.loadTypes();
		this.loadForemans();
		this.loadData();
		this.initInputs();
	},
	computed: {
		data() {
			return this.paginate.data || [];
		},
		exportAvailable() {
			return !this.loading && !this.resetting && !this.exporting;
		},
		operationColors() {
			return {
				cash_collection: '#ff0000',
				cash_disbursement: '#008000',
				payroll_cash_collected: '#008000',
				payroll_cash_paid: '#ff0000',
				cash_transfer: '#CD853F',
			};
		},
	},
	methods: {
		formatNumber(num) {
			return Number(num).toLocaleString('en-US', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			});
		},
		formatDate(date) {
			return moment(date).utc().format('MM-DD-YYYY hh:mm A');
		},
		getTypeLabel(type) {
			const result = this.types.list.find((item) => item.id === type);
			return result?.name || '--';
		},
		loadData(page = 1, resetting = false, filterValues) {
			if (resetting) {
				this.resetting = true;
			} else {
				this.loading = true;
			}
			axios
				.get('/cash-registry/get-operations', {
					params: { ...this.getFilter(filterValues), page },
				})
				.then(({ data }) => {
					this.paginate = data.records;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				})
				.finally(() => {
					if (resetting) {
						this.resetting = false;
					} else {
						this.loading = false;
					}
				});
		},
		loadForemans() {
			AxiosHelper({
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
		async loadTypes() {
			await axios
				.get('/cash-registry/operation-types')
				.then(({ data }) => {
					this.types.list = Object.entries(
						data.records?.for_filter || {}
					).map(([key, value]) => ({
						id: key,
						name: value,
					}));
				})
				.finally(() => {
					this.types.loaded = true;
				});
		},
		exportFile() {
			this.exporting = true;
			axios
				.get('/cash-registry/operation-excel', {
					params: this.getFilter(),
				})
				.then(({ data }) => download(data.link))
				.finally(() => {
					this.exporting = false;
				});
		},
		getFilter(filterValues) {
			const { type, startDate, endDate, employee } =
				filterValues ?? this.filter;

			return {
				type: type || null,
				start_date: startDate
					? moment(startDate)
							.startOf('day')
							.format('YYYY-MM-DD HH:mm:ss')
					: null,
				end_date: endDate
					? moment(endDate).endOf('day').format('YYYY-MM-DD HH:mm:ss')
					: null,
				employee_id: employee || null,
			};
		},
		resetFilter() {
			const newFilter = {
				startDate: null,
				endDate: null,
				type: null,
				employee: null,
			};
			this.loadData(1, true, newFilter);
			this.filter = newFilter;
			this.fpInstance?.clear();
		},
		initInputs() {
			this.fpInstance = window.flatpickr(this.$refs.dateRange, {
				// appendTo: this.$refs.dateRangeContainer,
				mode: 'range',
				dateFormat: 'Y-m-d',
				altInput: true,
				altFormat: 'M j, Y',
				// minDate: moment().subtract(30, 'days').toDate(),
				maxDate: moment().endOf('day').toDate(),
				plugins: myFlatpickrPlugins,
				ranges: {
					Today: [new Date(), new Date()],
					Yesterday: [
						moment().subtract(1, 'days').toDate(),
						moment().subtract(1, 'days').toDate(),
					],
					'Last 30 Days': [
						moment().subtract(29, 'days').toDate(),
						new Date(),
					],
					'Last 90 Days': [
						moment().subtract(89, 'days').toDate(),
						new Date(),
					],
					'Last 180 Days': [
						moment().subtract(179, 'days').toDate(),
						new Date(),
					],
					'Last 365 Days': [
						moment().subtract(364, 'days').toDate(),
						new Date(),
					],
				},
				rangesOnly: false,
				rangesAllowCustom: true,
				rangesCustomLabel: 'Custom Range',
				locale: {
					rangeSeparator: ' - ',
				},
				onValueUpdate: (selectedDates, formattedValue) => {
					const [start, end] = selectedDates;
					if (start && end) {
						this.filter.startDate = selectedDates[0] || null;
						this.filter.endDate = selectedDates[1] || null;
					}
				},
			});
		},
	},
};
</script>

<style lang="scss">
.select-control {
	flex: 1 1 auto;
	max-width: 212px;

	.select2-selection {
		border-top-left-radius: 0;
		border-bottom-left-radius: 0;
	}
}

.select2-container {
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

.frame-wrap.position-absolute {
	top: 0;
	z-index: 2;

	.spinner-border {
		top: 50%;
	}
}

.filter-row {
	margin-right: -6px;
	margin-left: -6px;

	.col {
		padding-right: 6px;
		padding-left: 6px;
	}
}

.filter-col {
	flex: 0 1 272px;
}

.input-group {
	flex-wrap: nowrap;
}

.input-group-text {
	min-width: 48px;
	padding: 0.5rem 0.85rem;
}

.datetime-control {
	background-color: transparent !important;
	margin-left: 0 !important;
}

.btn-success,
.btn-secondary {
	&._loading {
		.spinner-border {
			color: #fff !important;
		}
	}
}

.flatpickr-calendar {
	&.flatpickr-has-predefined-ranges {
		display: none !important;

		&.open {
			display: grid !important;
		}
	}
}

.empty-result {
	background-color: #fff !important;

	td {
		color: #fd3995;
		font-size: 1rem;
		text-align: center;
	}
}
</style>

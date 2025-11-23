<template>
	<div>
		<div class="row mb-3">
			<div class="col">
				<h1 class="subheader-title">Foreman cash report</h1>
			</div>
			<!--			<div class="col-auto d-flex flex-wrap align-items-center">-->
			<!--				<UiLoadingButton-->
			<!--					theme="outline-primary"-->
			<!--					:loading="exportingExcel"-->
			<!--					:disabled="!exportExcelAvailable"-->
			<!--					@click="exportExcel"-->
			<!--					class="mr-3"-->
			<!--				>-->
			<!--					Export Excel-->
			<!--				</UiLoadingButton>-->

			<!--				<UiLoadingButton-->
			<!--					theme="outline-primary"-->
			<!--					:loading="exportingCSV"-->
			<!--					:disabled="!exportCSVAvailable"-->
			<!--					@click="exportCSV"-->
			<!--				>-->
			<!--					Export CSV-->
			<!--				</UiLoadingButton>-->
			<!--			</div>-->
		</div>

		<div class="row filter-row mb-3">
			<div class="col col-auto">
				<b-form-radio-group
					v-model="filter.type"
					:options="[
						{ text: 'All', value: 'all' },
						{ text: 'Processed', value: 'processed' },
						{ text: 'Unprocessed', value: 'unprocessed' },
					]"
					button-variant="outline-primary"
					name="payment-type"
					buttons
				></b-form-radio-group>
			</div>
			<div class="col filter-col">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text fs-xl">
							<i class="fal fa-calendar"></i>
						</span>
					</div>
					<input
						type="text"
						class="form-control"
						placeholder="Select date"
						id="dateRangePicker"
					/>
				</div>
			</div>
			<div class="col filter-col">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text fs-xl">
							<svg width="16" height="16" viewBox="0 0 17 17">
								<use :href="`${this.svgSpritePath}#person`" />
							</svg>
						</span>
					</div>
					<vue-select2
						v-if="managersAreLoaded"
						v-model="filter.user"
						:config="managersSelectConfig"
						class="select-control"
					>
						<option
							v-for="manager of managers"
							:key="manager.id"
							:value="manager.id"
						>
							{{ manager.name }}
						</option>
					</vue-select2>
					<div v-else class="form-control">Loading...</div>
				</div>
			</div>
			<div class="col col-auto">
				<UiLoadingButton
					type="submit"
					:theme="filterSubmitAvailable ? 'success' : 'default'"
					:loading="loading"
					:disabled="!filterSubmitAvailable"
					@click="loadReports(1)"
				>
					Apply
				</UiLoadingButton>
			</div>
		</div>

		<div class="position-relative">
			<div class="row mb-4">
				<div class="col">
					<div class="value-group">
						<p>Previous balance, $</p>
						<p>{{ balancePreviousFormatted }}</p>
					</div>
				</div>
				<div class="col">
					<div class="value-group">
						<p>Balance in the end of period, $</p>
						<p>{{ balancePeriodFormatted }}</p>
					</div>
				</div>
			</div>

			<div
				v-if="!filter.user || !paginate.data"
				class="report-foreman-cash-empty"
			>
				<svg width="56" height="56" viewBox="0 0 17 17">
					<use :href="`${this.svgSpritePath}#person`" />
				</svg>
				<p class="fw-500 mb-2 mt-4">
					Choose foreman to display the data for
				</p>
				<p>Use the filters above to get the required information</p>
			</div>
			<div
				v-if="paginate.data && !paginate.data.length"
				class="report-foreman-cash-empty"
			>
				<i class="fal fa-file-times"></i>
				<p class="fw-500 mb-2 mt-4">No data to display</p>
				<p>Try to change the filters above</p>
			</div>

			<PayrollEmployee
				v-for="payroll of paginate.data"
				:payroll="payroll"
				:can-view-employee-card="true"
				:list-variant="true"
				:key="payroll.id"
				clas="mb-3"
			/>

			<div v-if="paginate.total" class="row">
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
						@pagination-change-page="loadReports"
					/>
				</div>
			</div>

			<div
				v-if="loading"
				class="frame-wrap position-absolute w-100 h-100 opacity-50"
				style="top: 0; z-index: 4"
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
		</div>
	</div>
</template>

<script>
import moment from 'moment';
import { BFormRadioGroup } from 'bootstrap-vue';
import { AxiosHelper } from '@/helpers/axiosHelper';
import { download } from '@/helpers/download';
import VueSelect2 from '@components/VueSelect2.vue';
import UiLoadingButton from '@ui/Button/LoadingButton.vue';
import pagination from 'laravel-vue-pagination';
import PayrollEmployee from '@components/Order/TabPayment/PayrollEmployee';

const allowedFilterTypes = ['all', 'processed', 'unprocessed'];

export default {
	name: 'ReportForemanCash',
	components: {
		VueSelect2,
		UiLoadingButton,
		pagination,
		BFormRadioGroup,
		PayrollEmployee,
	},
	data() {
		return {
			loading: false,
			exportingCSV: false,
			exportingExcel: false,
			paginate: {},
			managers: [],
			managersAreLoaded: false,
			managersSelectConfig: {
				placeholder: 'Foreman',
				allowClear: false,
			},
			filter: {
				type: 'all', // all | processed | unprocessed
				startDate: moment().startOf('month'),
				endDate: moment(),
				user: '',
			},
			balance: {
				previous: null,
				endPeriod: null,
			},
			svgSpritePath: '/images/foreman-cash-sprite.svg?cache=2',
		};
	},
	mounted() {
		this.checkURLParams();
		this.loadManagers();
        setTimeout(() => this.initInputs());
	},
	computed: {
		filterSubmitAvailable() {
			return !!this.filter.user && !this.loading;
		},
		exportExcelAvailable() {
			return this.filterSubmitAvailable && !this.exportingExcel;
		},
		exportCSVAvailable() {
			return this.filterSubmitAvailable && !this.exportingCSV;
		},
		balancePreviousFormatted() {
			return typeof this.balance.previous === 'number'
				? this.formatNumber(this.balance.previous)
				: '-';
		},
		balancePeriodFormatted() {
			return typeof this.balance.endPeriod === 'number'
				? this.formatNumber(this.balance.endPeriod)
				: '-';
		},
	},
	methods: {
		formatNumber(num) {
			return Number(num).toLocaleString('en-US', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			});
		},
		initInputs() {
			$('#dateRangePicker').daterangepicker(
				{
					minDate: moment('2020-01-01', 'YYYY-MM-DD'),
					maxDate: moment(),
					startDate: this.filter.startDate,
					endDate: this.filter.endDate,
					drops: 'auto',
					locale: {
						format: 'MMM DD, YYYY',
					},
					maxSpan: {
						days: 365,
					},
					alwaysShowCalendars: true,
					ranges: {
						Today: [moment(), moment()],
						Yesterday: [
							moment().subtract(1, 'days'),
							moment().subtract(1, 'days'),
						],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [
							moment().subtract(30, 'days'),
							moment(),
						],
					},
				},
				(start, end) => {
					this.filter.startDate = start;
					this.filter.endDate = end;
				}
			);
		},
		async loadManagers() {
			await AxiosHelper({
				url: window.location.pathname + '/foremans',
			})
				.then(({ data }) => {
					this.managers = Object.entries(data).map(
						([key, value]) => ({
							id: key,
							name: value,
						})
					);
				})
				.finally(() => {
					this.managersAreLoaded = true;
				});
		},
		async loadReports(page = 1, filterValues) {
			this.loading = true;
			await AxiosHelper({
				url: window.location.pathname + '?page=' + page,
				data: this.getFilter(filterValues),
			})
				.then(({ paginate, meta }) => {
					this.paginate = paginate;
					this.balance = {
						previous: meta?.previous_balance ?? null,
						endPeriod: meta?.balance_end_period ?? null,
					};
				})
				.finally(() => {
					this.loading = false;
				});
		},
		async exportCSV() {
			this.exportingCSV = true;

			await AxiosHelper({
				url: window.location.pathname + '/export-csv',
				data: this.getFilter(),
			})
				.then(({ link }) => download(link))
				.finally(() => {
					this.exportingCSV = false;
				});
		},
		async exportExcel() {
			this.exportingExcel = true;
			await AxiosHelper({
				url: window.location.pathname + '/export-excel',
				data: this.getFilter(),
			})
				.then(({ link }) => download(link))
				.finally(() => {
					this.exportingExcel = false;
				});
		},
		async checkURLParams() {
			const url = new URL(window.location);
			const type = url.searchParams.get('type');
			const user = url.searchParams.get('user');

			if (!user) return;

			const newFilter = {
				...this.filter,
				type: type && allowedFilterTypes.includes(type) ? type : 'all',
				user,
			};
			this.filter = newFilter;
			await this.loadReports(1, newFilter);
			window.history.pushState({}, null, window.location.pathname);
		},
		getFilter(filterValues) {
			const { type, startDate, endDate, user } =
				filterValues ?? this.filter;
			return {
				type,
				start_range: startDate ? startDate.format('YYYY-MM-DD') : null,
				end_range: endDate ? endDate.format('YYYY-MM-DD') : null,
				employee_id: user || null,
			};
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

.balance-label {
	flex: 0 1 224px;
}

.value-group {
	display: flex;
	align-items: center;
	padding: 4px 0;

	p {
		margin-bottom: 0;
	}

	> :first-child {
		flex: 0 1 224px;
		font-size: 0.755rem;
		color: var(--color-text-secondary);
	}

	> :last-child {
		flex: 1 1 160px;
		color: var(--color-text-primary);
		font-weight: 500;
	}
}

.report-foreman-cash-empty {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding-top: 53px;
	color: #adb5bd;

	i {
		font-size: 56px;
	}

	p {
		&:first-of-type {
			font-size: 1rem;
			line-height: 1.17;
		}
	}
}

.input-group {
	flex-wrap: nowrap;
}

.input-group-text {
	min-width: 48px;
	padding: 0.5rem 0.85rem;
}
</style>

<template>
	<div>
		<div class="row mb-3">
			<div class="col col-6 col-xl-4">
				<div class="form-group">
					<label class="form-label">Month</label>
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
			</div>
			<div class="col col-6 col-xl-4">
				<div class="form-group">
					<label class="form-label">Manager</label>
					<vue-select2
						v-if="managersAreLoaded"
						v-model="form.user_id"
						:config="managersSelectConfig"
					>
						<option
							v-for="v in managers"
							:key="v.id"
							v-bind:value="v.id"
						>
							{{ v.name }}
						</option>
					</vue-select2>
					<div v-else class="form-control">Loading...</div>
				</div>
			</div>
			<div class="col col-12 pt-4 d-flex flex-wrap align-items-end gap-2">
				<UiLoadingButton
					type="submit"
					theme="primary"
					:loading="loading"
					:disabled="loading"
					@click="loadReport(1)"
				>
					Show Report
				</UiLoadingButton>

				<UiLoadingButton
					theme="outline-secondary"
					:loading="exportingCSV"
					:disabled="loading || exportingCSV"
					@click="exportCSV"
				>
					Export CSV
				</UiLoadingButton>

				<UiLoadingButton
					theme="outline-secondary"
					:loading="exportingExcel"
					:disabled="loading || exportingExcel"
					@click="exportExcel"
				>
					Export Excel
				</UiLoadingButton>
			</div>
		</div>
		<hr />

		<div class="frame-wrap">
			<table class="table report-1">
				<thead class="thead-themed">
					<tr>
						<th class="fs-sm">Order ID</th>
						<th class="fs-sm">Manager</th>
						<th class="fs-sm">Service date</th>
						<th class="fs-sm">Total paid</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(v, i) in paginate.data" :key="i">
						<td>
							{{ v.order_id }}
						</td>
						<td>
							{{ v.manager_name }}
						</td>
						<td>
							{{ v.service_date }}
						</td>
						<td>
							{{ v.total_paid }}
						</td>
					</tr>
				</tbody>
			</table>

			<pagination
				:data="paginate"
				@pagination-change-page="loadReport"
			></pagination>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';
import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';
import { AxiosHelper } from '@/helpers/axiosHelper';
import { download } from '@/helpers/download';
import { filterByMonth } from '@/reports-helpers/filter-by-month';
import { DateService } from '@/services/date';
import VueSelect2 from '@components/VueSelect2.vue';
import UiLoadingButton from '@ui/Button/LoadingButton.vue';

import pagination from 'laravel-vue-pagination';
import Debounce from 'lodash.debounce';
import Multiselect from 'vue-multiselect';

export default {
	name: 'ReportFinancialOrder',
	components: {
		VueSelect2,
		UiLoadingButton,
		pagination,
		Multiselect,
	},
	filters: {
		currencyFilter,
		formatDate,
		managerName,
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
				placeholder: 'Anyone',
				allowClear: true,
			},
			form: {
				date: null,
				user_id: null,
			},
			autocomplete: {
				isLoading: false,
				row_id: null,
				data: [],
			},
		};
	},
	// computed: {
	//     managers() {
	//         return this.managersSource;
	//     }
	// },
	async mounted() {
		this.setFormDate(DateService.fromToday());
		await this.loadManagers();
		await this.loadReport();
		this.initInputs();
	},
	methods: {
		autocompleteOnSelect(obj) {
			this.paginate.data[
				this.autocomplete.row_id
			].miscs.manager_form.order_id = obj.id ?? null;
			this.paginate.data[
				this.autocomplete.row_id
			].miscs.manager_form.branch_id = obj.branch_id ?? null;

			this.autocomplete.row_id = null;
		},
		autocompleteQuery: Debounce(async function (q) {
			this.autocomplete.isLoading = true;
			let res = await axios.post(
				'/reports/report-authorize/order-autocomplete',
				{
					q,
				}
			);

			this.autocomplete.data = res.data.items;
			this.autocomplete.isLoading = false;
		}, 500),
		initInputs() {
			filterByMonth({
				controlElement: $('#dateRangePicker'),
				minDate: new DateService(new Date(2021, 8)),
				maxDate: DateService.fromToday(),
				initialDate: DateService.fromToday(),
				onChange: ([selectedDate], _, __, asYearValue) => {
					this.setFormDate(selectedDate, asYearValue);
				},
			});
		},
		setFormDate(dateOrService, asYearValue = false) {
			let date =
				dateOrService instanceof DateService
					? dateOrService
					: new DateService(dateOrService);
			this.form.date = date.format({
				preset: asYearValue ? 'yearFilter' : 'monthFilter',
				convert: false,
			});
		},
		async loadManagers() {
			this.loading = true;
			await AxiosHelper({
				url: window.location.href + '/managers',
			})
				.then(({ link }) => {
					this.managers = link.map((item) => ({
						id: item.id,
						name: item.name,
					}));
				})
				.finally(() => {
					this.managersAreLoaded = true;
					this.loading = false;
				});
		},
		async loadReport(page = 1) {
			this.loading = true;
			await AxiosHelper({
				url: window.location.href + '?page=' + page,
				data: this.getFilter(),
			})
				.then(({ paginate }) => {
					paginate.data.forEach(function (item) {
						item.expanded = false;
						return item;
					});
					this.paginate = paginate;
				})
				.finally(() => {
					this.loading = false;
				});
		},
		async exportCSV() {
			this.exportingCSV = true;

			await AxiosHelper({
				url: window.location.href + '/export-csv',
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
				url: window.location.href + '/export-excel',
				data: this.getFilter(),
			})
				.then(({ link }) => download(link))
				.finally(() => {
					this.exportingExcel = false;
				});
		},
		getFilter() {
			const { date, user_id } = this.form;
			return {
				date,
				user_id:
					typeof user_id === 'number' || user_id
						? String(user_id)
						: null,
			};
		},
	},
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

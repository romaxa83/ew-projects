<template>
	<div>
		<div class="row mb-3">
			<div class="col col-12 col-xl-4">
				<div class="form-group">
					<label class="form-label">Period</label>
					<input
						type="text"
						class="form-control"
						placeholder="Select date"
						id="dateRangePicker"
					/>
				</div>
			</div>
			<div class="col col-12 col-xl-4">
				<div class="form-group">
					<label class="form-label">Sales team</label>
					<select
						v-if="salesTeamsAreLoaded"
						v-model="form.sales_team"
						class="form-control"
						name="sales_team"
					>
						<option
							v-if="salesTeams.length === 0"
							disabled
							selected
						>
							No available options
						</option>
						<option
							v-if="salesTeams.length > 0"
							v-for="team in salesTeams"
							:key="team.id"
							:value="team.id"
						>
							{{ team.title }}
						</option>
					</select>
					<div v-else class="form-control">Loading...</div>
				</div>
			</div>
			<div class="col col-12 col-xl-4">
				<div class="form-group">
					<label class="form-label">Manager</label>
					<vue-select2
						v-if="managersAreLoaded"
						v-model="form.user_id"
						:config="managersSelectConfig"
					>
						<option v-if="managers.length === 0" disabled selected>
							No available options
						</option>
						<option
							v-if="managers.length > 0"
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
					@click="loadReport"
				>
					Show Report
				</UiLoadingButton>
			</div>
		</div>
		<hr />

		<div class="frame-wrap">
			<table class="table report-1">
				<thead class="thead-themed">
					<tr>
						<th
							class="fs-sm"
							v-for="header in headers"
							:key="header"
						>
							{{ header }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(values, key) in records" :key="key">
						<td v-for="(v, k) in values" :key="k">
							{{ v }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';
import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';
import { AxiosHelper } from '@/helpers/axiosHelper';

import VueSelect2 from '@components/VueSelect2.vue';
import UiLoadingButton from '@ui/Button/LoadingButton.vue';

export default {
	name: 'ReportSalesFunelOrder',
	components: {
		VueSelect2,
		UiLoadingButton,
	},
	filters: {
		currencyFilter,
		formatDate,
		managerName,
	},
	data() {
		return {
			headers: [],
			records: [],
			loading: true,
			form: {
				user_id: null,
			},
			autocomplete: {
				isLoading: false,
				row_id: null,
				data: [],
			},
			managers: [],
			managersAreLoaded: false,
			managersSelectConfig: {
				placeholder: 'Anyone',
				allowClear: true,
			},
			salesTeams: [],
			salesTeamsAreLoaded: false,
		};
	},
	async mounted() {
		this.initDatepicker();
		await Promise.allSettled([this.loadManagers(), this.loadSalesTeams()]);
		await this.loadReport();
	},
	methods: {
		/**
		 * @param {Moment} start
		 * @param {Moment} end
		 */
		updateFormDate(start, end) {
			this.form.date_start = start.format('YYYY-MM-DD');
			this.form.date_end = end.format('YYYY-MM-DD');
		},
		initDatepicker() {
			this.updateFormDate(moment(), moment());

			$('#dateRangePicker').daterangepicker(
				{
					minDate: moment('2021-08-01', 'YYYY-MM-DD'),
					maxDate: moment(),
					startDate: moment(),
					endDate: moment(),
					drops: 'auto',
					locale: {
						format: 'MMM DD, YYYY',
					},
					// maxSpan: {
					// 	days: 90,
					// },
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
						'This month': [moment().startOf('month'), moment()],
						'This year': [moment().startOf('year'), moment()],
					},
				},
				(start, end) => {
					this.updateFormDate(start, end);
				}
			);
		},
		async loadSalesTeams() {
			await AxiosHelper({
				method: 'get',
				url: window.location.href + '/filter/sales-team',
			})
				.then(({ data }) => {
					this.salesTeams = data;
					this.form.sales_team = this.salesTeams[0]?.id || null;
				})
				.finally(() => {
					this.salesTeamsAreLoaded = true;
				});
		},
		async loadManagers() {
			await AxiosHelper({
				url: '/reports/financial-check-report/managers',
			})
				.then(({ link }) => {
					this.managers = link.map((item) => ({
						id: item.id,
						name: item.name,
					}));
				})
				.finally(() => {
					this.managersAreLoaded = true;
				});
		},
		async loadReport() {
			this.loading = true;
			await AxiosHelper({
				url: window.location.href,
				data: this.form,
			})
				.then(({ data: { headers, records } }) => {
					this.headers = headers;
					this.records = records;
					console.log(this);
				})
				.finally(() => {
					this.loading = false;
				});
		},
	},
};
</script>

<template>
	<div>
		<div class="row mb-3">
			<div class="col-5 col-sm-4 col-xl-3 p-2">
				<div class="form-group">
					<label class="form-label" for="dateRangePicker"
						>Period</label
					>
					<input
						type="text"
						class="form-control"
						placeholder="Select date"
						id="dateRangePicker"
					/>
				</div>
			</div>
			<div class="col-7 col-sm-4 p-2">
				<div class="form-group">
					<label class="form-label" for="users">Choose users</label>
					<select
						size="15"
						v-model="form.users"
						class="custom-select change-control select2"
						id="users"
						multiple
					>
						<option
							v-for="v in sortUsers"
							:key="v.id"
							v-bind:value="v.id"
						>
							{{ v.name }} {{ !v.active ? ' (Not active)' : '' }}
						</option>
					</select>
				</div>
			</div>
			<div class="col-12 col-sm-4 p-2">
				<div class="d-inline">
					<div class="dropdown d-inline-block mt-4">
						<a
							class="btn btn-secondary dropdown-toggle waves-effect waves-themed"
							href="#"
							role="button"
							id="dropdownMenuLink"
							data-toggle="dropdown"
							aria-haspopup="true"
							aria-expanded="false"
						>
							Display Rows
						</a>
						<div
							class="dropdown-menu p-4"
							aria-labelledby="dropdownMenuLink"
						>
							<div
								v-for="v in form.availableRows"
								:key="v.key"
								class="custom-control custom-checkbox mb-2"
							>
								<input
									type="checkbox"
									class="custom-control-input"
									:value="true"
									:id="'row_' + v.key"
									v-model="v.checked"
								/>
								<label
									class="custom-control-label"
									:for="'row_' + v.key"
									>{{ v.title }}</label
								>
							</div>
						</div>
					</div>

					<button
						type="button"
						name="create"
						class="btn btn-primary waves-effect waves-themed"
						@click="submit"
					>
						Show Report
					</button>
				</div>
			</div>
		</div>
		<hr />

		<div v-if="loading" class="d-flex justify-content-center">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<div class="frame-wrap">
			<table
				v-if="!loading && report.length"
				class="table table-striped table-bordered table-hover"
			>
				<thead class="thead-themed">
					<tr>
						<th class="fs-xl">Manager</th>
						<th
							v-for="v in activeRows"
							:key="v.key"
							class="fs-sm"
							v-text="v.title"
						></th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="v in report" :key="v.key">
						<th scope="row">
							{{ v.user.name }}
						</th>
						<th
							v-for="r in activeRows"
							:key="`${v.key}_${r.key}`"
							v-text="displayCellData(v.report, r.key)"
						></th>
					</tr>
					<tr class="text-primary">
						<th scope="row">Total:</th>
						<th
							v-for="r in activeRows"
							:key="r.key"
							v-text="displayTotalCellData(r.key)"
						></th>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';
import { getUsers } from '@/api/crm';
import { AxiosHelper } from '@/helpers/axiosHelper';
import { mapGetters } from 'vuex';

export default {
	name: 'ReportByManagers',
	filters: {
		currencyFilter,
	},
	data() {
		return {
			loading: false,
			users: {},
			report: [],
			form: {
				date_start: moment().startOf('month'),
				date_end: moment().subtract(1, 'day'),
				availableRows: [
					{
						key: 'total_sales',
						title: 'Total sales',
						checked: true,
					},
					{
						key: 'leads',
						title: 'Leads',
						checked: true,
					},
					{
						key: 'sent_quotes',
						title: 'Sent Quotes',
						checked: true,
					},
					{
						key: 'booked',
						title: 'Booked',
						checked: true,
					},
					{
						key: 'leads_converted',
						title: 'Leads Converted',
						checked: true,
					},
					{
						key: 'leads_lost',
						title: 'Leads Lost',
						checked: true,
					},
					{
						key: 'conversion',
						title: 'Conversion',
						checked: true,
					},
				],
				users: [],
			},
		};
	},
	computed: {
		activeRows() {
			return this.form.availableRows.filter((item) => item.checked);
		},
		sortUsers() {
			return this.users.length
				? this.users
						.sort((a, b) => a.name.localeCompare(b.name))
						.sort((a, b) => b.active - a.active) // Опускаем не активных
				: [];
		},
	},
	mounted() {
		this.initUsers();
		setTimeout(() => this.initInputs());
	},
	methods: {
		displayCellData(data, key) {
			let res = data[key] ?? 'Click Report btn';
			if (key === 'total_sales')
				res = this.$options.filters.currencyFilter(res);

			return res;
		},
		displayTotalCellData(key) {
			if (key === 'conversion') {
				const leads_converted =
					this.displayTotalCellData('leads_converted');

				return leads_converted
					? parseFloat(
							(leads_converted /
								this.displayTotalCellData('leads')) *
								100
					  ).toFixed(2) + '%'
					: '0%';
			}

			const total = this.report.reduce((sum, item) => {
				const v = item.report[key] ?? 0;
				return sum + v;
			}, 0);

			return key === 'total_sales'
				? this.$options.filters.currencyFilter(total)
				: total;
		},
		initInputs() {
			$('#dateRangePicker').daterangepicker(
				{
					minDate: moment('2020-01-01', 'YYYY-MM-DD'),
					maxDate: moment(),
					startDate: this.form.date_start,
					endDate: this.form.date_end,
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
						'Last 14 Days': [
							moment().subtract(13, 'days'),
							moment(),
						],
						'This Month': [
							moment().startOf('month').toDate(),
							moment().endOf('month').toDate(),
						],
						'Last Month': [
							moment()
								.subtract(1, 'month')
								.startOf('month')
								.toDate(),
							moment()
								.subtract(1, 'month')
								.endOf('month')
								.toDate(),
						],
					},
				},
				(start, end) => {
					this.form.date_start = start.format('YYYY-MM-DD');
					this.form.date_end = end.format('YYYY-MM-DD');
				}
			);

			let select2 = $('.select2');
			select2.select2({
				placeholder: 'All active users or Choose',
			});
			select2.on('select2:close', function (e) {
				this.dispatchEvent(new Event('change', { target: e.target }));
			});
		},
		async initUsers() {
			this.loading = true;

			const users_ids = await this.getUsersWithOrders();

			getUsers()
				.then(({ records }) => {
					this.users = records
						.filter((item) => users_ids.includes(item.id))
						.filter((item) => {
							if (
								!item.division_ids?.includes(
									App.Miscs.getCurrentDivision()
								)
							)
								return false;

							return true;
						});
				})
				.finally(() => (this.loading = false));
		},
		async getUsersWithOrders() {
			return await AxiosHelper({
				url: window.location.href + '/users-with-orders',
			}).then(({ ids }) => ids);
		},
		submit() {
			let rows = this.activeRows.map((item) => item.key);

			this.loading = true;
			AxiosHelper({
				url: window.location.href,
				data: {
					rows,
					division_id: App.Miscs.getCurrentDivision(),
					...this.form,
				},
			})
				.then(({ report }) => {
					this.report = report;
				})
				.finally(() => (this.loading = false));
		},
	},
};
</script>

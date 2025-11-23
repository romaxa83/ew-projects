<template>
	<div>
		<div class="row mb-3">
			<div class="col-5 col-sm-3 col-xl-2 p-2">
				<div class="form-group">
					<label class="form-label" for="dateRangePicker"
						>Data Range</label
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
			<div class="col-12 col-sm-5 p-2">
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
				class="table table-striped table-hover"
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
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';
import { getUsers } from '@/api/crm';
import { AxiosHelper } from '@/helpers/axiosHelper';

export default {
	name: 'ReportEffectiveActions',
	filters: {
		currencyFilter,
	},
	data() {
		return {
			loading: false,
			users: {},
			report: [],
			form: {
				date_start: moment().subtract(8, 'days'),
				date_end: moment().subtract(1, 'day'),
				availableRows: [
					{
						key: 'total_sales',
						title: 'Total sales',
						checked: true,
					},
					{
						key: 'leads_created',
						title: 'Leads created',
						checked: false,
					},
					{
						key: 'won_leads',
						title: 'Won leads',
						checked: false,
					},
					{
						key: 'lost_leads',
						title: 'Lost leads',
						checked: false,
					},
					// {
					//     key: 'outbound_calls',
					//     title: 'Outbound calls',
					//     checked: false,
					// },
					// {
					//     key: 'inbound_calls',
					//     title: 'Inbound calls',
					//     checked: false,
					// },
					// {
					//     key: 'all_calls',
					//     title: 'All calls',
					//     checked: true,
					// },
					{
						key: 'notes_added',
						title: 'Notes added',
						checked: true,
					},
					{
						key: 'tasks_added',
						title: 'Tasks added',
						checked: true,
					},
					{
						key: 'emails_sent',
						title: 'Emails sent',
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
		initUsers() {
			getUsers().then(({ records }) => (this.users = records));
		},
		submit() {
			let rows = this.activeRows.map((item) => item.key);

			this.loading = true;
			AxiosHelper({
				url: window.location.href,
				data: {
					rows,
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

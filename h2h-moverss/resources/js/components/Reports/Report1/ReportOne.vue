<template>
	<div>
		<div class="row mb-3">
			<div class="col p-2">
				<input
					type="text"
					class="form-control"
					placeholder="Select date"
					id="dateRangePicker"
				/>
			</div>
			<div class="col p-2">
				<select
					class="custom-select change-control"
					v-model="form.selectBy"
				>
					<option value="users">By Users</option>
					<option value="branch">By Branch</option>
				</select>
			</div>
			<div class="col p-2">
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
		<hr />

		<button class="button" @click="createTask">
			Create task from report
		</button>

		<div v-if="loading" class="d-flex justify-content-center">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<div class="frame-wrap">
			<table
				v-if="!loading && header.records.length"
				class="table report-1"
			>
				<thead class="thead-themed">
					<tr>
						<th
							v-for="(v, i) in header.records"
							:key="i"
							:class="v.class"
							v-text="v.value"
						></th>
						<th
							v-if="header.colspan"
							:colspan="header.colspan"
						></th>
					</tr>
				</thead>
				<tbody>
					<template v-for="(record, index) in report">
						<tr :key="`header-${index}`">
							<th scope="row">
								<i
									v-if="record.hasRecords"
									class="fal fa-angle-down fs-xl mr-2 cursor-pointer"
									@click="toggleRow(index)"
								></i>
								{{ record.title }}
							</th>
							<td
								v-for="(v, i) in record.header"
								:key="i"
								:class="v.class"
								v-text="v.value"
							></td>
						</tr>
						<template v-for="(vv, ii) in record.records">
							<tr
								v-show="record.expanded"
								:key="`header-${index}-${ii}`"
							>
								<th scope="row" class="record-first-row">
									<i
										v-if="vv.records"
										class="fal fa-angle-down fs-xl mr-2 cursor-pointer"
										@click="toggleRowLvl2(index, ii)"
									></i>
									{{ vv.title }}
								</th>
								<td
									v-for="(row, key) in vv.rows"
									:key="`header-${index}-${ii}-${key}`"
									:class="row.class"
									v-text="row.value"
								></td>
							</tr>
							<template v-for="(vvv, iii) in vv.records">
								<tr
									v-show="vv.expanded"
									:key="`header-${index}-${ii}-${iii}`"
								>
									<td
										v-for="(row, key) in vvv.rows"
										:key="`header-${index}-${iii}-${key}`"
										:class="row.class"
										v-text="row.value"
									></td>
								</tr>
							</template>
						</template>
					</template>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
export default {
	name: 'ReportOne',
	data() {
		return {
			loading: false,
			header: {
				records: [],
			},
			report: {},
			form: {
				date_start: moment().subtract(8, 'days'),
				date_end: moment().subtract(1, 'day'),
				selectBy: 'users',
				users: [],
			},
		};
	},
	mounted() {
		setTimeout(() => this.initInputs());
	},
	methods: {
		createTask() {
			this.$root.$refs.tasks.openCreateModal({
				title: 'For report...',
			});
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
						days: 14,
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
			select2.select2();
			select2.on('select2:close', function (e) {
				this.dispatchEvent(new Event('change', { target: e.target }));
			});
		},
		submit() {
			this.loading = true;
			axios
				.post(window.location.href, this.form)
				.then((resp) => {
					if (resp.data.success === true) {
						if (resp.data.report) {
							this.header = resp.data.report.header;
							this.report = resp.data.report.records;

							this.loading = false;
						}
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
				});
		},
		toggleRow(i) {
			this.report[i].expanded = !this.report[i].expanded;
		},
		toggleRowLvl2(i, ii) {
			this.report[i].records[ii].expanded =
				!this.report[i].records[ii].expanded;
		},
	},
};
</script>

<style scoped>
.report-1 .weekdays {
	font-weight: bold;
	color: #7ca5de;
}

.report-1 .week-Sat {
	color: #ff922d;
}

.report-1 .week-Sun {
	color: #7cc446;
}

.report-1 .record-first-row {
	font-weight: normal;
	padding-left: 50px;
}

.report-1 .record-first-row-2 {
	font-weight: normal;
	padding-left: 70px;
}
</style>

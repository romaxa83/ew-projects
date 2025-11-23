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
					class="form-control change-control"
					v-model="form.filters.order_ids"
					id="filter-order"
					placeholder="Search order by id"
					data-placeholder="Search order by id"
					data-route="/orders/autocomplete"
					multiple
				></select>
			</div>
			<div class="col p-2">
				<select
					class="select2 form-control"
					v-model="form.filters.status_ids"
					id="filter-status"
					placeholder="Search by status"
					data-placeholder="Search by status"
					data-route="/reports/report-authorize/status-autocomplete"
					multiple
				></select>
			</div>
			<div class="col p-2">
				<button
					type="button"
					name="create"
					class="btn btn-primary waves-effect waves-themed"
					@click="loadReport(1)"
				>
					<span
						v-show="loading"
						class="spinner-border spinner-border-sm"
						role="status"
						aria-hidden="true"
					></span>
					Show Report
				</button>
			</div>
		</div>
		<hr />

		<div class="frame-wrap">
			<table class="table report-1">
				<thead class="thead-themed">
					<tr>
						<th
							class="fs-xl cursor-pointer"
							@click="toggleOrder('id')"
						>
							ID
							<div
								class="sorting_tools"
								:class="{ sorting: form.order.key === 'id' }"
							>
								<span
									class="asc"
									:class="{ active: !form.order.isDesc }"
								></span>
								<span
									class="desc"
									:class="{ active: form.order.isDesc }"
								></span>
							</div>
						</th>
						<th
							class="fs-sm cursor-pointer"
							@click="toggleOrder('account_id')"
						>
							Account
							<div
								class="sorting_tools"
								:class="{
									sorting: form.order.key === 'account_id',
								}"
							>
								<span
									class="asc"
									:class="{ active: !form.order.isDesc }"
								></span>
								<span
									class="desc"
									:class="{ active: form.order.isDesc }"
								></span>
							</div>
						</th>
						<th
							class="fs-sm cursor-pointer"
							@click="toggleOrder('status')"
						>
							Status
							<div
								class="sorting_tools"
								:class="{
									sorting: form.order.key === 'status',
								}"
							>
								<span
									class="asc"
									:class="{ active: !form.order.isDesc }"
								></span>
								<span
									class="desc"
									:class="{ active: form.order.isDesc }"
								></span>
							</div>
						</th>
						<th
							class="fs-sm cursor-pointer"
							@click="toggleOrder('amount')"
						>
							Amount
							<div
								class="sorting_tools"
								:class="{
									sorting: form.order.key === 'amount',
								}"
							>
								<span
									class="asc"
									:class="{ active: !form.order.isDesc }"
								></span>
								<span
									class="desc"
									:class="{ active: form.order.isDesc }"
								></span>
							</div>
						</th>
						<th class="fs-sm">Client</th>
						<th
							class="fs-sm cursor-pointer"
							@click="toggleOrder('updated_at')"
						>
							Date Submit / Updated
							<div
								class="sorting_tools"
								:class="{
									sorting: form.order.key === 'updated_at',
								}"
							>
								<span
									class="asc"
									:class="{ active: !form.order.isDesc }"
								></span>
								<span
									class="desc"
									:class="{ active: form.order.isDesc }"
								></span>
							</div>
						</th>
						<th class="fs-sm">Manager Data</th>
					</tr>
				</thead>
				<tbody>
					<template v-for="(v, i) in paginate.data">
						<tr :key="`record-${v.id}`">
							<th
								scope="row"
								class="text-muted fw-700 cursor-pointer"
								v-text="v.id"
								@click="toggleRow(i)"
							></th>
							<td v-text="v.account.title"></td>
							<td
								:class="{
									'text-success':
										v.status === 'settledSuccessfully',
								}"
								v-text="v.status"
							></td>
							<td>{{ v.amount | currencyFilter }}</td>
							<td>
								<span v-if="v.miscs.billTo">{{
									v.miscs.billTo.firstName +
									' ' +
									v.miscs.billTo.lastName
								}}</span>
								<div
									v-if="
										v.miscs.customer &&
										v.miscs.customer.email
									"
								>
									<b>Email:</b> {{ v.miscs.customer.email }}
								</div>
								<div
									v-if="
										v.miscs.billTo &&
										v.miscs.billTo.phoneNumber
									"
								>
									<b>Phone:</b>
									{{ v.miscs.billTo.phoneNumber }}
								</div>
							</td>
							<td>
								{{ v.submitTimeUTC | formatDate }}
								<b
									v-if="v.created_at !== v.updated_at"
									class="text-warning d-block"
									title="Updated at"
								>
									{{ v.updated_at | formatDate }}
								</b>
							</td>
							<td>
								<b v-if="v.miscs.manager.approved"
									>Approved by
									{{ v.miscs.manager.user | managerName }}</b
								>
								<span v-else>Not approved</span>
								<b
									v-if="v.miscs.manager.comment"
									class="text-warning"
									>Has Comment</b
								>
								<a
									:href="
										'/orders/' + v.miscs.manager.order_id
									"
									target="_blank"
									v-if="v.miscs.manager.order_id"
									class="text-primary"
									v-text="`#${v.miscs.manager.order_id}`"
								></a>
							</td>
						</tr>
						<tr v-show="v.expanded" :key="`record-details-${v.id}`">
							<td colspan="6">
								<div class="d-flex">
									<div class="flex-fill mr-2">
										<div
											class="custom-control custom-checkbox"
										>
											<input
												type="checkbox"
												class="custom-control-input"
												:id="`approved-${v.id}`"
												v-model="
													v.miscs.manager_form
														.approved
												"
											/>
											<label
												class="custom-control-label"
												:for="`approved-${v.id}`"
												>Approved</label
											>
										</div>
										<div class="form-group mt-2">
											<multiselect
												v-model="v.miscs.manager_form.q"
												track-by="id"
												label="text"
												placeholder="Order ID. Start typing..."
												:searchable="true"
												:loading="
													autocomplete.isLoading
												"
												:clear-on-select="false"
												:options="autocomplete.data"
												@search-change="
													autocompleteQuery
												"
												@select="autocompleteOnSelect"
												@open="autocomplete.row_id = i"
											>
											</multiselect>
										</div>
										<div class="form-group">
											<textarea
												class="form-control"
												placeholder="Comment"
												v-model="
													v.miscs.manager_form.comment
												"
											></textarea>
										</div>

										<button
											type="button"
											@click="save(i)"
											class="btn btn-primary waves-effect waves-themed"
										>
											Save
										</button>
									</div>
									<div class="flex-fill">
										<h4>Transaction DATA</h4>
										<div class="panel-tag">
											<pre v-html="v.miscs"></pre>
										</div>
									</div>
								</div>
							</td>
						</tr>
					</template>
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
import Debounce from 'lodash.debounce';

import currencyFilter from '@/filters/currency.filter';
import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';

import pagination from 'laravel-vue-pagination';
import Multiselect from 'vue-multiselect';
import { AxiosHelper } from '@/helpers/axiosHelper';

export default {
	name: 'ReportAuthorizeTransactions',
	components: {
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
			paginate: {},
			form: {
				date_start: moment().subtract(8, 'days'),
				date_end: moment(),
				order: {
					key: 'updated_at',
					isDesc: true,
				},
				filters: {
					order_ids: [],
					status_ids: [],
				},
			},
			autocomplete: {
				isLoading: false,
				row_id: null,
				data: [],
			},
		};
	},
	mounted() {
		this.loadReport();
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
			$('#dateRangePicker').daterangepicker(
				{
					minDate: moment('2021-08-01', 'YYYY-MM-DD'),
					maxDate: moment(),
					startDate: this.form.date_start,
					endDate: this.form.date_end,
					drops: 'auto',
					locale: {
						format: 'MMM DD, YYYY',
					},
					maxSpan: {
						days: 90,
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

			$('#filter-status, #filter-order').each(function () {
				let el = $(this);
				el.select2({
					allowClear: true,
					ajax: {
						url() {
							return el.data('route');
						},
						method: 'POST',
						dataType: 'json',
						data(params) {
							return {
								q: params.term, // search term
								page: params.page || 1,
							};
						},
						processResults(response, params) {
							response = response.hasOwnProperty('data')
								? response.data
								: response;

							return {
								results: response.results,
								pagination: response.pagination,
							};
						},
						cache: true,
					},
					escapeMarkup(markup) {
						return markup;
					},
					minimumInputLength: 0,
					templateResult(data) {
						if (
							!data.disabled &&
							el.data('route').includes('orders')
						) {
							let orderId = `<div class="fs-md oid">Order: #${data.id} <span class="badge badge-warning">${data.division.title}</span></div>`;
							if (!data.client) {
								return `<div className="clearfix">${orderId}</div>`;
							}

							let client = $(App.Miscs.formatClient(data.client));

							$(client).find('.fs-md').parents().prepend(orderId);
							$(client)
								.find('.fs-md:not(.oid)')
								.removeClass('fs-md');

							return client;
						} else {
							return data.text;
						}
					},
					templateSelection: function (v) {
						if (el.data('route').includes('orders') && v.id)
							return 'Order #' + v.id;

						return v.text;
					},
				}).on('select2:close', function (e) {
					this.dispatchEvent(
						new Event('change', { target: e.target })
					);
				});
			});
		},
		async loadReport(page = 1) {
			this.loading = true;

			let resp = await AxiosHelper({
				url: window.location.href + '?page=' + page,
				data: this.form,
			});

			resp.paginate.data.map(function (item) {
				item.expanded = false;

				if (!item.miscs.manager) {
					item.miscs.manager = {
						approved: false,
						comment: null,
						branch_id: null,
						order_id: null,
						q: null,
					};
					item.miscs.manager_form = {
						approved: false,
						comment: null,
						branch_id: null,
						order_id: null,
						q: null,
					};
				} else {
					let data = { ...item.miscs.manager };
					if (data.order_id) data.order_id = data.order_id;

					item.miscs.manager_form = {
						q: {
							id: data.order_id,
							text: data.hasOwnProperty('order_id')
								? `Order #${data.order_id}`
								: null,
						},
						...data,
					};
				}
				return item;
			});

			this.paginate = resp.paginate;
			this.loading = false;
		},
		async save(i) {
			await AxiosHelper({
				url: window.location.href + '/save',
				data: {
					id: this.paginate.data[i].id,
					record: this.paginate.data[i].miscs.manager_form,
				},
			});

			this.paginate.data[i].miscs.manager = {
				...this.paginate.data[i].miscs.manager_form,
			};
			App.Forms.showAlert('success', 'Form saved');
		},
		toggleOrder(key) {
			if (key === this.form.order.key) {
				this.form.order.isDesc = !this.form.order.isDesc;
			} else {
				this.form.order.key = key;
			}
			this.loadReport();
		},
		toggleRow(i) {
			this.paginate.data[i].expanded = !this.paginate.data[i].expanded;
		},
	},
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

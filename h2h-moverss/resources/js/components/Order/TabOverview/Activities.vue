<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>
				<span
					v-show="loading"
					class="spinner-border spinner-border-sm mr-2"
					role="status"
					aria-hidden="true"
				></span>
				Activity
				<span class="ml-2 badge badge-warning" v-text="total"></span>
			</h2>
			<!--            <div v-if="section === 'short'" class="panel-toolbar ml-2 pr-3">-->
			<!--                <ul id="demo_panel-tabs" class="nav nav-tabs border-bottom-0" role="tablist">-->
			<!--                    <li class="nav-item">-->
			<!--                        <a class="nav-link" data-toggle="tab" href="#tab_default-1" role="tab"-->
			<!--                           aria-selected="false">All</a>-->
			<!--                    </li>-->
			<!--                    <li class="nav-item">-->
			<!--                        <a class="nav-link active" data-toggle="tab" @click.prevent="" href="#tab_default-2" role="tab"-->
			<!--                           aria-selected="true">Emails</a>-->
			<!--                    </li>-->
			<!--                    <li class="nav-item">-->
			<!--                        <a class="nav-link disabled" data-toggle="tab" href="#" role="tab">Disabled</a>-->
			<!--                    </li>-->
			<!--                </ul>-->
			<!--            </div>-->
			<div v-if="section === 'tab'" class="panel-toolbar">
				<select
					class="custom-select custom-select-sm rounded-0 border-top-0 border-left-0 border-right-0"
					v-model="sort"
				>
					<option value="asc">By date, Asc</option>
					<option value="desc">By date, Desc</option>
				</select>
			</div>
		</div>
		<div class="panel-container collapse show">
			<div class="panel-content pt-0">
				<ul class="activity-timeline">
					<li v-for="(v, i) in validRecords" :key="v.id">
						<button
							class="btn btn-sm btn-default btn-icon activity-timeline-icon rounded-circle js-waves-off"
						>
							<i :class="'fas ' + icon(v)"></i>
						</button>
						<div class="card mb-2">
							<div
								class="card-header bg-white py-2 pr-2 d-flex align-items-center flex-wrap"
							>
								<div class="fs-xs">
									<span
										v-if="v.type === 'status'"
										v-html="
											statusHighlight(
												v.miscs.from,
												v.miscs.to
											)
										"
									></span>
									<template
										v-else-if="
											v.type === 'sms' ||
											v.type === 'email'
										"
									>
										{{ v.type | capitalize }} sent to
										<b v-if="v.type === 'sms'">{{
											v.miscs.to | formatPhone
										}}</b>
										<b v-else>{{ v.miscs.to }}</b>
									</template>
									<template v-else-if="v.type === 'user'">
										Manager changed
										<span class="badge badge-secondary">{{
											v.miscs.from | managerName
										}}</span>
										to
										<span class="badge badge-info">{{
											v.miscs.to | managerName
										}}</span>
									</template>
									<template v-else>
										{{ v.type | capitalize }} changed
										{{ v.miscs }}
									</template>
								</div>
								<div
									class="d-flex position-relative ml-auto pr-2"
								>
									<span class="fs-xs text-muted">
										{{ v.created_at | formatDate }} by
										{{ v.user_id | managerName }}
									</span>
								</div>
							</div>
							<div
								v-if="v.type === 'sms'"
								class="card-body fs-xs py-2"
							>
								{{ v.miscs.text }}
							</div>
						</div>
					</li>
				</ul>

				<pagination
					:data="paginate"
					@pagination-change-page="setPage"
					:limit="8"
				></pagination>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

let myTimer = null;
let order_id = document.getElementById('order_id').textContent;

import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';
import formatPhone from '@/filters/formatPhone.filter';

import pagination from 'laravel-vue-pagination';

export default {
	name: 'OrderActivities',
	components: {
		pagination,
	},
	filters: {
		formatDate,
		managerName,
		formatPhone,
		capitalize(value) {
			if (!value) return '';
			value = value.toString();
			if (value === 'sms') value = 'SMS';
			else value = value.charAt(0).toUpperCase() + value.slice(1);

			return value;
		},
	},
	props: {
		section: String,
	},
	data() {
		return {
			sort: 'desc',
			tab: null,
			is_loaded: false,
			loading: false,
			activities: [],
			currentDate: null, // Дата получения данных с бека (надо для получения недавно измененных сообщений)
			page: 1,
			per_page: 20,
		};
	},
	computed: {
		paginate() {
			let from = (this.page - 1) * this.per_page,
				to = from + (this.per_page - 1),
				last_page = Math.ceil(this.total / this.per_page);

			return {
				current_page: this.page,
				last_page,
				from,
				to,
				next_page_url: this.page !== last_page,
				prev_page_url: this.page !== 1,
				first_page_url: true,
				last_page_url: this.page !== last_page,
				per_page: this.per_page,
				total: this.total,
			};
		},
		total() {
			return this.activities.length;
		},
		validRecords() {
			let records = this.activities,
				from = (this.page - 1) * this.per_page,
				to = from + (this.per_page - 1);

			if (this.sort === 'desc') records = records.slice().reverse();

			records = records.slice(from, to);

			return records;
		},
		...mapGetters({
			session: 'getSession',
		}),
	},
	mounted() {
		let vm = this;
		// Костыль, смотрим что нам надо подгрузить данные по табу
		$('#order-tabs').on('shown.bs.tab', function () {
			let tab = $('.active', this).attr('href').replace('#tab-', '');
			if (!vm.is_loaded && tab === 'activity') {
				vm.runTimer();
			}
		});
	},
	methods: {
		getActivities() {
			this.loading = true;
			axios
				.post('/orders/activity', {
					order_id,
					currentDate: this.currentDate,
					client_id: this.session.order.client_id,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						if (resp.data.activities) {
							let activities = resp.data.activities
									.slice()
									.filter((item) => item.type !== 'email'),
								emailRecords = resp.data.activities
									.slice()
									.filter((item) => item.type === 'email');

							this.activities = [
								...activities,
								...this.activities,
							];

							// Заливаем в стор данные для вывода Communications
							this.$store.dispatch('orderActivity/addRecords', {
								section: 'messages',
								records: emailRecords,
								total: 0,
							});
						}

						if (resp.data.gmail_messages) {
							this.$store.dispatch('orderActivity/addRecords', {
								section: 'gmail',
								records: resp.data.gmail_messages.records,
								total: resp.data.gmail_messages.total,
							});
						}

						this.currentDate = resp.data.currentDate;

						this.is_loaded = true;
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.finally(() => (this.loading = false))
				.catch((error) => App.Forms.simpleErrors(error.response.data));
		},
		getZadarmaCalls() {
			axios
				.get('/orders/callsZadarma?orderId=' + order_id)
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store.dispatch(
							'orderActivity/setZadarmaCalls',
							resp.data.records
						);
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.finally(() => (this.loading = false))
				.catch((error) => App.Forms.simpleErrors(error.response.data));
		},
		getCalls() {
			axios
				.post('/orders/calls', {
					order_id,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store.dispatch(
							'orderActivity/setCalls',
							resp.data.records
						);
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.finally(() => (this.loading = false))
				.catch((error) => App.Forms.simpleErrors(error.response.data));
		},
		icon(v) {
			let icon = 'fa-repeat-alt';
			if (v.type === 'status' && !v.miscs.from) icon = 'fa-plus';
			else if (v.type === 'sms') icon = 'fa-comment-alt-dots';
			else if (v.type === 'email') icon = 'fa-envelope';
			else if (v.type === 'user') icon = 'fa-user';

			return icon;
		},
		mailEvents(ev) {
			let statuses = '';

			Object.keys(ev).forEach((k) => {
				if (k === 'open') {
					statuses +=
						'<span class="badge badge-info">Email viewed</span> ';
				} else if (k === 'click') {
					statuses +=
						'<span class="badge badge-success">Client opened confirmation page</span> ';
				} else if (k === 'unsub') {
					statuses +=
						'<span class="badge badge-danger">Unsubscribed</span> ';
				} else if (k === 'spam') {
					statuses +=
						'<span class="badge badge-danger">Marked as SPAM</span> ';
				} else if (k === 'blocked') {
					statuses +=
						'<span class="badge badge-danger">Blocked by mail service</span> ';
				} else if (k === 'bounce') {
					statuses +=
						'<span class="badge badge-danger">Delivery failed</span> ';
				}
			});

			return statuses;
		},
		runTimer() {
			this.getCalls();
			this.getZadarmaCalls();
			this.getActivities();

			clearInterval(myTimer);
			myTimer = setInterval(() => this.getActivities(), 60 * 1000 * 5); // Every 5 min
		},
		setPage(page = 1) {
			this.page = page;
		},
		statusHighlight(status_from, status_to) {
			if (status_from) {
				return (
					'Status changed <span class="badge badge-secondary">' +
					(window.statuses_list[status_from]
						? window.statuses_list[status_from].title
						: status_from) +
					'</span> ' +
					'to <span class="badge badge-info">' +
					(window.statuses_list[status_to]
						? window.statuses_list[status_to].title
						: status_to) +
					'</span>'
				);
			}

			return window.statuses_list[status_to].title;
		},
	},
};
</script>

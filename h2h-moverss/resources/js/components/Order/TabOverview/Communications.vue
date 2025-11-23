<template>
	<div class="panel" v-show="total">
		<div class="panel-hdr">
			<template v-if="section === 'short'">
				<h2>
					Last {{ total > 5 ? ' 5' : '' }} Communications
					<span
						class="ml-2 badge badge-warning"
						v-text="total"
					></span>
				</h2>
				<div class="panel-toolbar" v-if="total > 5">
					<button
						@click="openTab"
						class="btn btn-primary btn-sm waves-effect waves-themed"
					>
						See all
					</button>
				</div>
			</template>
			<h2 v-else>
				Communications
				<span class="ml-2 badge badge-warning" v-text="total"></span>
			</h2>
		</div>
		<div class="panel-container collapse show">
			<div class="panel-content pt-0">
				<ul
					class="activity-timeline"
					:class="{
						'mb-0': section === 'short',
						'mt-2': section === 'short',
					}"
				>
					<communications-record
						v-for="v in validRecords"
						:key="v.section + v.id"
						:v="v"
						:section="section"
						:now="now"
					></communications-record>
				</ul>

				<pagination
					v-if="section !== 'short'"
					:data="paginate"
					:limit="3"
					@pagination-change-page="setPage"
				></pagination>
			</div>
		</div>
	</div>
</template>

<script>
import Debounce from 'lodash.debounce';
import { mapGetters } from 'vuex';
import pagination from 'laravel-vue-pagination';
import CommunicationsRecord from './Communications/Record';

export default {
	name: 'Communications',
	components: {
		CommunicationsRecord,
		pagination,
	},
	props: {
		section: {
			type: String,
			default: 'short',
		},
	},
	data() {
		return {
			func: null,
			page: 1,
			per_page: 20,
			now: moment(),
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
		validRecords() {
			let records =
					this.section === 'short'
						? this.records(5)
						: this.records(0),
				from = (this.page - 1) * this.per_page,
				to = from + (this.per_page - 1);

			if (this.section !== 'short') records = records.slice(from, to);

			return records;
		},
		...mapGetters({
			records: 'orderActivity/records',
			total: 'orderActivity/total',
		}),
	},
	watch: {
		total() {
			this.startTimer();
		},
	},
	methods: {
		openTab() {
			$('[href="#tab-activity"]').trigger('click');
			window.scrollTo(0, 180);
		},
		setPage(page = 1) {
			this.page = page;
		},
		startTimer: Debounce(function () {
			// Запускаем часы на 20 сек.
			let interval = setInterval(
				() => (this.now = moment.utc().local()),
				1000
			);

			setTimeout(() => clearInterval(interval), 20000);
		}, 500),
	},
};
</script>

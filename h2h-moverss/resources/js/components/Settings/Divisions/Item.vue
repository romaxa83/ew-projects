<template>
	<div
		class="tab-pane fade"
		:id="`divisions_${record.id}`"
		role="tabpanel"
		:class="{ show: !i, active: !i }"
		:aria-labelledby="`divisions-${record.id}-tab`"
	>
		<div class="card border">
			<div class="card-header">
				<ul class="nav nav-tabs card-header-tabs" role="tablist">
					<li class="nav-item">
						<a
							class="nav-link active"
							data-toggle="tab"
							:href="`#divisions_${record.id}-tab-1`"
							role="tab"
							>Main</a
						>
					</li>
					<li class="nav-item">
						<a
							class="nav-link"
							data-toggle="tab"
							:href="`#divisions_${record.id}-tab-2`"
							role="tab"
							>Settings</a
						>
					</li>
					<li class="nav-item">
						<a
							class="nav-link"
							data-toggle="tab"
							:href="`#divisions_${record.id}-tab-4`"
							role="tab"
							>Payments</a
						>
					</li>
					<li class="nav-item">
						<a
							class="nav-link"
							data-toggle="tab"
							:href="`#divisions_${record.id}-tab-3`"
							role="tab"
							>Customer page</a
						>
					</li>
				</ul>
			</div>
			<div class="card-body">
				<div class="tab-content">
					<div
						class="tab-pane fade show active"
						:id="`divisions_${record.id}-tab-1`"
						role="tabpanel"
					>
						<div class="row">
							<div class="col-md-6 col-lg-4">
								<div class="form-group mb-3">
									<label class="form-label">Title</label>
									<input
										type="text"
										class="form-control"
										v-model="record.title"
									/>
								</div>
							</div>
							<div class="col-md-3 col-lg-1">
								<div class="form-group mb-3">
									<label class="form-label">Short Name</label>
									<input
										type="text"
										class="form-control"
										v-model="record.short"
									/>
								</div>
							</div>
							<div class="col-md-3 col-lg-4">
								<div class="form-group mb-3">
									<label class="form-label">Subdomain</label>
									<input
										type="text"
										class="form-control"
										v-model="record.name"
									/>
								</div>
							</div>
						</div>
					</div>
					<div
						class="tab-pane fade"
						:id="`divisions_${record.id}-tab-2`"
						role="tabpanel"
					>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label">Timezone</label>
									<select
										size="15"
										v-model="timezone"
										class="custom-select change-control"
										:id="`d_${record.id}_tz`"
									>
										<option
											v-for="(v, i) in TimeZones"
											:key="i"
											v-bind:value="v"
										>
											{{ v }}
										</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row border-top mb-3">
							<div class="col"></div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Zadarma PBX ID</label
									>
									<input
										type="text"
										class="form-control"
										v-model="zadarma_pbx_id"
									/>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Zadarma API KEY</label
									>
									<input
										type="text"
										class="form-control"
										v-model="zadarma_api_key"
									/>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Zadarma API SECRET</label
									>
									<input
										type="text"
										class="form-control"
										v-model="zadarma_api_secret"
									/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Zadarma Caller ID</label
									>
									<input
										type="text"
										class="form-control"
										v-model="zadarma_pbx_caller_id"
									/>
								</div>
							</div>
						</div>
						<div class="row border-top mb-3">
							<div class="col"></div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Ringostat Project ID</label
									>
									<input
										type="text"
										class="form-control"
										v-model="ringostat_project_id"
									/>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Ringostat Auth Key</label
									>
									<input
										type="text"
										class="form-control"
										v-model="ringostat_auth_key"
									/>
								</div>
							</div>
						</div>
						<div class="row border-top mb-3">
							<div class="col"></div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Mailchimp API KEY</label
									>
									<input
										type="text"
										class="form-control"
										v-model="mailchimp_api_key"
									/>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Mandrill From Name</label
									>
									<input
										type="text"
										class="form-control"
										v-model="mandrill_from_name"
									/>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Mandrill From Email</label
									>
									<input
										type="text"
										class="form-control"
										v-model="mandrill_from_email"
									/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Mandrill Templates Division
										Label</label
									>
									<input
										type="text"
										class="form-control"
										v-model="mandrill_templates_label"
									/>
								</div>
							</div>
						</div>
						<div class="row border-top mb-3">
							<div class="col"></div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>HelloSign API Key</label
									>
									<input
										type="text"
										class="form-control"
										v-model="hellosign_api_key"
									/>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>HelloSign CC email</label
									>
									<input
										type="text"
										class="form-control"
										v-model="hellosign_api_cc_email"
									/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>HelloSign Interstate Template ID</label
									>
									<input
										type="text"
										class="form-control"
										v-model="
											hellosign_interstate_template_id
										"
									/>
								</div>
							</div>
						</div>
						<div class="row border-top mb-3">
							<div class="col"></div>
						</div>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Summer Rates for Local Estimate</label
									>
									<div class="form-row">
										<div class="form-group col-sm-6">
											<label>From Date (including)</label>
											<input
												type="text"
												class="form-control"
												v-model="
													local_rates_summer_from
												"
												ref="from_date"
											/>
										</div>
										<div class="form-group col-sm-6">
											<label>To Date (including)</label>
											<input
												type="text"
												class="form-control"
												v-model="local_rates_summer_to"
												ref="to_date"
											/>
										</div>
									</div>
									<!--                                    <select class="form-control"-->
									<!--                                            v-model="local_rates_season">-->
									<!--                                        <option value="winter">Winter</option>-->
									<!--                                        <option value="summer">Summer</option>-->
									<!--                                    </select>-->
								</div>
							</div>
						</div>
					</div>
					<div
						class="tab-pane fade"
						:id="`divisions_${record.id}-tab-3`"
						role="tabpanel"
					>
						<div class="row">
							<div class="col-4">
								<div class="form-group mb-3">
									<label class="form-label"
										>Office Phones</label
									>
									<input
										type="text"
										class="form-control"
										v-model="phone"
									/>
								</div>
							</div>
							<div class="col-4">
								<div class="form-group mb-3">
									<label
										class="form-label"
										:for="`d_${record.id}_domain`"
										>Division website</label
									>
									<input
										type="text"
										:id="`d_${record.id}_domain`"
										class="form-control"
										placeholder="domain.com"
										v-model="domain"
									/>
								</div>
							</div>
						</div>
					</div>
					<tab-payments-divisions
						:record="record"
						:index="i"
						@paymentAddEmpty="paymentAddEmpty"
						@paymentRemoveItem="paymentRemoveItem"
						@paymentsArrowDown="paymentsArrowDown"
						@paymentsArrowUp="paymentsArrowUp"
					></tab-payments-divisions>
				</div>
				<!--                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>-->
			</div>
		</div>

		<!--        <h3 v-text="record.title"></h3>-->
		<div class="d-flex mt-4">
			<button
				@click="submit()"
				type="button"
				class="text-nowrap btn btn-success waves-effect waves-themed"
			>
				<span
					v-show="updating"
					class="spinner-border spinner-border-sm"
					role="status"
					aria-hidden="true"
				></span>
				<i class="fal fa-download mr-1"></i>
				{{ updating ? 'Saving changes' : 'Save changes' }}
			</button>
			<button
				@click="clearCache()"
				type="button"
				class="ml-auto btn btn-danger waves-effect waves-themed"
				:disabled="clearing"
			>
				<span
					v-show="clearing"
					class="spinner-border spinner-border-sm"
					role="status"
					aria-hidden="true"
				></span>
				Update session division cache
			</button>
		</div>
	</div>
</template>

<script>
import { TimeZones } from '@/helpers/TimeZones';
import TabPaymentsDivisions from '@components/Settings/Divisions/TabPaymentsDivisions.vue';

export default {
	name: 'DivisionsItem',
	components: { TabPaymentsDivisions },
	props: {
		clearing: {
			type: Boolean,
		},
		i: {
			type: Number,
			required: true,
		},
		record: {
			type: Object,
			required: true,
		},
		updating: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			TimeZones,
		};
	},
	computed: {
		domain: {
			get() {
				return this.record.miscs.domain ?? null;
			},
			set(val) {
				this.record.miscs.domain = val;
			},
		},
		local_rates_summer_from: {
			get() {
				return this.record.miscs.local_rates_summer_from ?? null;
			},
			set(val) {
				this.record.miscs.local_rates_summer_from = val;
			},
		},
		local_rates_summer_to: {
			get() {
				return this.record.miscs.local_rates_summer_to ?? null;
			},
			set(val) {
				this.record.miscs.local_rates_summer_to = val;
			},
		},
		mailchimp_api_key: {
			get() {
				return this.record.miscs.mailchimp_api_key ?? null;
			},
			set(val) {
				this.record.miscs.mailchimp_api_key = val;
			},
		},
		mandrill_from_email: {
			get() {
				return this.record.miscs.mandrill_from_email ?? null;
			},
			set(val) {
				this.record.miscs.mandrill_from_email = val;
			},
		},
		hellosign_api_key: {
			get() {
				return this.record.miscs.hellosign_api_key ?? null;
			},
			set(val) {
				this.record.miscs.hellosign_api_key = val;
			},
		},
		hellosign_api_cc_email: {
			get() {
				return this.record.miscs.hellosign_api_cc_email ?? null;
			},
			set(val) {
				this.record.miscs.hellosign_api_cc_email = val;
			},
		},
		hellosign_interstate_template_id: {
			get() {
				return (
					this.record.miscs.hellosign_interstate_template_id ?? null
				);
			},
			set(val) {
				this.record.miscs.hellosign_interstate_template_id = val;
			},
		},
		mandrill_from_name: {
			get() {
				return this.record.miscs.mandrill_from_name ?? null;
			},
			set(val) {
				this.record.miscs.mandrill_from_name = val;
			},
		},
		mandrill_templates_label: {
			get() {
				return this.record.miscs.mandrill_templates_label ?? null;
			},
			set(val) {
				this.record.miscs.mandrill_templates_label = val;
			},
		},
		phone: {
			get() {
				return this.record.miscs.phone ?? null;
			},
			set(val) {
				this.record.miscs.phone = val;
			},
		},
		timezone: {
			get() {
				return this.record.miscs.tz ?? null;
			},
			set(val) {
				this.record.miscs.tz = val;
			},
		},
		zadarma_api_key: {
			get() {
				return this.record.miscs.zadarma_api_key ?? null;
			},
			set(val) {
				this.record.miscs.zadarma_api_key = val;
			},
		},
		zadarma_api_secret: {
			get() {
				return this.record.miscs.zadarma_api_secret ?? null;
			},
			set(val) {
				this.record.miscs.zadarma_api_secret = val;
			},
		},
		zadarma_pbx_caller_id: {
			get() {
				return this.record.miscs.zadarma_pbx_caller_id ?? null;
			},
			set(val) {
				this.record.miscs.zadarma_pbx_caller_id = val;
			},
		},
		ringostat_project_id: {
			get() {
				return this.record.miscs.ringostat_project_id ?? null;
			},
			set(val) {
				this.record.miscs.ringostat_project_id = val;
			},
		},
		ringostat_auth_key: {
			get() {
				return this.record.miscs.ringostat_auth_key ?? null;
			},
			set(val) {
				this.record.miscs.ringostat_auth_key = val;
			},
		},
		zadarma_pbx_id: {
			get() {
				return this.record.miscs.zadarma_pbx_id ?? null;
			},
			set(val) {
				this.record.miscs.zadarma_pbx_id = val;
			},
		},
		// local_rates_season: {
		//     get() {
		//         return this.record.miscs.local_rates_season ?? null;
		//     },
		//     set(val) {
		//         this.record.miscs.local_rates_season = val;
		//     }
		// }
	},
	beforeMount() {
		if (!this.record.miscs) {
			this.record.miscs = {};
		}
	},
	mounted() {
		// console.log(this.$refs.from_date);
		flatpickr(this.$refs.from_date, {
			altInput: true,
			altFormat: 'F j',
			dateFormat: 'm-d',
		});
		flatpickr(this.$refs.to_date, {
			altInput: true,
			altFormat: 'F j',
			dateFormat: 'm-d',
		});
		// fp = flatpickr('#start_date', {
		//     inline: true,
		//     // onChange: (selectedDates, dateStr, instance) => {
		//     //     // Запрещаем менять данные если назначены работы
		//     //     if (this.hasDispatchTasks)
		//     //         instance.setDate(this.record.start_date);
		//     // }
		// });
		let select2 = $(`#d_${this.record.id}_tz`);
		select2.select2({
			placeholder: 'Division timezone',
		});
		select2.on('select2:close', function (e) {
			this.dispatchEvent(new Event('change', { target: e.target }));
		});
	},
	methods: {
		clearCache() {
			this.$emit('clear-cache', this.i);
		},
		paymentAddEmpty() {
			this.$emit('paymentAddEmpty', this.i);
		},
		paymentRemoveItem(i) {
			this.$emit('paymentRemoveItem', {
				index: this.i,
				i,
			});
		},
		paymentsArrowDown(obj) {
			this.$emit('paymentsArrowDown', obj);
		},
		paymentsArrowUp(obj) {
			this.$emit('paymentsArrowUp', obj);
		},
		submit() {
			this.$emit('submit', this.i);
		},
	},
};
</script>

<style>
.cur-year {
	display: none !important;
}
</style>

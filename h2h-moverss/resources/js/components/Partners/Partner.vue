<template>
	<div>
		<div class="d-flex">
			<div class="flex-grow-1">
				<ul role="tablist" class="nav nav-tabs nav-tabs-clean">
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-information"
							role="tab"
							aria-selected="true"
							class="nav-link active"
							>Information Part</a
						>
					</li>
				</ul>
			</div>
			<div class="ml-auto nav-tabs-clean">
				<div class="form-group mb-0">
					<div class="row">
						<div class="col text-right">
							<a
								href="/partners"
								class="btn btn-outline-default mr-3 waves-effect waves-themed"
							>
								<i class="fal fa-home"></i> All Partners
							</a>

							<button
								@click="submit()"
								type="button"
								class="text-nowrap btn waves-effect waves-themed"
								:class="{
									'btn-danger': is_changed,
									'btn-default': !is_changed,
								}"
								:disabled="!is_changed"
							>
								<span
									v-show="updating"
									class="spinner-border spinner-border-sm"
									role="status"
									aria-hidden="true"
								></span>
								<i class="fal fa-download mr-1"></i>
								{{
									record.id
										? updating
											? 'Saving changes'
											: 'Save changes'
										: 'Create new truck'
								}}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-content mt-md-3 mt-6">
			<div v-if="loading" class="d-flex justify-content-center">
				<div class="spinner-border" role="status">
					<span class="sr-only">Loading...</span>
				</div>
			</div>
			<div
				v-else
				role="tabpanel"
				id="tab-information"
				aria-labelledby="tab-information"
				class="tab-pane fade active show"
			>
				<div class="row">
					<div class="col-lg-6">
						<div class="panel">
							<div class="panel-hdr">
								<h2>Partner Information</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<!-- Branch -->
									<div class="form-group">
										<label
											for="division_id"
											class="form-label"
											><sup>*</sup> Branches</label
										>
										<select
											id="division_id"
											class="form-control select2"
											data-placeholder="Choose Branches"
											v-model="record.division_id"
										>
											<option :value="null">
												-- select an option --
											</option>
											<option
												v-for="v in divisions"
												:key="v.id"
												v-bind:value="v.id"
											>
												{{ v.title }}
											</option>
										</select>
									</div>
									<!-- Name -->
									<div class="form-group">
										<label class="form-label"
											><sup>*</sup>Name</label
										>
										<input
											type="text"
											class="form-control"
											v-model="record.name"
											placeholder="Name"
										/>
									</div>
									<!-- Contact person -->
									<div class="form-group">
										<label class="form-label"
											>Contact person</label
										>
										<input
											type="text"
											class="form-control"
											v-model="record.contact_person"
											placeholder="Contact person"
										/>
									</div>
									<!-- Phone -->
									<div class="form-group">
										<label class="form-label">Phone</label>
										<input
											type="text"
											class="form-control partner_contact_phone"
											v-model="record.phone"
											placeholder="Phone"
										/>
									</div>
									<!-- Email -->
									<div class="form-group">
										<label class="form-label">Email</label>
										<input
											type="text"
											class="form-control partner_contact_email"
											v-model="record.email"
											placeholder="Email"
										/>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { AxiosHelper } from '@/helpers/axiosHelper';

export default {
	name: 'Partner',
	components: {},
	data() {
		return {
			divisions_id: null,
			loading: true,
			updating: false,
			is_changed: false,
			record: {},
			divisions: {},
			records_orig: [],
		};
	},
	computed: {},
	watch: {
		record: {
			handler: function (val, oldVal) {
				if (!this.is_changed && Object.keys(oldVal).length) {
					this.is_changed = true;
				}
			},
			deep: true,
		},
	},
	mounted() {
		AxiosHelper({
			url: window.location.href,
		})
			.then(({ record, divisions }) => {
				if (record) {
					this.record = record;
					this.divisions = divisions;
				}
			})
			.finally(() => {
				this.loading = false;
				this.initMasks();
			});
	},
	methods: {
		initMasks() {
			this.$nextTick(() => {
				$('.partner_contact_phone').each(function () {
					Inputmask({ mask: '(999) 999-9999' }).mask(this);
				});
				$('.partner_contact_email').each(function () {
					Inputmask('email', { jitMasking: true }).mask(this);
				});

				let select2 = $('.select2');
				select2.select2();
				select2.on('select2:close', function (e) {
					this.dispatchEvent(
						new Event('change', { target: e.target })
					);
				});
			});
		},
		submit() {
			this.updating = true;

			AxiosHelper({
				url: window.location.href + '/save',
				data: this.record,
			})
				.then(({ record, msg }) => {
					if (record) {
						this.record = record;

						App.Forms.showAlert('success', msg);
					}
				})
				.finally(() => {
					this.updating = false;
				});
		},
	},
};
</script>

<template>
	<div>
		<div class="d-flex">
			<div class="flex-grow-1">
				<ul role="tablist" class="nav nav-tabs nav-tabs-clean">
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-general"
							role="tab"
							aria-selected="true"
							class="nav-link active"
							>General</a
						>
					</li>
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-driver"
							role="tab"
							aria-selected="false"
							class="nav-link"
							>Driver License</a
						>
					</li>
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-calendar"
							role="tab"
							aria-selected="false"
							class="nav-link"
							>Holidays</a
						>
					</li>
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-signature"
							role="tab"
							aria-selected="false"
							class="nav-link"
							>Signature</a
						>
					</li>
				</ul>
			</div>
			<div class="ml-auto nav-tabs-clean">
				<div class="form-group mb-0">
					<div class="row">
						<a
							href="/company/employees"
							class="btn btn-outline-default mr-3 waves-effect waves-themed"
						>
							<i class="fal fa-home"></i> All Employee
						</a>

						<button
							class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed"
							@click="addNewEmployee"
							:disabled="is_changed"
						>
							<i class="fal fa-plus mr-1"></i> Add New Employee
						</button>

						<div class="col text-right">
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
										: 'Create new record'
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
				id="tab-general"
				aria-labelledby="tab-general"
				class="tab-pane fade active show"
			>
				<div class="row">
					<div class="col-lg-6">
						<div class="panel">
							<div class="panel-hdr">
								<h2>Employee Information</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label for="name" class="form-label"
											>First name<sup>*</sup></label
										>
										<input
											id="name"
											type="text"
											class="form-control"
											placeholder="First name"
											v-model="record.name"
										/>
									</div>
									<div class="form-group">
										<label for="l_name" class="form-label"
											>Last name</label
										>
										<input
											id="l_name"
											type="text"
											class="form-control"
											placeholder="Last name"
											v-model="record.l_name"
										/>
									</div>
									<div class="form-group">
										<label for="address" class="form-label"
											>Address</label
										>
										<input
											id="address"
											type="text"
											class="form-control"
											placeholder="Address"
											v-model="record.address"
										/>
									</div>
									<div class="form-group">
										<label for="birthday" class="form-label"
											>Date of birth</label
										>
										<input
											id="birthday"
											type="text"
											class="form-control flatpickr"
											placeholder="Date of birth"
											v-model="record.birthday"
										/>
									</div>
								</div>
							</div>
						</div>

						<div class="panel">
							<div class="panel-hdr">
								<h2>Job Information</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label for="active" class="form-label"
											>Job Status</label
										>
										<select
											id="active"
											class="form-control"
											v-model="record.active"
										>
											<option value="1">In Work</option>
											<option value="0">Fired</option>
										</select>
									</div>
									<div class="form-group">
										<label
											for="division_ids"
											class="form-label"
											><sup>*</sup> Branches</label
										>
										<select
											id="division_ids"
											class="form-control select2"
											multiple
											data-placeholder="Choose Branches"
											v-model="record.division_ids"
										>
											<option :value="null">
												-- select an option --
											</option>
											<option
												v-for="v in types.divisions"
												:key="v.id"
												v-bind:value="v.id"
											>
												{{ v.title }}
											</option>
										</select>
									</div>
									<div class="form-group">
										<label for="roles" class="form-label"
											><sup>*</sup> Job Roles</label
										>
										<select
											id="roles"
											class="form-control select2"
											multiple
											data-placeholder="Select role"
											v-model="record.roles"
										>
											<option :value="null">
												-- select an option --
											</option>
											<option
												v-for="v in types.roles"
												:key="v.id"
												v-bind:value="v.id"
											>
												{{ v.title }}
											</option>
										</select>
									</div>
									<div class="form-group">
										<label for="roles" class="form-label"
											>Sales Team</label
										>
										<select
											id="sales-team"
											class="form-control"
											data-placeholder="Select team"
											v-model="record.sales_team"
										>
											<option :value="null">
												Without team
											</option>
											<option
												v-for="v in types.sales_team"
												:key="v.key"
												v-bind:value="v.key"
											>
												{{ v.title }}
											</option>
										</select>
									</div>
									<div class="form-group">
										<label for="pay_type" class="form-label"
											>Pay type</label
										>
										<select
											id="pay_type"
											v-model="record.pay_type"
											class="form-control"
										>
											<option value="hour">
												Pay per hour
											</option>
											<option value="month">
												Pay per month
											</option>
										</select>
									</div>

									<div class="h4">Zadarma PBX data</div>
									<b-tabs
										v-if="Object.keys(pbxData).length > 0"
										no-fade
										pills
										card
										vertical
										:nav-class="['bg-white']"
									>
										<b-tab
											v-for="(pbx, key) in pbxData"
											:key="key"
											title-item-class="title-class"
											:title="
												pbx.division.pbx_id +
												' (' +
												pbx.division.title +
												')'
											"
										>
											<div>
												<div class="form-group">
													<label class="form-label"
														>PBX Extension</label
													>
													<input
														type="text"
														class="form-control"
														placeholder="PBX Extension"
														v-model="
															pbxData[key].pbx_ext
														"
													/>
												</div>
												<div class="form-group">
													<label class="form-label"
														>Password</label
													>
													<input
														type="text"
														class="form-control"
														placeholder="Password"
														v-model="
															pbxData[key]
																.pbx_password
														"
													/>
												</div>
												<div class="form-group">
													<label class="form-label"
														>PBX show
														web-phone</label
													>
													<select
														class="form-control"
														v-model.number="
															pbxData[key]
																.pbx_show_webrtc
														"
													>
														<option value="0">
															No
														</option>
														<option value="1">
															Yes
														</option>
													</select>
												</div>
											</div>
										</b-tab>
									</b-tabs>

									<!--                                    <div class="form-group">-->
									<!--                                        <label class="form-label">Nickname</label>-->
									<!--                                        <input type="text" class="form-control" placeholder="Truck Nickname">-->
									<!--                                        -->
									<!--                                <span class="help-block">For Dispatch page</span>-->
									<!--                                    </div>-->
									<!--                                    <div class="form-group">-->
									<!--                                        <label class="form-label">Dispatch Color</label>-->
									<!--                                        <input type="text" class="form-control" placeholder="Truck Color">-->
									<!--                                        -->
									<!--                                <span class="help-block">For Dispatch page</span>-->
									<!--                                    </div>-->
								</div>
							</div>
						</div>

						<div class="panel">
							<div class="panel-hdr">
								<h2>Auth</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label class="form-label" for="static"
											>Login</label
										>
										<input
											type="text"
											class="form-control-plaintext"
											id="static"
											:value="userEmail"
											readonly
										/>
										<span
											class="help-block"
											v-show="userEmail"
											>Primary email or first</span
										>
										<span
											class="help-block"
											v-show="!userEmail"
											>Will be used primary email or
											first</span
										>
									</div>

									<div class="form-group">
										<label
											for="user_active"
											class="form-label"
											>Can login to account</label
										>
										<select
											id="user_active"
											v-model="userActive"
											class="form-control"
										>
											<option value="1">Yes</option>
											<option value="0">No</option>
										</select>
									</div>

									<div class="form-group">
										<label
											for="sent_welcome"
											class="form-label"
											>Sent Welcome Email</label
										>
										<select
											id="sent_welcome"
											v-model="record.send_welcome"
											class="form-control"
										>
											<option value="1">Yes</option>
											<option value="0">No</option>
										</select>
										<span class="help-block"
											>Password will be generated and
											changed</span
										>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="panel">
							<div class="panel-hdr">
								<h2>Employee Contacts</h2>
								<div class="panel-toolbar">
									<button
										class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed dropdown-toggle waves-effect waves-themed"
										type="button"
										data-toggle="dropdown"
										aria-haspopup="true"
										aria-expanded="false"
									>
										Add record
									</button>
									<div
										class="dropdown-menu dropdown-menu-right"
									>
										<a
											class="dropdown-item"
											href="#"
											@click.prevent="
												createNewRecord('phones')
											"
											>Add phone number</a
										>
										<a
											class="dropdown-item"
											href="#"
											@click.prevent="
												createNewRecord('emails')
											"
											>Add email</a
										>
										<a
											class="dropdown-item"
											href="#"
											@click.prevent="
												createNewRecord('messengers')
											"
											>Add messenger</a
										>
									</div>
								</div>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<template v-if="record.phones.length">
										<label class="form-label"
											>Phone numbers:</label
										>
										<modal-phones
											:records="record.phones"
											:types="types.phones"
											:in-line="false"
											@setPrimary="setPrimary"
											@setType="setType"
											@deleteRecord="deleteRecord"
											@addRecord="addRecord"
										></modal-phones>
										<hr class="mb-2 mt-2" />
									</template>

									<template v-if="record.emails.length">
										<label class="form-label"
											>Emails:</label
										>
										<modal-emails
											:records="record.emails"
											:in-line="false"
											@setPrimary="setPrimary"
											@deleteRecord="deleteRecord"
											@addRecord="addRecord"
										></modal-emails>
										<hr class="mb-2 mt-2" />
									</template>

									<template v-if="record.messengers.length">
										<label class="form-label"
											>Messengers:</label
										>
										<modal-messengers
											:records="record.messengers"
											:types="types.messengers"
											:in-line="false"
											@setType="setType"
											@deleteRecord="deleteRecord"
											@addRecord="addRecord"
										></modal-messengers>
										<hr class="mb-2 mt-2" />
									</template>

									<!--                                    <div class="form-group">-->
									<!--                                        <label class="form-label">Nickname</label>-->
									<!--                                        <input type="text" class="form-control" placeholder="Truck Nickname">-->
									<!--                                        -->
									<!--                                <span class="help-block">For Dispatch page</span>-->
									<!--                                    </div>-->
									<!--                                    <div class="form-group">-->
									<!--                                        <label class="form-label">Dispatch Color</label>-->
									<!--                                        <input type="text" class="form-control" placeholder="Truck Color">-->
									<!--                                        -->
									<!--                                <span class="help-block">For Dispatch page</span>-->
									<!--                                    </div>-->
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-hdr">
								<h2>Partner</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label
											for="partner_id"
											class="form-label"
											>Partner</label
										>

										<select
											id="partner_id"
											class="form-control"
											data-placeholder="Choose Partner"
											v-model="record.partner_id"
										>
											<option :value="null">
												Not a partner
											</option>
											<option
												v-for="v in types.partners"
												:key="v.id"
												v-bind:value="v.id"
											>
												{{ v.name }}
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-hdr">
								<h2>Notes</h2>
								<div class="panel-toolbar">
									<button
										class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed createNote"
									>
										Add Note
									</button>
								</div>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<modal-notes
										:records="record.notes"
										:in-line="false"
										@addRecord="addNoteRecord"
										@deleteRecord="deleteNoteRecord"
									></modal-notes>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div
				role="tabpanel"
				id="tab-driver"
				aria-labelledby="tab-driver"
				class="tab-pane fade"
			>
				<div class="row" v-if="!loading">
					<div class="col-lg-10 col-xl-4">
						<div class="panel">
							<div class="panel-hdr">
								<h2>Driver license</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<label
											for="driver_start_of_work"
											class="form-label"
											>Start service date</label
										>
										<div class="input-group">
											<input
												id="driver_start_of_work"
												type="text"
												class="form-control"
												v-model="
													record.driver_start_of_work
												"
											/>
										</div>
									</div>

									<div class="form-group">
										<label class="form-label"
											>ID or a Driver License
											Information</label
										>
										<textarea
											class="js-summernote"
											v-model="record.driver_notes"
										></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-12 col-xl-8">
						<attachments
							type="employee"
							:id="record.id"
						></attachments>
					</div>
				</div>
			</div>

			<div
				role="tabpanel"
				id="tab-calendar"
				aria-labelledby="tab-calendar"
				class="tab-pane fade"
			>
				<holidays-truck-employee
					v-if="!loading"
					:records.sync="record.busy_weeks_days.miscs"
					@submit="submit"
				></holidays-truck-employee>
				<year-calendar
					v-if="!loading"
					:dataSource.sync="record.busy_dates"
					@submit="submit"
				></year-calendar>
			</div>
			<div
				role="tabpanel"
				id="tab-signature"
				aria-labelledby="tab-signature"
				class="tab-pane fade"
			>
				<div class="row" v-if="!loading">
					<div class="col-lg-6">
						<div class="panel">
							<div class="panel-hdr">
								<h2>Email</h2>
							</div>
							<div class="panel-container show">
								<div class="panel-content">
									<div class="form-group">
										<textarea
											class="js-summernote"
											v-model="record.signature"
										></textarea>
										<!--                                        <input type="text" class="form-control" placeholder="First name">-->
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
import { BTab, BTabs } from 'bootstrap-vue';
import ModalEmails from '../Order/TabOverview/ClientModal/Emails';
import ModalMessengers from '../Order/TabOverview/ClientModal/Messengers';
import ModalNotes from '../Order/TabOverview/ClientModal/Notes';
import ModalPhones from '../Order/TabOverview/ClientModal/Phones';
import HolidaysTruckEmployee from './HolidaysTruckEmployee';

const YearCalendar = () =>
	import(/* webpackChunkName: "YearCalendar" */ '../Settings/YearCalendar');
const Attachments = () =>
	import(
		/* webpackChunkName: "Attachments" */ '@/components/App/Attachments'
	);

export default {
	name: 'CompanyEmployee',
	components: {
		BTab,
		BTabs,
		Attachments,
		YearCalendar,
		ModalNotes,
		ModalPhones,
		ModalEmails,
		ModalMessengers,
		HolidaysTruckEmployee,
	},
	data() {
		return {
			loading: true,
			updating: false,
			is_changed: false,
			record: {},
			records_orig: {},
			pbxData: null,
			types: {
				phones: [],
				roles: [],
				sales_team: [],
				messengers: {},
				positions: {},
				divisions: {},
				pbx: [],
				partner_id: null,
			},
		};
	},
	computed: {
		computedPbxData() {
			return this.pbxData;
		},
		divisionsWithPbxData() {
			// let pbxData = {};
			if (this.types?.pbx) {
				return this.types.pbx.filter((item) => Number(item.pbx_id) > 0);
			}
			return null;
		},

		userActive: {
			get() {
				return this.record.user ? this.record.user.active : 0;
			},
			set(value) {
				if (!this.record.user) {
					this.record.user = {};
				}
				this.record.user.active = value;
			},
		},
		userEmail() {
			return this.record.user ? this.record.user.email : null;
		},
	},
	watch: {
		'record.busy_weeks_days.miscs': function (val, oldVal) {
			if (oldVal) {
				this.formatCalendar();
			}
		},
		record: {
			handler(val, oldVal) {
				if (!this.is_changed && Object.keys(oldVal).length) {
					this.is_changed = true;
				}
			},
			deep: true,
		},
		computedPbxData: {
			handler(val, oldVal) {
				if (oldVal) this.is_changed = true;
			},
			// immediate: true,
			deep: true,
		},
	},
	mounted() {
		AxiosHelper({
			url: window.location.href,
		})
			.then(({ record, types }) => {
				if (record) {
					this.record = this.formatRecord(record);
					this.types = types;

					let pbxData = {};
					// fill data for pbxData
					if (this.divisionsWithPbxData)
						for (const division of this.divisionsWithPbxData) {
							pbxData[division.pbx_id] = {
								division,
								id: null,
								pbx_ext: '',
								pbx_id: division.pbx_id,
								pbx_show_webrtc: 0,
							};
						}

					if (
						this.record.pbx_data &&
						this.record.pbx_data.length > 0
					) {
						for (const recordPbxData of this.record.pbx_data) {
							pbxData[recordPbxData.pbx_id] = {
								...recordPbxData,
								division:
									pbxData[recordPbxData.pbx_id].division,
							};
						}
					}
					this.pbxData = pbxData;

					this.formatCalendar();
				}
			})
			.finally(() => {
				this.loading = false;
				this.initMasks();
			});
	},
	methods: {
		addNewEmployee() {
			if ($('#add_record').is(':visible')) $('#add_record').slideUp(250);
			else $('#add_record').slideDown(250);
		},
		addNoteRecord(payload) {
			this.record.notes.push(payload);
		},
		addRecord(type, obj) {
			obj.push({
				is_new: true,
				is_primary: 0,
				value: null,
				type_id: 1,
				type: {
					id: 1,
					icon: 'fa-telegram',
				},
			});
			this.$nextTick(() => this.initMasks());
		},
		closeDrop() {
			$('#tab-general h2').trigger('click');
		},
		createNewRecord(type) {
			this.addRecord(type, this.record[type]);
			this.closeDrop();
		},
		deleteNoteRecord(index) {
			this.$delete(this.record.notes, index);
			this.closeDrop();
		},
		deleteRecord(obj, index) {
			this.$delete(obj, index);
			this.closeDrop();
		},
		formatCalendar() {
			let records = [];

			// Генерим PeakDays на текущий год +1
			let fromDate = moment().startOf('year');
			let toDate = fromDate.clone().add(1, 'year').endOf('year');
			let diff = toDate.diff(fromDate, 'days');
			for (let i = 0; i < diff; i++) {
				let day = moment(fromDate).add(i, 'days');
				if (this.record.busy_weeks_days.miscs.includes(day.day())) {
					records.push({
						id: null,
						name: 'Holidays periodic',
						date: day.format('YYYY-MM-DD'),
						startDate: day,
						startTime: '00:00',
						endDate: day,
						endTime: '23:59',
						color: '#b56ce2',
						is_virtual: true,
						randomRef: App.Miscs.generateToken(),
					});
				}
			}

			this.records_orig.forEach((item) => {
				let startDate = moment(item.start_date),
					endDate = moment(item.end_date),
					endTime = endDate.format('HH:mm');

				if (endTime === '00:00') {
					endTime = '23:59';
				}

				records.push({
					id: item.id,
					name: item.reason,
					details:
						startDate.format('hh:mm A') +
						' - ' +
						endDate.format('hh:mm A'),
					startDate: startDate,
					startTime: startDate.format('HH:mm'),
					endDate: endDate,
					endTime,
					randomRef: App.Miscs.generateToken(),
				});
			});

			this.record.busy_dates = records;
		},
		formatRecord(record) {
			this.records_orig = record.busy_dates;

			let roles = [];
			if (record.user && record.user.roles) {
				record.user.roles.forEach((item) => roles.push(item.id));
			}
			record.roles = roles;
			record.send_welcome = 0;

			if (!record.busy_weeks_days) {
				record.busy_weeks_days = {
					miscs: [],
				};
			}

			return record;
		},
		initMasks() {
			let vm = this;
			this.$nextTick(() => {
				$('#tab-general [id^="phones-"]').each(function () {
					Inputmask({ mask: '(999) 999-9999' }).mask(this);
				});
				$('#tab-general [id^="emails-"]').each(function () {
					Inputmask('email', { jitMasking: true }).mask(this);
				});

				let select2 = $('.select2');
				select2.select2();
				select2.on('select2:close', function (e) {
					this.dispatchEvent(
						new Event('change', { target: e.target })
					);
				});

				// flatpickr
				flatpickr('.flatpickr', {
					altInput: true,
					altFormat: 'F j, Y',
					dateFormat: 'Y-m-d',
					// onChange: function (selectedDates, dateStr) {
					//     window.location = '/dispatch?start_date=' + dateStr;
					// },
				});
				// summernote
				$('.js-summernote').summernote({
					height: 200,
					tabsize: 2,
					placeholder: 'Type here...',
					dialogsFade: true,
					toolbar: [
						['style', ['style']],
						['font', ['strikethrough', 'superscript', 'subscript']],
						['font', ['bold', 'italic', 'underline', 'clear']],
						['fontsize', ['fontsize']],
						['fontname', ['fontname']],
						['color', ['color']],
						['para', ['ul', 'ol', 'paragraph']],
						['height', ['height']],
						['table', ['table']],
						['insert', ['link', 'picture', 'video']],
						['view', ['fullscreen', 'codeview', 'help']],
					],
					callbacks: {
						onChange(contents) {
							vm.record.signature = contents;
						},
					},
				});

				let minDate = new Date();
				minDate.setDate(minDate.getDate() - 14);
				flatpickr('#driver_start_of_work', {
					enableTime: false,
					dateFormat: 'Y-m-d',
					altInput: true,
					altFormat: 'F j, Y',
					minDate,
				});
			});
		},
		setPrimary(obj, index) {
			obj = obj.map((v, i) => {
				// Проставляем primary
				v.is_primary = i === index ? 1 : 0;
			});
			this.closeDrop();
		},
		setType(record, id) {
			record.type_id = id;
			this.closeDrop();
		},
		submit() {
			this.updating = true;

			let pbx_data = [];
			if (Object.keys(this.pbxData).length > 0) {
				for (const k of Object.keys(this.pbxData)) {
					pbx_data.push(this.pbxData[k]);
				}
			}

			let busy_dates = this.record.busy_dates
				.slice()
				.filter((item) => !item.is_virtual)
				.map((item) => {
					item.startDate = moment(item.startDate).format(
						'YYYY-MM-DD'
					);
					item.endDate = moment(item.endDate).format('YYYY-MM-DD');

					return item;
				});
			this.record.busy_dates = busy_dates;

			AxiosHelper({
				url: window.location.href + '/save',
				data: { ...this.record, pbx_data },
			})
				.then(({ record, msg }) => {
					if (record) {
						this.record = this.formatRecord(record);

						this.formatCalendar();

						App.Forms.showAlert('success', msg);

						// Костыль для рефреша данных календаря. Это печаль
						this.$nextTick(() =>
							$('.calendar-header .next').trigger('click')
						);
						this.$nextTick(() => {
							$('.calendar-header .prev').trigger('click');
							this.is_changed = false;
						});
					}
				})
				.finally(() => {
					this.updating = false;
				});
		},
	},
};
</script>

<style lang="scss">
.title-class {
	.nav-link {
		color: #0c83e2;
		text-decoration: none;

		&.active {
			color: #fff;
			background-color: #0c83e2;
		}
	}
}
</style>

<template>
	<div class="row">
		<div class="col-xl-12">
			<div class="panel" style="">
				<div
					class="frame-wrap position-absolute w-100 opacity-60 panel-loader"
					:class="{ 'd-none': !loading }"
				>
					<div class="d-flex justify-content-center">
						<div
							class="spinner-border text-info position-absolute"
							style="top: 30%"
							role="status"
						>
							<span class="sr-only">Loading...</span>
						</div>
					</div>
				</div>
				<div class="panel-hdr">
					<h2>Conversations</h2>
					<!--                    <div class="panel-toolbar">-->
					<!--                        <select class="custom-select custom-select-sm" v-model="contactsFilter">-->
					<!--                            <option value="all">All Contacts</option>-->
					<!--                            <option value="myclients">My Leads</option>-->
					<!--                            <option value="unassigned">Unassigned</option>-->
					<!--                        </select>-->
					<!--                        <select class="ml-2 custom-select custom-select-sm" v-model="communicationsFilter">-->
					<!--                            <option value="all">All conversations</option>-->
					<!--                            <option value="unanswered">Unanswered</option>-->
					<!--                        </select>-->
					<!--                    </div>-->
				</div>
				<!--                <div class="panel-container show border border-top-0 border-right-0 border-left-0">-->
				<!--                    <div class="panel-content">-->
				<!--                        <div class="d-flex">-->
				<!--                            <div class="form-group">-->
				<!--                                <label class="form-label" for="example-select">Input Select</label>-->
				<!--                                <select class="form-control" id="example-select">-->
				<!--                                    <option>1</option>-->
				<!--                                    <option>2</option>-->
				<!--                                    <option>3</option>-->
				<!--                                    <option>4</option>-->
				<!--                                    <option>5</option>-->
				<!--                                </select>-->
				<!--                            </div>-->
				<!--                        </div>-->
				<!--                    </div>-->
				<!--                </div>-->
				<div class="panel-container show">
					<div class="d-flex">
						<div
							class="panel-content p-0 border-right position-relative contacts-scroller"
							:class="{ 'overflow-y-hidden': showFilterWindow }"
						>
							<div
								class="frame-wrap position-absolute w-100 h-100 opacity-50"
								v-show="contactRecordsLoading"
								style="z-index: 5"
							>
								<div class="d-flex justify-content-center">
									<div
										class="spinner-border text-info position-absolute"
										style="top: 50%"
										role="status"
									>
										<span class="sr-only">Loading...</span>
									</div>
								</div>
							</div>

							<div
								class="w-100 position-sticky bg-white"
								style="top: 0px; z-index: 3"
							>
								<div class="mb-2">
									<div class="input-group">
										<div class="input-group-prepend">
											<span
												class="input-group-text border-bottom-0 border-top-0 border-left-0 border-right-0 border-left-0 py-1 pr-0 bg-transparent"
											>
												<i class="fal fa-search"></i
											></span>
										</div>
										<input
											type="text"
											v-model="searchValue"
											placeholder="Search customer or Lead #"
											class="form-control form-control-sm border-left-0 border-bottom-0 border-top-0 border-left-0 border-right-0 border-left-0"
										/>
									</div>
								</div>
								<div class="d-flex mt-2">
									<div
										class="ml-3 mr-auto fs-nano cursor-pointer"
										@click="
											$store.commit(
												'communicationsFlow/setFilterWindowState',
												!showFilterWindow
											)
										"
									>
										<i class="fal fa-sliders-h"></i
										><span class="pl-1">Filter</span>
									</div>
									<!--                                    <div class="mr-3 fs-nano cursor-pointer">Total: ?? <i class="fal fa-sort-amount-down"></i></div>-->
								</div>
								<div
									class="mt-2 border-top"
									:class="{ 'mb-2': showFilterWindow }"
								></div>
								<div
									class="bg-white w-100 border-bottom"
									:class="{ 'd-none': !showFilterWindow }"
								>
									<communication-filters />
								</div>
							</div>
							<div
								class="overlayed-modal"
								@click="
									$store.commit(
										'communicationsFlow/setFilterWindowState',
										!showFilterWindow
									)
								"
								:class="{ 'd-none': !showFilterWindow }"
							></div>
							<ul class="chat-contacts" style="">
								<contact-record
									v-for="(event, key) in sortedContacts"
									:event="event"
									:key="key"
									:index="key"
									@select="selectContact"
									:datetime="localDT(event.timestamp)"
								/>
							</ul>
							<infinite-loading
								v-if="!loading"
								:distance="100"
								@infinite="infiniteContactsHandler"
								:identifier="infiniteContactsId"
							>
								<div slot="no-more">
									<h6 class="text-muted mt-3">
										No more results
									</h6>
								</div>
								<div slot="no-results">
									<h6 class="text-muted mt-3">
										No more results
									</h6>
								</div>
							</infinite-loading>
						</div>

						<div class="flex-fill">
							<div
								v-if="selectedContact"
								class="panel-content p-0 bg-faded h-100"
							>
								<div
									class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
									:class="{
										'd-none': !loading && !historyLoading,
									}"
								>
									<div class="d-flex justify-content-center">
										<div
											class="spinner-border text-info position-absolute"
											style="top: 30%"
											role="status"
										>
											<span class="sr-only"
												>Loading...</span
											>
										</div>
									</div>
								</div>
								<div
									class="d-flex bg-white w-100 align-items-center p-0 border border-top-0 border-left-0 border-right-0 flex-shrink-0"
									style="height: 70px"
								>
									<div
										class="d-flex align-items-center w-100 pl-3 px-lg-4 py-2 position-relative"
									>
										<div
											class="d-flex flex-row align-items-center mt-1 mb-1"
										>
											<div class="mr-2 d-inline-block">
												<span
													class="rounded-circle profile-image d-block"
													style="
														background-image: url('/smartadmin/img/demo/avatars/avatar-m.png');
														background-size: cover;
													"
												></span>
											</div>
											<div class="info-card-text">
												<a
													href="javascript:void(0);"
													class="fs-lg text-truncate text-truncate-lg"
													data-toggle="dropdown"
													aria-expanded="false"
												>
													{{ selectedClientTitle }}
													<i
														class="fal fa-angle-down d-inline-block ml-1 fs-md"
													></i>
												</a>
												<div
													class="dropdown-menu"
													style=""
												>
													<a
														class="dropdown-item px-3 py-2"
														href="/assigne"
														v-if="!selectedClient"
														@click.prevent="
															assignModal
														"
														>Assign Contact to
														Client</a
													>
													<a
														class="dropdown-item px-3 py-2"
														href="/create"
														v-if="!selectedClient"
														@click.prevent="
															createNewClientOrder
														"
														>Create Client &
														Order</a
													>
													<a
														class="dropdown-item px-3 py-2"
														href="#"
														v-if="selectedClient"
														@click.prevent="
															createNewOrder
														"
														>Create new Order</a
													>
													<!--                                                    <a class="dropdown-item px-3 py-2" href="#">Block Con</a>-->
													<!--                                                    <a class="dropdown-item px-3 py-2" href="#">Block contact</a>-->
												</div>
												<span
													class="text-truncate text-truncate-md opacity-80"
													v-html="profileBottom"
												></span>
											</div>
										</div>
										<div
											class="ml-auto"
											v-if="
												hasPhones &&
												hasZadarmaApi &&
												hasZadarmaExtension
											"
										>
											<b-dropdown
												size="md"
												right
												variant="info"
												toggle-class="btn-icon no-arrow rounded-circle ml-1 waves-effect waves-themed"
												no-caret
											>
												<template #button-content>
													<i
														class="fal fa-phone fs-md"
													></i>
												</template>
												<b-dropdown-item-button
													v-for="phone in phones"
													:key="phone.id"
													@click="
														callPBXv2(phone.value)
													"
												>
													Call to
													{{
														phone.value
															| formatPhone
													}}
												</b-dropdown-item-button>
											</b-dropdown>
											<!--                                            <a href="javascript:void(0);" class="btn btn-outline-info btn-icon rounded-circle ml-1 waves-effect waves-themed">-->
											<!--                                                <i class="fal fa-phone fs-md"></i>-->
											<!--                                            </a>-->
										</div>
										<div class="ml-4">
											<button
												v-if="showMarkButton"
												@click="markAsRead"
												type="button"
												class="btn btn-sm btn-outline-info waves-effect waves-themed"
											>
												Mark as answered
											</button>
										</div>
									</div>
									<!-- button for mobile -->
									<a
										href="javascript:void(0);"
										class="px-3 py-2 d-flex d-lg-none align-items-center justify-content-center mr-2 btn waves-effect waves-themed"
										data-action="toggle"
										data-class="slide-on-mobile-left-show"
										data-target="#js-chat-contact"
									>
										<i
											class="fal fa-ellipsis-v h1 mb-0"
										></i>
									</a>
									<!-- end button for mobile -->
								</div>
								<div class="history-container">
									<div
										class="position-relative history-scroller"
										ref="historyScroller"
									>
										<infinite-loading
											force-use-infinite-wrapper=".history-scroller"
											v-if="neededFlowHandler"
											:identifier="infiniteId"
											direction="top"
											@infinite="infiniteFlowHandler"
											ref="flowInfiniteLoading"
										>
											<div slot="no-more">
												<h6 class="text-muted mt-3">
													no more history
												</h6>
											</div>
											<div slot="no-results">
												<h6 class="text-muted mt-3">
													All history loaded
												</h6>
											</div>
										</infinite-loading>

										<CommunicationHistoryFlow
											@assign="assignModal"
											@updated="historyLoading = false"
										/>
										<!--                                        <ul class="activity-timeline-lg mx-4">-->
										<!--                                            <template v-for="(record, key) in sortedFlow">-->
										<!--                                                <li v-if="record.divider">-->
										<!--                                                    <div class="d-flex justify-content-center">-->
										<!--                                                        <div class="bg-white shadow-3 px-3 py-1 rounded-pill fs-xs fw-500">-->
										<!--                                                            {{ record.divider }}-->
										<!--                                                        </div>-->
										<!--                                                    </div>-->
										<!--                                                </li>-->

										<!--                                                <component :is="detectComponent(record)"-->
										<!--                                                           :key=key :index="key" :record="record" :datetime="localDT(record.timestamp)" interface="flow"-->
										<!--                                                           @assign="assignModal"/>-->
										<!--                                            </template>-->
										<!--                                        </ul>-->
									</div>
									<div
										ref="inputArea"
										class="panel-interface-input"
									>
										<!--                                        <input-interface-task v-if="mode.current == 'task' && tasksEnvironment" :mode="mode"/>-->
										<!--                                        <input-interface-note v-if="mode.current == 'note'" :mode="mode"/>-->
										<input-interface-sms
											v-if="
												hasPhones &&
												mode.current == 'sms'
											"
											:mode="mode"
											:phones="phones"
										/>
										<input-interface-email
											v-if="
												hasEmails &&
												mode.current == 'email'
											"
											:mode="mode"
											:emails="emails"
										/>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<b-modal
				v-model="modalShow"
				@hidden="resetModal"
				id="assign-order"
				ref="assign-order"
				hide-backdrop
				size="lg"
				centered
				header-class="d-none"
				footer-class="d-none"
			>
				<div
					ref="modalContent"
					class="panel-content position-relative border-faded border-0 mt-0 pt-0"
				>
					<div class="mb-1 text-muted fs-sm fw-500">
						{{ modalHeadText }}
					</div>
					<vue-select2
						v-if="modalShow"
						v-model="orderID"
						:config="configSelect2ForOrder()"
					></vue-select2>
					<div
						class="d-flex border align-items-center border-top-0 py-2 px-2 bg-white border border-top-0 rounded-bottom"
					>
						<!--                        <div class="">-->
						<!--                            <button class="btn btn-danger btn-sm ml-auto waves-effect waves-themed mr-1" @click="addToIgnore">-->
						<!--                                Add to Ignore list-->
						<!--                            </button>-->
						<!--                        </div>-->
						<div class="ml-auto">
							<!--                            <button class="btn btn-secondary btn-sm ml-auto waves-effect waves-themed mr-1" @click="createNew">-->
							<!--                                Create new Client & Order-->
							<!--                            </button>-->
							<button
								class="btn btn-primary btn-sm ml-auto waves-effect waves-themed"
								:disabled="!orderID"
								@click="assignWithClient"
							>
								Assign & Update Client
							</button>
						</div>
					</div>
				</div>
			</b-modal>
		</div>
	</div>
</template>

<script>
import { zadarmaRequest } from '@/api/crm';
import formatDateTime from '@/filters/formatDateTime.filter';
import formatPhone from '@/filters/formatPhone.filter';
import { axiosPromise } from '@/helpers/axiosPromise';
import { debounce } from '@/helpers/functions';
import CommunicationFilters from '@components/App/Communications/CommunicationFilters.vue';
import CommunicationHistoryFlow from '@components/App/Communications/CommunicationHistoryFlow.vue';
import ContactRecord from '@components/App/Communications/ContactRecord'; // import Empty from "@components/Order/TabOverview/CommunicationPanel/History/Empty";
import InputInterfaceEmail from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceEmail';
import InputInterfaceSms from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceSms'; // import Call from "@components/Order/TabOverview/CommunicationPanel/History/Call";
// import TwilioSms from "@components/Order/TabOverview/CommunicationPanel/History/TwilioSms";
// import ConversationMark from "@components/Order/TabOverview/CommunicationPanel/History/ConversationMark";
// import Email from "@components/Order/TabOverview/CommunicationPanel/History/Email";
// import OrderCreateEvent from "@components/Order/TabOverview/CommunicationPanel/History/OrderCreateEvent";
import vueSelect2 from '@components/VueSelect2';
import { BDropdown, BDropdownItemButton, BModal, VBModal } from 'bootstrap-vue';
import cloneDeep from 'lodash.clonedeep';
import InfiniteLoading from 'vue-infinite-loading';

export default {
	name: 'Communications',
	filters: {
		formatPhone,
	},
	computed: {
		communicationFilter() {
			return this.$store.state.communicationsFlow.filters;
		},
		contactRecordsLoading() {
			return this.$store.state.communicationsFlow.contactsRecordsLoading;
		},
		showMarkButton() {
			return !this.selectedContact.isAnswered;
			// return true;
		},
		hasZadarmaApi() {
			return this.$store.state.zadarma?.hasApi;
		},
		hasZadarmaExtension() {
			return this.$store.state.zadarma?.hasExtension;
		},
		hasPhones() {
			return this?.phones && this.phones.length > 0;
		},
		phones() {
			if (this.selectedClient) {
				return this.selectedClient.phones;
			}
			if (
				this.selectedContact &&
				(this.selectedContact.type == 'TwilioSms' ||
					this.selectedContact.type == 'CallsEvents')
			) {
				return [{ value: this.selectedContact.channelContact }];
			}
			return [];
		},
		emails() {
			if (this.selectedClient) {
				return this.selectedClient.emails;
			}
			if (
				this.selectedContact &&
				this.selectedContact.type == 'Message'
			) {
				//return [this.selectedContact.channelContact]
			}
			return [];
		},
		hasEmails() {
			return this?.emails && this.emails.length > 0;
		},
		managerID() {
			return this.$store.state.user?.id;
		},
		divisionID() {
			return this.$store.state.divisionID;
		},
		// communicationsFilter: {
		//     get() {
		//         return this.$store.state.communicationsFlow.filters.communications;
		//     },
		//     set(value) {
		//         this.$store.commit('communicationsFlow/setFiltersCommunications', value)
		//     }
		// },
		// contactsFilter: {
		//     get() {
		//         return this.$store.state.communicationsFlow.filters.contacts;
		//     },
		//     set(value) {
		//         this.$store.commit('communicationsFlow/setFiltersContacts', value)
		//     }
		// },
		profileBottom() {
			if (this.selectedClient) {
				return (
					`<a href="/orders?filter-client[]=${this.selectedClient.id}" target="_blank">Orders: ` +
					this.selectedClient.orders_count +
					'</a>'
				);
			}
			return 'Contact';
		},
		selectedContact() {
			return this.$store.state.communicationsFlow.selectedContact;
		},
		sortedContacts() {
			// return this.$store.state.communicationsFlow.contacts;
			// return this.$store.state.communicationsFlow .contacts;
			return this.$store.getters[
				'communicationsFlow/sortedContactsRecords'
			];
		},
		selectedClient() {
			return this.$store.state.communicationsFlow.selectedContact?.client;
		},
		selectedClientTitle() {
			let title =
				this.$store.state.communicationsFlow.selectedContact
					?.channelContact;
			if (this.selectedClient)
				title =
					this.selectedClient.name + ' ' + this.selectedClient.lname;
			return title;
		},
		sortedFlow() {
			return this.$store.getters['communicationsFlow/sortedFlowRecords'];
		},
		sortedHistoryRecords() {
			// if (this.filterMode == 'all')
			//     return this.$store.getters["communicationsFlow/getAllRecords"];
			// if (this.filterMode == 'unassigned')
			//     return this.$store.getters["communicationsFlow/getUnassignRecords"];
			// // return this.$store.state.communicationsFlow.records.filter(record => !record.orderID);
			return [];
		},
		timezone() {
			return this.$store.state.communicationsFlow.timezone;
		},
		hasMoreContacts() {
			return this.$store.state.communicationsFlow.moreContacts;
		},
		hasMoreFlow() {
			return this.$store.state.communicationsFlow.moreFlow;
		},
		filterDropdownText() {
			if (this.filterMode && this.filterModes[this.filterMode])
				return 'Show: ' + this.filterModes[this.filterMode];
			return '';
		},
		modalHeadText() {
			let text = '';
			if (this.assignedRecordKey !== null) {
				const record =
					this.sortedHistoryRecords[this.assignedRecordKey];
				if (record.type == 'CallsEvents') {
					text = 'Phone: ' + record.item.caller_id;
				} else if (record.type == 'Message') {
					text = 'Email: ' + record.item.miscs.from.email;
				}
			}
			return text;
		},
		showFilterWindow() {
			return this.$store.state.communicationsFlow.showFilterWindow;
		},
		infiniteContactsId() {
			return this.$store.state.communicationsFlow.infiniteContactsId;
		},
	},
	data: () => ({
		searchValue: '',
		searchValueDebounced: '',
		componentKey: 0,
		mode: {
			// panelInterfaceClass: 'panel-interface-default',
			current: 'sms',
			list: {
				// task: {
				//     title: 'Task',
				// },
				sms: {
					title: 'SMS',
				},
			},
		},
		filterModes: { contacts: 'all', all: 'All' },
		filterMode: null,
		//filterDropdownText: 'Show: '.,
		assignedRecordKey: null,
		assignedRecordUid: null,
		modalShow: false,
		orderID: null,
		flowPage: 1,
		infiniteId: +new Date(),
		// infiniteContactsId: 1,
		prevRecordMoment: false,
		neededFlowHandler: false,
		loading: true,
		historyLoading: true,
	}),
	async mounted() {
		try {
			await this.$store.dispatch('fetchEnvironment', {
				page: 'communications',
			});
		} catch (e) {
			console.log('fetchEnvironment error', e);
		}
		this.setFilterMode('all');
		// this.$store.dispatch('communicationsFlow/fetchContactsRecords').then(() => {
		this.loading = false;
		// })
		if (window.Echo)
			window.Echo.channel('communications.' + this.divisionID).listen(
				'.communications.event',
				(e) => {
					if (
						this.communicationsFilter == 'unanswered' &&
						!this.isUnanswered(e.data)
					)
						return true;
					if (
						this.contactsFilter == 'myclients' &&
						!e.data.managers.includes(this.managerID)
					) {
						return true;
					}
					if (this.contactsFilter == 'unassigned' && e.data.client) {
						return true;
					}
					// console.log('Event', e.data);
					this.$store.commit(
						'communicationsFlow/pushUpdateContactRecord',
						e.data
					);
				}
			);
	},
	methods: {
		makeSearch(term) {
			this.loading = true;
			this.$store.commit('communicationsFlow/updateFilters', {
				untill: null,
				ignoreList: null,
				searchTerm: term,
			});
			this.$store.commit('communicationsFlow/clearContactsRecords');
			this.$store
				.dispatch('communicationsFlow/fetchContactsRecords')
				.then(() => {
					this.loading = false;
				})
				.catch(() => {
					this.loading = false;
				});
		},
		callPBXv2(phone, event) {
			return zadarmaRequest('callback', { phone })
				.then((data) => {
					if (data.success === true)
						App.Forms.showAlert(
							'success',
							'Сommutation request with ' + phone
						);
					else App.Forms.simpleErrors(data);
				})
				.catch((error) => {
					App.Forms.showAlert(
						'error',
						'Сommutation error with ' + phone
					);
				});
		},

		isUnanswered(record) {
			if (
				record.type == 'CallsEvents' &&
				record.item.event == 'NOTIFY_END' &&
				record.item.disposition != 'answered'
			) {
				return true;
			}
			if (record.type == 'ConversationMark') {
				return true;
			}
			if (
				record.type == 'TwilioSms' &&
				record.item.direction == 'inbound'
			) {
				return true;
			}
			if (record.type == 'Message' && record.item.tag == 'inbox') {
				return true;
			}
			return false;
		},

        markAsAnswered(uid) {
            this.$store.commit('communicationsFlow/changeCurrentContactAnsweredState', {
                uid,
                isAnswered: true,
            })
        },
		selectContact(uid) {
			this.neededFlowHandler = false;
			if (this.$refs.flowInfiniteLoading) {
				this.$refs.flowInfiniteLoading.stateChanger.reset();
				this.historyLoading = true;
			}
			// console.log(this.infiniteId);
			this.$store.commit('communicationsFlow/setSelected', uid);
			this.$nextTick(() => {
				this.historyLoading = true;
				this.fetchFirstPage();
			});
			// if (this.$refs.flowInfiniteLoading) {
			//     this.$refs.flowInfiniteLoading.stateChanger.reset();
			// }
			// this.loading = true;
			// this.$store.dispatch('communicationsFlow/fetchFlow');
			//console.log('select '+ index);
		},
		setFilterMode(value) {
			this.filterMode = value;
		},
		preparePayload() {
			try {
				const record = this.selectedContact;
				if (!record.channelContact) {
					throw new Error(
						'Selected relation does not have "channel contact"'
					);
				}
				const payload = {
					orderID: +this.orderID,
					relation: '',
					value: String(record.channelContact),
				};

				const phonesRel = [
					'CallsEvents',
					'TwilioSms',
					'EventAfterCall',
				].map(normalize);
				const emailsRel = ['Message'].map(normalize);
				const type = String(record.type).toLowerCase();

				if (phonesRel.includes(type)) {
					payload.relation = 'phones';
				} else if (emailsRel.includes(type)) {
					payload.relation = 'emails';
				} else {
					throw new Error(
						`Relation has unprocessed type ${record.type}`
					);
				}

				function normalize(v) {
					return v.toLowerCase();
				}

				return payload;
			} catch (e) {
				const msg = e instanceof Error ? e.message : String(e);
				console.log('preparePayload error with record', record);
				App.Forms.showAlert('error', 'Bad relation', msg);
			}
		},
		addToIgnore() {
			return axiosPromise(
				axios.post(
					'/communications/addIgnoreRecord',
					this.preparePayload()
				)
			)
				.then((data) => {
					// this.$store.commit('communicationsFlow/removeRecord', {uid: this.assignedRecordUid});
					this.$refs['assign-order'].hide();
					App.Forms.showAlert('success', 'Done', 'Added to ignore');
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});
		},
		createNewOrder() {
			$('#page-spinner').removeClass('d-none');
			this.loading = true;
			return axiosPromise(
				axios.post('/communications/createOrderToClient', {
					clientID: this.selectedClient.id,
				})
			)
				.then((data) => {
					window.open('/orders/' + data.orderID, '_blank');
					$('#page-spinner').addClass('d-none');
					this.loading = false;
					// this.$store.commit('communicationsFlow/assignRecordOrder', {orderID: +data.orderID, uid: this.assignedRecordUid});
					// App.Forms.showAlert('success', 'Done', 'Created & Assigned')
					this.$refs['assign-order'].hide();
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
					$('#page-spinner').addClass('d-none');
					this.loading = false;
				});
		},
		createNewClientOrder() {
			$('#page-spinner').removeClass('d-none');
			this.loading = true;
			return axiosPromise(
				axios.post(
					'/communications/createClientOrderRelationRecord',
					this.preparePayload()
				)
			)
				.then((data) => {
					this.$store.commit(
						'communicationsFlow/assignRecordClient',
						{ uid: this.selectedContact.uid, client: data.client }
					);
					window.open('/orders/' + data.orderID, '_blank');
					this.loading = false;
					$('#page-spinner').addClass('d-none');
					this.selectContact(this.selectedContact.uid);
					// this.$store.commit('communicationsFlow/assignRecordOrder', {orderID: +data.orderID, uid: this.assignedRecordUid});
					// App.Forms.showAlert('success', 'Done', 'Created & Assigned')
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
					$('#page-spinner').addClass('d-none');
					this.loading = false;
				});
		},
		assignWithClient() {
			$('#page-spinner').removeClass('d-none');
			return axiosPromise(
				axios.post(
					'/communications/addClientRelationRecord',
					this.preparePayload()
				)
			)
				.then((data) => {
					//update record client
					this.$store.commit(
						'communicationsFlow/assignRecordClient',
						{
							uid: this.selectedContact.uid,
							client: data.client,
						}
					);
					// this.$store.commit('communicationsFlow/assignRecordOrder', {orderID: +this.orderID, uid: this.assignedRecordUid});
					App.Forms.showAlert('success', 'Done', 'Assigned');
					this.$refs['assign-order'].hide();
					this.loading = false;
					$('#page-spinner').addClass('d-none');
					this.selectContact(this.selectedContact.uid);
				})
				.catch((error) => {
					$('#page-spinner').addClass('d-none');
					App.Forms.simpleErrors(error);
				});
		},

		resetModal() {
			this.assignedRecordUid = null;
			this.assignedRecordKey = null;
			this.orderID = null;
		},
		configSelect2ForOrder() {
			// console.log($(this.$refs.modalContent).parents('.modal-body:first'));
			const config = {
				dropdownParent: $('#assign-order').find('.modal-body:first')[0],
				containerCssClass:
					'border-bottom-left-radius-0 border-bottom-right-radius-0',
				selectionCssClass:
					'border-bottom-left-radius-0 border-bottom-right-radius-0',
				placeholder: 'Search Order (by id, client name, phones, email)',
				allowClear: true,
				ajax: {
					url: '/orders/autocomplete',
					method: 'POST',
					delay: 400,
					dataType: 'json',
					data(params) {
						return {
							q: params.term, // search term
							page: params.page || 1,
						};
					},
					// processResults(response, params) {
					//     return {
					//         results: response.data.results,
					//         pagination: response.data.pagination
					//     };
					// },
					// cache: true
				},
				escapeMarkup(markup) {
					return markup;
				},
				minimumInputLength: 0,
				templateResult: App.Miscs.templateSelect2Order,
				templateSelection: function (v) {
					if (v.id) return 'Order #' + v.id;
					// else if (el.data('route').includes('client') && v.name) {
					//     return v.name + ' ' + v.lname;
					// }
					return v.text;
				},
			};
			return config;
		},
		assignModal() {
			//this.assignedRecordUid = this.sortedHistoryRecords[key].uid;
			//this.assignedRecordKey = key;
			this.$refs['assign-order'].show();
			// console.log('1234')
			// this.$bvModal.show('assign-order')
		},
		infiniteContactsHandler($state) {
			// $state.complete();
			this.$store
				.dispatch('communicationsFlow/fetchContactsRecords')
				.then(() => {
					if (this.hasMoreContacts) $state.loaded();
					else $state.complete();
					this.loading = false;
				})
				.catch((err) => {
					console.log('got error: ', err);
					$state.complete();
				});
		},
		jumpToLatest() {
			if (this.$refs?.historyScroller)
				this.$refs.historyScroller.scrollTop =
					this.$refs.historyScroller.scrollHeight;
		},
		fetchFirstPage() {
			return this.$store
				.dispatch('communicationsFlow/fetchFlow')
				.then(() => {
					this.$nextTick(() => {
						this.jumpToLatest();
						this.loading = false;
						//console.log('fetchFirstPage disable historyLoading')
						// this.historyLoading = false;
						if (this.hasMoreFlow) {
							this.flowPage += 1;
							this.neededFlowHandler = true;
						}
					});
				});
		},
		infiniteFlowHandler($state) {
			// this.loading = true;
			this.$store.dispatch('communicationsFlow/fetchFlow').then(() => {
				if (this.hasMoreFlow) {
					$state.loaded();
					this.flowPage += 1;
				} else {
					$state.complete();
				}
				this.$nextTick(() => {
					this.loading = false;
					// console.log('fetchFlow disable historyLoading');
					// this.historyLoading = false;
				});
			});
		},
		localDT(timestampUTC) {
			return formatDateTime(timestampUTC, this.timezone);
		},
		markAsRead() {
			// $('#page-spinner').removeClass('d-none');
			this.loading = true;
			return axiosPromise(
				axios.post('/communications/markConversation', {
					conversation: this.selectedContact,
					type: 'read',
				})
			)
				.then((data) => {
					// this.$store.commit('communicationsFlow/changeCurrentContactAnsweredState', {uid: this.selectedContact.uid, isAnswered: true});
					this.$store.commit('communicationsFlow/resetFlow');
					// this.flowPage = 1;
					// this.infiniteId += 1;
					this.$store
						.dispatch('communicationsFlow/fetchFlow')
						.then(() => {
                            this.markAsAnswered(this.selectedContact.uid);
                            this.selectContact(this.selectedContact.uid);
							// this.$store.commit(
							// 	'communicationsFlow/setSelected',
							// 	this.selectedContact.uid
							// );
						})
						.then(() => {
							// $('#page-spinner').addClass('d-none');
							this.loading = false;
						});
					// this.$store.commit('communicationsFlow/assignRecordClient', {index: this.selectedContact.recordsIndex, client: data.client});
					// window.open('/orders/' + data.orderID, '_blank');
					// this.loading = false;
					// $('#page-spinner').addClass('d-none');
					// this.selectContact(this.selectedContact.recordsIndex);
					// this.$store.commit('communicationsFlow/assignRecordOrder', {orderID: +data.orderID, uid: this.assignedRecordUid});
					// App.Forms.showAlert('success', 'Done', 'Created & Assigned')
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
					$('#page-spinner').addClass('d-none');
					this.loading = false;
				});
		},
		restoreFilters() {
			if (this.$store.state.communicationsFlow.filtersBackup) {
				console.log('need restore!');
				this.$store.commit(
					'communicationsFlow/setFilters',
					cloneDeep(
						this.$store.state.communicationsFlow.filtersBackup
					)
				);
				this.$store.commit('communicationsFlow/setFiltersBackup', null);
			}
		},
		backupFilters() {
			this.$store.commit(
				'communicationsFlow/setFiltersBackup',
				cloneDeep(this.$store.state.communicationsFlow.filters)
			);
		},
	},
	directives: {
		'b-modal': VBModal,
	},
	components: {
		CommunicationFilters,
		vueSelect2,
		InputInterfaceEmail,
		InputInterfaceSms,
		InfiniteLoading,
		BModal,
		ContactRecord,
		CommunicationHistoryFlow,
		BDropdown,
		BDropdownItemButton,
		// CommunicationUnanswered
	},
	watch: {
		showFilterWindow(newVal, oldVal) {
			if (newVal) this.backupFilters();
			else if (!newVal) this.restoreFilters();
		},
		searchValue: debounce(function (newVal) {
			if (String(newVal).length == 0 || String(newVal).length >= 3)
				this.makeSearch(newVal);
		}, 1200),
		phones() {
			this.componentKey += 1;
		},
		emails() {
			this.componentKey += 1;
		},
		communicationsFilter() {
			this.loading = true;
			this.$store.commit('communicationsFlow/clearContactsRecords');
			// this.$store.dispatch('communicationsFlow/fetchContactsRecords').then(() => this.loading = false);
		},
		contactsFilter() {
			this.loading = true;
			this.$store.commit('communicationsFlow/clearContactsRecords');
			// this.$store.dispatch('communicationsFlow/fetchContactsRecords').then(() => this.loading = false);
		},
		assignedRecordUid(newVal, oldVal) {
			//console.log(newVal);
			if (newVal !== null) {
				this.$store.commit('communicationsFlow/updateSelected', {
					uid: newVal,
					value: true,
				});
			} else
				this.$store.commit('communicationsFlow/updateSelected', {
					uid: oldVal,
					value: false,
				});
		},
	},
};
</script>

<style lang="scss">
.overlayed-modal {
	position: absolute; /* Stay in place */
	z-index: 1; /* Sit on top */
	left: 0;
	top: 0;
	width: 100%; /* Full width */
	height: 100%; /* Full height */
	//overflow: auto; /* Enable scroll if needed */
	background-color: rgb(0, 0, 0); /* Fallback color */
	background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
}

.sticky-panel {
	position: -webkit-sticky;
	position: sticky;
	top: 114px;
}

.contacts-scroller {
	overflow-y: auto;
	height: 75vh;
	//width: 350px !important;
	flex: 0 0 350px;

	&.overflow-y-hidden {
		overflow-y: hidden !important;
	}

	&::-webkit-scrollbar {
		width: 6px;
		height: 6px;
	}

	&::-webkit-scrollbar-track {
		//border-radius: 10px;
		background: rgba(0, 0, 0, 0.1);
	}

	&::-webkit-scrollbar-thumb {
		//border-radius: 10px;
		background: rgba(0, 0, 0, 0.2);
	}

	&::-webkit-scrollbar-thumb:hover {
		background: rgba(0, 0, 0, 0.25);
	}

	&::-webkit-scrollbar-thumb:active {
		background: rgba(0, 0, 0, 0.9);
	}
}

.history-container {
	display: grid;
	grid-template-rows: 1fr 0fr;
	height: calc(75vh - 70px);
}

.history-scroller {
	overflow-y: auto;

	&::-webkit-scrollbar {
		width: 6px;
		height: 6px;
	}

	&::-webkit-scrollbar-track {
		//border-radius: 10px;
		background: rgba(0, 0, 0, 0.1);
	}

	&::-webkit-scrollbar-thumb {
		//border-radius: 10px;
		background: rgba(0, 0, 0, 0.2);
	}

	&::-webkit-scrollbar-thumb:hover {
		background: rgba(0, 0, 0, 0.25);
	}

	&::-webkit-scrollbar-thumb:active {
		background: rgba(0, 0, 0, 0.9);
	}
}

ul.activity-timeline-lg {
	list-style-type: none;
	position: relative;
	padding-inline-start: 0px;

	//&:before {
	//    content: ' ';
	//    background: #e5e5e5;
	//    display: inline-block;
	//    position: absolute;
	//    left: 26px;
	//    width: 2px;
	//    height: 100%;
	//    z-index: 3;
	//}

	& > li {
		margin: 20px 0;
		padding-left: 0px !important;
		position: relative;

		&.inbound-block {
			justify-content: flex-start;
			padding-left: 0px;

			& > .card {
				max-width: 75%;

				& > .card-header {
					background-color: #fff;
				}
			}
		}

		&.outbound-block {
			justify-content: flex-end;
			padding-left: 0px;
			//margin-left: -39px;
			& > .card {
				max-width: 75%;
				background-color: #ebffe2;

				& > .card-header {
					background-color: #ebffe2;
				}
			}
		}

		& .activity-timeline-icon-block {
			margin-right: 5px;

			&.btn-lg {
				& > .fa-2x {
					position: relative;
					top: 7px;
				}
			}
		}

		& > .activity-timeline-icon {
			display: inline-block;
			position: absolute;
			left: -39px;
			margin-top: -5px;
			z-index: 4;
			//width: calc(2.2rem + 4px);
			//line-height: 2.2em;

			&.btn-lg {
				& > .fa-2x {
					position: relative;
					top: 7px;
				}
			}

			//&> i.fa-2x {
			//    position: relative;
			//    top: 5px;
			//}
		}
	}

	.btn-outline-bg-white {
		background-color: #fff;

		&:hover {
			color: unset;
			//background-color: initial;
		}
	}
}

.chat-contacts {
	padding: 0;
	margin: 0;
	list-style: none;
	position: relative;

	li {
		position: relative;
		//background: $white;

		&.unread {
			background-color: #fffaee;

			& .name {
				font-weight: 500;
			}
		}

		> :first-child {
			color: #222;
			border-bottom: 1px solid rgba(0, 0, 0, 0.06);

			&:hover {
				text-decoration: none;
				background-image: linear-gradient(
					rgba(29, 33, 41, 0.03),
					rgba(29, 33, 41, 0.04)
				);
			}

			&:focus {
				text-decoration: none;
			}

			> span {
				position: relative;

				> span {
					/* IE fix */
					display: block;
				}
			}
		}

		&:last-child {
			> a {
				border: 0;
			}
		}

		.name {
			//color: #222;
			font-weight: 400;
			font-size: 0.8rem;
		}

		&.selected {
			background-color: #1dc9b7;

			.text-muted,
			a {
				color: #fff !important;
			}
		}
	}

	//.msg-a,
	//.msg-b {
	//    color: lighten($black, 33.5%);
	//}

	//&:not(.notification-loading) {
	//
	//    &:before {
	//        content: "No new messages";
	//        position: absolute;
	//        top: 0;
	//        left: 0;
	//        z-index: 0;
	//        padding: 1.5rem;
	//        width: 100%;
	//        display: block;
	//    }
	//
	//}
}
</style>

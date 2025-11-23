<template>
	<div class="panel">
		<div class="panel-hdr" v-if="interface != 'payments'">
			<h2>
				Client
				<span
					v-if="loading"
					role="status"
					aria-hidden="true"
					class="spinner-border spinner-border-sm ml-2"
				></span>
				<span v-if="!loading && clientID" class="ml-2"
					>#{{ clientID }}</span
				>
			</h2>
			<div class="panel-toolbar" v-if="!loading">
				<template v-if="client">
					<button
						v-if="estimateType == 'interstate'"
						ref="signatureRequest"
						:disabled="signatureRequestWait"
						class="btn btn-md btn-primary mr-1 shadow-0 waves-effect waves-themed"
						title="Signature request"
						@click="signtureRequest"
					>
						<span
							v-if="signatureRequestWait"
							class="spinner-border spinner-border-sm"
							role="status"
							aria-hidden="true"
						></span>
						Signature <i class="fas fa-signature"></i>
					</button>
					<a
						v-if="canEditOrder"
						target="_blank"
						data-toggle="tooltip"
						:href="orderHash"
						title="Customer page"
						class="btn btn-md btn-icon btn-warning mr-1 shadow-0 waves-effect waves-themed"
					>
						<i class="fas fa-external-link"></i>
					</a>
					<button
						v-if="canEditClient"
						class="btn btn-md btn-secondary shadow-0 waves-effect waves-themed"
						type="button"
						@click="clientEditModal()"
					>
						Edit
					</button>
					<button
						v-if="canAddClient"
						class="btn btn-md btn-secondary shadow-0 waves-effect waves-themed"
						type="button"
						@click="chooseClientModal()"
					>
						Choose or Create New
					</button>
					<button
						v-if="canEditClient || canAddClient"
						class="btn btn-toolbar-master waves-effect waves-themed"
						data-toggle="dropdown"
						aria-expanded="false"
					>
						<i class="fal fa-ellipsis-v"></i>
					</button>
					<div
						class="dropdown-menu dropdown-menu-animated dropdown-menu-right"
					>
						<button
							v-if="canEditClient"
							class="dropdown-item"
							type="button"
							@click="clientEditModal()"
						>
							Edit
						</button>
						<button
							v-if="canAddClient"
							class="dropdown-item"
							type="button"
							@click="chooseClientModal()"
						>
							Choose or Create New
						</button>
					</div>
				</template>
				<button
					v-else-if="canAddClient"
					class="btn btn-sm btn-secondary shadow-0 waves-effect waves-themed"
					type="button"
					@click="chooseClientModal()"
				>
					Choose or Create
				</button>
			</div>
		</div>
		<div
			v-if="!loading && clientID && client"
			class="panel-container collapse show"
		>
			<div class="panel-content">
				<div class="d-flex flex-row mb-2 align-items-center">
					<div class="align-middle mr-2">
						<button
							class="ml-0 btn btn-default btn-md btn-icon rounded-circle"
						>
							{{ clientIcon(client) }}
						</button>
					</div>
					<div>
						<h5 class="mb-0 text-dark fw-500">
							{{ client.name }} {{ client.lname }}
						</h5>
					</div>
				</div>

				<div class="client-margin">
					<div class="phones-box mt-2">
						<div
							v-for="v in client.phones"
							:key="v.id"
							class="mb-1"
						>
							<v-popover
								v-if="hasZadarmaApi"
								offset="0px"
								placement="bottom"
								:autoHide="true"
								:hideOnTargetClick="true"
								innerSelector="v-tooltip-inner v-popover-inner v-client-inner"
							>
								<!-- This will be the popover target (for the events and position) -->
								<!--                                <a class="tooltip-target" @click.prevent.stop="callPBX(v.value, $event)" :href="'tel:+'+v.value">-->
								<span>
									<i
										class="fas fa-mobile-alt text-muted mr-2 fs-md"
										:class="{ 'text-dark': v.is_primary }"
									></i>
									<span class="phone-link">{{
										v.value | formatPhone
									}}</span>
								</span>
								<!--                                <button class="tooltip-target b3">Click me</button>-->
								<!-- This will be the content of the popover -->
								<template slot="popover">
									<div
										class="btn-group-vertical"
										role="group"
										aria-label="Vertical button group"
									>
										<button
											v-if="hasZadarmaExtenstion"
											v-v-close-popover
											type="button"
											@click="
												callPBXv2(v.value, 'zadarma')
											"
											class="btn btn-outline-secondary waves-effect waves-themed"
										>
											Zadarma: Make a call
										</button>
										<button
											v-if="hasZadarmaExtenstion"
											v-v-close-popover
											type="button"
											@click="
												callPBXv2(v.value, 'twilio')
											"
											class="btn btn-outline-secondary waves-effect waves-themed"
										>
											Twilio: Make a call
										</button>
										<button
											v-v-close-popover
											type="button"
											@click="sendSMSModal(v.value)"
											class="btn btn-outline-secondary waves-effect waves-themed"
										>
											Twilio: Send SMS
										</button>
									</div>
								</template>
							</v-popover>
							<a v-else :href="'tel:+' + v.value">
								<i
									class="fas fa-mobile-alt text-muted mr-2 fs-md"
									:class="{ 'text-dark': v.is_primary }"
								></i>
								<span>{{ v.value | formatPhone }}</span>
							</a>
							<!--                            <span class="color-success-600"><i class="fas fa-phone-square-alt"></i></span>-->
						</div>
					</div>

					<div v-if="client.emails" class="emails-box mt-2">
						<div
							v-for="v in client.emails"
							:key="v.id"
							class="mb-1"
						>
							<i
								class="fas fa-envelope text-muted mr-1 fs-md"
								:class="{ 'text-dark': v.is_primary }"
							></i>
							<a :href="'mailto:' + v.value" target="_blank">
								{{ v.value }}
							</a>
						</div>
					</div>

					<div v-if="client.messengers" class="emails-box mt-2">
						<div
							class="mb-1"
							v-for="v in client.messengers"
							:key="v.id"
						>
							<i
								:class="
									'text-muted mr-2 fs-md fab ' + v.type.icon
								"
							></i>
							{{ v.value }}
						</div>
					</div>

					<client-tags
						class="mt-2"
						v-if="client.tags"
						:tags="client.tags"
                        show-tag-author
					></client-tags>
				</div>

				<div class="mt-2" v-if="client.notes">
					<div
						v-for="v in client.notes"
						:key="v.id"
						class="panel-tag mb-3 fs-xs position-relative"
					>
						<p class="mb-0 mt-2">{{ v.value }}</p>
						<span
							class="fs-xs opacity-70 pt-1 pr-2 position-absolute pos-right pos-top color-success-700"
						>
							{{ v.user_id | managerName }},
							{{ v.created_at | formatDate('L') }}
						</span>
					</div>
				</div>

				<div
					v-if="interface != 'payments'"
					class="panel-content rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 px-0"
				>
					<div v-if="canViewOrderList" class="btn-group btn-group-sm">
						<a
							target="_blank"
							:href="'/orders?filter-client[]=' + clientID"
							class="btn btn-default waves-effect waves-themed"
						>
							Orders
							<span class="badge bg-primary-500 ml-2">{{
								client.orders_count
							}}</span>
						</a>
					</div>
					<div v-else>
						Orders
						<span class="badge bg-primary-500 ml-2">{{
							client.orders_count
						}}</span>
					</div>
				</div>
				<div>
					<!--                    <time-picker use12-hours format="h:mm A" inputReadOnly showNow/>-->
				</div>
			</div>
		</div>

		<client-modal v-if="openModal" :open-onload="true"></client-modal>

		<order-client-change-user-modal
			v-if="interface != 'payments'"
		></order-client-change-user-modal>

		<send-sms :number="this.smsModal.number" :show="this.smsModal.show" />
	</div>
</template>

<script>
import { zadarmaRequest } from '@/api/crm';
import formatDate from '@/filters/formatDate.filter';
import formatPhone from '@/filters/formatPhone.filter';
import managerName from '@/filters/managerName.filter';
import { AxiosHelper } from '@/helpers/axiosHelper';
import ClientTags from '@components/Order/TabOverview/Client/ClientTags';
import sendSms from '@components/Order/TabOverview/ClientModal/SendSms';
import { VClosePopover, VPopover, VTooltip } from 'v-tooltip';
import { mapGetters } from 'vuex';
import OrderClientChangeUserModal from './Client/ChangeUserModal';

const ClientModal = () =>
	import(/* webpackChunkName: "ClientModal" */ '@components/App/ClientModal');

VTooltip.options.defaultClass = 'has-v-tooltip';
VTooltip.options.popover.defaultBaseClass = 'v-tooltip v-popover';
VTooltip.options.popover.defaultWrapperClass = 'v-popover-wrapper';
VTooltip.options.popover.defaultInnerClass =
	'v-tooltip-inner v-popover-inner v-client-inner';
VTooltip.options.popover.defaultArrowClass =
	'v-tooltip-arrow v-popover-arrow v-client-arrow';

export default {
	name: 'OrderClient',
	components: {
		ClientModal,
		ClientTags,
		OrderClientChangeUserModal,
		VPopover,
		sendSms,
	},
	directives: {
		VClosePopover,
	},
	filters: {
		formatDate,
		managerName,
		formatPhone,
	},
	props: ['interface'],
	data() {
		return {
			signatureRequestWait: null,
			smsModal: {
				show: false,
				number: '',
			},
			loading: true,
			openModal: false,
		};
	},
	computed: {
		hasZadarmaApi() {
			if (this.$store.state.order.settings.zadarma.hasApi)
				return this.$store.state.order.settings.zadarma.hasApi;
			return null;
		},
		hasZadarmaExtenstion() {
			if (this.$store.state.order.settings.zadarma.hasExtension)
				return true;
			return null;
		},
		orderHash() {
			return `/customer/order/${this.session.order.hash}`;
		},
		estimateType() {
			if (this.session && this.session?.order?.estimate?.type)
				return this.session?.order?.estimate?.type;
			return '';
		},
		isAdmin() {
			if (this.whoami && this.whoami.is_admin) {
				return true;
			}
			return false;
		},
		firstEmail() {
			if (this.client.emails && this.client.emails.length) {
				return this.client.emails[0].value;
			}
			return '';
		},
		whoami() {
			return this.$store.state.tasksCalendar.whoami;
		},
		canEditOrder() {
			return this.$store.state.order.permissions.canManageOrder;
		},
		canEditClient() {
			return (
				this.clientID &&
				this.$store.state.order.permissions.canManageClients
			);
		},
		canAddClient() {
			return (
				!this.clientID &&
				this.$store.state.order.permissions.canManageOrder
			);
		},
		canViewOrderList() {
			return this.$store.state.order.permissions.canViewOrderList;
		},
		...mapGetters({
			session: 'getSession',
			client: 'clients/record',
			clientID: 'getClientId',
		}),
	},
	async mounted() {
		await this.$store.dispatch('getSession');
		if (this.session.order.client_id)
			await this.$store.dispatch(
				'clients/fetchClient',
				this.session.order.client_id
			);

		this.loading = false;
	},
	methods: {
		signtureRequest() {
			this.signatureRequestWait = true;
			Swal.fire({
				title: 'Are you sure?',
				text:
					'Do you really want to send signature request to <' +
					this.firstEmail +
					'>',
				// icon: 'question',
				showCancelButton: true,
				// reverseButtons: true,
				confirmButtonColor: '#4679cc',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Send signature request',
				cancelButtonText: 'Cancel',
				showLoaderOnConfirm: true,
				allowOutsideClick: () => !Swal.isLoading(),
				preConfirm: () => {
					return AxiosHelper({
						url:
							'/orders/hellosign/signature_request/' +
							this.session.order.id,
					})
						.then((data) => {
							// console.log(data);
							this.signatureRequestWait = null;
							App.Forms.showAlert(
								'success',
								'Signature request successfully sent to customer email: ' +
									data.sentTo
							);
						})
						.catch(() => {
							this.signatureRequestWait = null;
						});
				},
			}).then(() => {
				this.signatureRequestWait = false;
			});
			// return AxiosHelper({
			//     url: '/orders/hellosign/signature_request/' + this.session.order.id
			// }).then((data) => {
			//     // console.log(data);
			//     this.signatureRequestWait = null
			//     App.Forms.showAlert('success', 'Signature request successfully sent to customer email: '+data.sentTo);
			// }).catch(() => {
			//     this.signatureRequestWait = null
			// })
		},
		callPBXv2(phone, provider) {
			return zadarmaRequest('callback', { phone, provider })
				.then((data) => {
					if (data.success === true)
						App.Forms.showAlert(
							'success',
							'Commutation request with ' + phone
						);
					else App.Forms.simpleErrors(data);
				})
				.catch((error) => {
					App.Forms.showAlert(
						'error',
						'Commutation error with ' + phone
					);
				});
		},
		chooseClientModal() {
			$('#client-choose-modal').modal('show');
		},
		clientEditModal() {
			if (!this.openModal) this.openModal = true;
			else $('#client-modal').modal('show');
		},
		clientIcon(v) {
			if (v.name)
				return (
					v.name.substring(0, 1) +
					' ' +
					(v.lname ? v.lname.substring(0, 1) : '')
				).trim();
			return null;
		},
		sendSMSModal(phonenumber) {
			this.smsModal.number = phonenumber;
			$('#send-sms-modal').modal('show');
		},
	},
};
</script>

<style>
.v-client-inner {
	padding: 0px !important;
}

.v-client-arrow {
	border-color: #6c757d !important;
}

.phone-link {
	color: #5a87d2;
}

.phone-link:hover {
	cursor: pointer;
	text-decoration: underline;
}
</style>

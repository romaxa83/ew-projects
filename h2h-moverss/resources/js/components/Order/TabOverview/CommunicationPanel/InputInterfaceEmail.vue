<template>
	<div
		class="panel-content position-relative border-faded bg-faded border-0 mt-0 pt-0"
	>
		<div
			class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
			:class="{ 'd-none': !loading }"
		>
			<div class="d-flex justify-content-center">
				<div
					class="spinner-border text-info position-absolute"
					style="top: 40%"
					role="status"
				>
					<span class="sr-only">Loading...</span>
				</div>
			</div>
		</div>
		<pinned-notes-container
			v-if="
				$store.getters[
					// TODO: Remove prev version after migration
					this.v2
						? 'order/isShowPinnedNotesV2'
						: 'order/isShowPinnedNotes'
				]
			"
		/>

		<div
			class="d-flex align-items-center py-2 px-2 bg-white border border-bottom-0 rounded-top"
		>
			<div>
				<div
					v-if="
						$store.getters[
							// TODO: Remove prev version after migration
							this.v2
								? 'order/pinnedNotesTextV2'
								: 'order/pinnedNotesText'
						]
					"
					class="fa-sm ml-2 pinned-notes-link"
				>
					<a
						href="pinnedNotes"
						@click.prevent="
							$store.commit(
								// TODO: Remove prev version after migration
								this.v2
									? 'order/toggleShowPinnedNotesV2'
									: 'order/toggleShowPinnedNotes'
							)
						"
						>{{
							$store.getters[
								// TODO: Remove prev version after migration
								this.v2
									? 'order/pinnedNotesTextV2'
									: 'order/pinnedNotesText'
							]
						}}</a
					>
				</div>
				<input-interface-dropdown :mode="mode" />
			</div>
			<div class="ml-2 d-flex flex-fill">
				<div class="align-items-center mr-1 py-2 fw-500">to</div>
				<div class="flex-grow-1 mr-2">
					<vue-select2
						v-model="content.to"
						:config="
							configSelect2({ minimumResultsForSearch: Infinity })
						"
					>
						<option v-for="email in emails" :value="email.value">
							{{ email.value }}
						</option>
					</vue-select2>
				</div>
				<div
					class="align-items-center mr-1 py-2 fw-500"
					v-if="gmail_accounts.length"
				>
					via
				</div>
				<div class="flex-fill mr-1" v-if="gmail_accounts.length">
					<vue-select2
						v-model="content.provider"
						:config="
							configSelect2({ minimumResultsForSearch: Infinity })
						"
					>
						<option value="mailjet" v-if="allowed_mailjet">
							Mailjet (default)
						</option>
						<option
							v-for="v in gmail_accounts"
							:key="v.id"
							:value="`gmail_${v.id}`"
							v-text="`Gmail: ${v.email}`"
						/>
						<!--                        <option value="mandrill">Mandrill</option>-->
					</vue-select2>
				</div>

				<!--                <div class="custom-control custom-switch pt-2">-->
				<!--                    <input v-model="form.pinned" type="checkbox" class="custom-control-input" id="note_is_pinned">-->
				<!--                    <label class="custom-control-label" for="note_is_pinned">Pinned</label>-->
				<!--                </div>-->
			</div>
			<!--            <div>-->
			<!--                <button type="button" class="btn btn-md btn-secondary" @click="toggleInterface">UP</button>-->
			<!--            </div>-->
		</div>
		<div
			class="d-flex align-items-center px-2 bg-white border border-bottom-0"
		>
			<div class="ml-2 mr-2 fw-500">Subject:</div>
			<div class="flex-fill mr-2">
				<input
					type="text"
					v-model="content.subject"
					class="form-control border-0 rounded-0 pl-0"
				/>
			</div>
		</div>
		<div
			class="d-flex align-items-center px-2 bg-white border border-bottom-0"
		>
			<div class="ml-2 mr-2 fw-500">Reply to:</div>
			<div class="flex-fill mr-2">
				<input
					type="text"
					v-model="content.reply_to"
					class="form-control border-0 rounded-0 pl-0"
				/>
			</div>
		</div>
		<div
			class="d-flex align-items-center px-2 bg-white border border-bottom-0"
		>
			<div class="ml-2 mr-2 fw-500">Template:</div>
			<div class="flex-fill mr-1">
				<div class="d-flex">
					<div class="flex-fill">
						<vue-select2
							v-if="
								allowed_mailjet && template_engine == 'mailjet'
							"
							@option="catchTemplate"
							v-model="template_id"
							:config="
								configSelect2({
									placeholder: 'Choose mailjet template',
								})
							"
						>
							<option value="31">Empty</option>
							<optgroup
								v-for="email_group in email_templates"
								:key="email_group.id"
								:label="email_group.title"
							>
								<option
									v-for="template in email_group.group_records"
									:value="template.id"
									:key="template.id"
								>
									{{ template.title }}
								</option>
							</optgroup>
						</vue-select2>
						<vue-select2
							v-if="
								allowed_mandrill &&
								template_engine == 'mandrill'
							"
							v-model="template_id"
							:config="
								configSelect2({
									placeholder: 'Choose mandrill template',
								})
							"
						>
							<!--                    <optgroup v-for="email_group in email_templates" :key="email_group.id" :label="email_group.title">-->
							<option :value="null">Empty</option>
							<option
								v-for="template in mandrill_templates"
								:value="template.slug"
								:key="template.slug"
							>
								{{ template.name }}
							</option>
							<!--                    </optgroup>-->
						</vue-select2>
					</div>
					<div class="ml-2 mr-2 align-items-center py-2 fw-500">
						Engine:
					</div>
					<div style="width: 200px">
						<vue-select2
							v-model="template_engine"
							:config="
								configSelect2({
									minimumResultsForSearch: Infinity,
								})
							"
						>
							<option v-if="allowed_mailjet" value="mailjet">
								Mailjet
							</option>
							<option v-if="allowed_mandrill" value="mandrill">
								Mandrill
							</option>
						</vue-select2>
					</div>
					<!--                    <div class="ml-2 mr-2 fw-500">-->
					<!--                        <select class="form-control border-bottom-0 border-top-0 border-right-0 border-left-0">-->
					<!--                            <option>test1</option>-->
					<!--                        </select>-->
					<!--                    </div>-->
				</div>

				<!--                <div v-else style="line-height: 40px;">None</div>-->
			</div>
		</div>

		<!--        <textarea v-model="form.text" rows="8" class="form-control border-->
		<!--                border-bottom-left-radius-0 border-bottom-right-radius-0 border-top-left-radius-0 border-top-right-radius-0"-->
		<!--                  placeholder="Type here..."></textarea>-->
		<ckeditor
			ref="editor"
			class="form-control border rounded-0 w-100 h-100 border-0 p-0"
			v-model="content.html"
			@namespaceloaded="onNamespaceLoaded"
			:config="editorConfig"
		></ckeditor>

		<div
			class="d-flex align-items-center py-2 px-2 bg-white border border-top-0 rounded-bottom"
		>
			<input
				id="attachFiles"
				type="file"
				ref="files"
				v-on:change="attachHandleFileUpload()"
				class="d-none"
			/>
			<button
				v-if="allowedAttachments"
				type="button"
				class="btn btn-icon fs-lg waves-effect waves-themed mr-2"
				@click="attachClickFileUpload"
			>
				<i class="fal fa-paperclip"></i>
			</button>

			<span
				v-for="(v, i) in attachments"
				:key="v.name"
				class="badge border border-primary text-primary mr-2 d-inline-flex"
			>
				<i class="fal fa-download mt-1 mr-1"></i>
				<div
					class="m-1 text-truncate text-truncate-sm"
					v-text="v.name"
					:title="v.name"
				></div>
				<button
					class="btn btn-danger btn-xs btn-icon waves-effect waves-themed"
					@click="attachRemove(i)"
				>
					<i class="fal fa-times"></i>
				</button>
			</span>
			<button
				class="btn btn-primary btn-sm ml-auto waves-effect waves-themed"
				@click="sendEmailTemplate()"
			>
				Send Email
			</button>
		</div>
	</div>
</template>

<script>
import { axiosPromise } from '@/helpers/axiosPromise';
import { ckEditorConfig } from '@/helpers/ckEditorConfig';
import pinnedNotesContainer from '@components/Order/TabOverview/CommunicationPanel/History/PinnedNotesContainer';
import inputInterfaceDropdown from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceDropdown';
import VueSelect2 from '@components/VueSelect2';
import CKEditor from 'ckeditor4-vue'; // import ClassicEditor from 'ckeditor4-vue/dist/ckeditor';
import { mapGetters } from 'vuex';

export default {
	name: 'InputInterfaceEmail',
	props: ['mode', 'emails', 'v2'],
	data() {
		return {
			// editor: ClassicEditor,
			allowed_mailjet: null,
			allowed_mandrill: null,
			template_engine: null,
			currentTemplates: null,
			loading: true,
			template_id: 31, // empty template
			template_title: 'default', // empty template
			email_templates: [],
			mandrill_templates: null,
			// configSelect2: {
			//     containerCssClass: 'border-0',
			//     dropdownCssClass: 'border-0',
			//     selectionCssClass: 'border-0',
			//     minimumResultsForSearch: Infinity
			// },
			form: {
				text: '',
				email: null,
			},
			//
			mail_provider: 'mailjet',
			content: {
				to: null,
				cc: null,
				subject: null,
				reply_to: null,
				html: null,
				provider: 'mailjet',
			},
			attachments: [],
			sending: false,
			is_gmail_loaded: false,
			editorConfig: {
				...ckEditorConfig(),
				height: '400px',
			},
		};
	},
	computed: {
		...mapGetters({
			session: 'getSession',
			gmail_accounts: 'mailbox/getAccounts',
		}),
		allowedAttachments() {
			return this.mail_provider.includes('gmail_');
		},
		mailProvider() {
			return this.content.provider;
		},
		orderID() {
			return this.$store.state.session?.order?.id;
		},
		// emails() {
		//     return this.client?.emails;
		// },
	},
	async mounted() {
		this.updContactTo();
		// пока хардкод
		if (+this.session?.order?.division_id == 2) {
			this.allowed_mailjet = true;
			this.mail_provider = 'mailjet';
			this.content.provider = 'mailjet';
			// this.currentTemplates = 'mailjet';
			this.template_engine = 'mailjet';
			Promise.all([
				this.loadMailjetTemplates(),
				this.loadGmailAccounts(),
				this.loadTemplate(31),
			]).then(() => (this.loading = false));
		} else if (+this.session?.order?.division_id == 1) {
			this.allowed_mandrill = true;
			this.mail_provider = 'mandrill';
			this.content.provider = 'mandrill';
			this.template_id = null;

			// await this.loadMandrillTemplates();
			Promise.all([
				this.loadGmailAccounts(),
				this.loadMandrillTemplates(),
			]).then(() => (this.loading = false));
			this.template_engine = 'mandrill';
		} else {
			Promise.all([this.loadGmailAccounts()]).then(
				() => (this.loading = false)
			);
		}
		// this.form.email = this.emails[0].value;
	},
	methods: {
		updContactTo() {
			const email =
				this.emails.find((e) => e.is_primary === 1) || this.emails[0];
			if (email && email.value !== this.content.to) {
				// if (this.content.to !== null) {
				// 	window.Swal.fire(
				// 		"Recipient's email was changed!",
				// 		"The recipient's email address has been updated to match the client's primary email address",
				// 		'warning'
				// 	);
				// }
				this.content.to = email.value;
			}
		},
		attachClickFileUpload() {
			$('#attachFiles').trigger('click');
		},
		attachRemove(index) {
			this.$delete(this.attachments, index);
		},
		attachHandleFileUpload() {
			let file = this.$refs.files.files[0];
			if (!file) {
				App.Forms.showAlert('error', 'The file is not selected');
				return;
			}

			const total = this.attachments.reduce(
				(acc, item) => acc + item.size,
				0
			);

			if (file.size > 8 * 1024 * 1024) {
				// 8 mb
				App.Forms.showAlert('error', 'Max file size - 8 Mb');
				return;
			}

			// total max - 25 Mb
			if (total + file.size > 25 * 1024 * 1024) {
				App.Forms.showAlert('error', 'Total Attachments is - 25 Mb');
				return;
			}

			this.attachments.push(file);
		},
		changeHeight() {
			this.$emit('sent');
			// console.log('instance', this.$refs.editor.instance)
			// this.content.html = null;
			// this.$refs.editor.instance.resize('100%', 200);
			// console.log('element Height', this.$refs.editor.instance.container.$.offsetHeight);
			// console.log('data', CKEditor.instance.getData())
		},
		// loadFirstMandrillTemplate() {
		//     return new Promise((resolve, reject) => {
		//         if (this.mailProvider == 'mandrill' && this.mandrill_templates && this.mandrill_templates.length > 0) {
		//             this.loadTemplate(this.mandrill_templates[0].slug).then(() => {
		//                 resolve();
		//             })
		//         } else {
		//             resolve();
		//         }
		//     });
		// },
		loadMailjetTemplates() {
			return axiosPromise(
				axios.get('/settings/email-templates/mailjet/list')
			)
				.then((data) => {
					this.email_templates = data.records;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				});
		},
		loadMandrillTemplates() {
			this.loading = true;
			return axiosPromise(axios.get('/mailchimp/mandrillTemplates'))
				.then((data) => {
					this.mandrill_templates = data.records;
					this.loading = false;
				})
				.catch((error) => {
					this.loading = false;
					App.Forms.simpleErrors(error);
				});
		},
		onNamespaceLoaded(CKEDITOR) {},
		catchTemplate(option) {
			if (option) this.template_title = option[0].text;
			else this.template_title = '';
		},
		sendEmailTemplate() {
			this.loading = true;
			let obj = {
				tpl_id: this.template_id,
				order_id: this.orderID,
				template_title: this.template_title,
				'attachments[]': this.attachments,
				...this.content,
				returnFormat: 'communicationPanel',
			};

			let url = '/mailbox/send'; // gmail
			if (this.content.provider === 'mailjet') {
				url = '/settings/email-templates/sender'; // mailjet
			} else if (this.content.provider === 'mandrill') {
				url = '/mailchimp/sendMandrill'; // mandrill
			} else if (this.content.provider.includes('gmail_')) {
				obj.account_id = +this.content.provider.substring(6);
				obj.tpl_id = null;
			}

			axiosPromise(
				axios.post(url, obj, {
					headers: {
						'Content-Type': 'multipart/form-data',
					},
				})
			)
				.then((data) => {
					if (data.activityRecord)
						this.$store.commit(
							// TODO: Remove prev version after migration
							this.v2
								? 'order/pushCommunicationRecordV2'
								: 'order/pushCommunicationRecord',
							data.activityRecord
						);

					App.Forms.showAlert('success', 'Email sent');
					this.content.html = null;
					this.$refs.editor.instance.resize('100%', 200);
					this.$emit('sent');
				})
				.catch((error) => {
					if (error.response)
						App.Forms.simpleErrors(error.response.data);
					else App.Forms.simpleErrors(error);
				})
				.finally(() => (this.loading = false));
		},

		toggleInterface() {
			if (this.mode.panelInterfaceClass == 'panel-interface-default')
				this.mode.panelInterfaceClass = 'panel-interface-input-up';
			else if (
				this.mode.panelInterfaceClass == 'panel-interface-input-up'
			)
				this.mode.panelInterfaceClass = 'panel-interface-default';
		},
		configSelect2(add) {
			let config = {
				containerCssClass: 'border-0',
				dropdownCssClass: 'border-0',
				selectionCssClass: 'border-0',
				minimumResultsForSearch: 0,
			};
			if (typeof add === 'object' && add !== null)
				config = { ...config, ...add };
			return config;
		},
		emptyRecord() {
			return {
				to: null,
				cc: null,
				subject: null,
				reply_to: null,
				html: null,
				provider: this.mail_provider,
			};
		},
		loadTemplate(tpl_id) {
			this.content.html = 'Loading...';
			if (this.mandrill_templates && this.template_engine == 'mandrill') {
				// change subject
				const found = this.mandrill_templates.find(
					(item) => item.slug === tpl_id
				);
				if (found) {
					this.content.subject = found.subject;
					// this.content.reply_to = found.from_email;
				}
				return axiosPromise(
					axios.post('/mailchimp/renderMandrill', {
						orderID: this.orderID,
						tpl_slug: tpl_id,
					})
				)
					.then((data) => {
						this.content.html = data.template.html;
						if (
							this.$refs.editor?.instance?.container &&
							+this.$refs.editor.instance.container.$
								.offsetHeight < 400
						)
							this.$refs.editor.instance.resize('100%', '400');
					})
					.catch((error) => {
						App.Forms.simpleErrors(error);
					});
			} else if (this.template_engine == 'mailjet') {
				return axiosPromise(
					axios.post('/settings/email-templates/sender', {
						tpl_id,
						order_id: this.orderID,
						is_render: true,
					})
				)
					.then((data) => {
						this.content.subject = data.meta.subject;
						//this.content.to = data.meta.to.email;
						this.content.reply_to = data.meta['reply-to'];
						this.content.html = data.data;
					})
					.catch((error) => {
						App.Forms.simpleErrors(error);
					});
			} else {
				this.content.html = '';
				return new Promise((resolve) => resolve());
			}
		},
		mandrillTemplate(template_id) {
			this.content.html = 'Loading...';
			return axios
				.post('/mailchimp/renderMandrill', {
					orderID: this.orderID,
					tpl_id: template_id,
				})
				.then(({ data }) => {
					if (data.success === true) {
						this.content.html = data.template.html;
					} else {
						App.Forms.simpleErrors(data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				});
		},
		loadGmailAccounts() {
			return axiosPromise(axios.post('/mailbox/accounts'))
				.then((data) => {
					let accounts = data.records
						.filter((item) => item.active)
						.filter((item) => !item.is_archived)
						.filter(
							(item) =>
								item.division_id ===
								this.session.order.division_id
						);
					this.$store.commit('mailbox/setAccounts', accounts);

					// Выбираем отправку через Gmail по дефолту для Chicago
					if (
						this.session?.order?.division_id &&
						this.session.order.division_id == 1 &&
						accounts.length
					) {
						this.mail_provider = 'gmail_' + accounts[0].id;
						this.content.provider = this.mail_provider;
					}
					this.is_gmail_loaded = true;
				})
				.catch((error) => {
					console.log(error);
					App.Forms.simpleErrors(error.response.data);
				});
		},
	},
	watch: {
		template_id(newVal, oldVal) {
			this.loading = true;
			this.loadTemplate(newVal).then(() => (this.loading = false));
		},
		mailProvider(newVal, oldVal) {
			// this.loading = true;
			// if (newVal == 'mandrill' && !this.mandrill_templates && !this.loading)
			//     this.loadMandrillTemplates();
			// this.loadTemplate(newVal).then(() => this.loading = false)
		},
	},
	components: {
		inputInterfaceDropdown,
		VueSelect2,
		ckeditor: CKEditor.component,
		pinnedNotesContainer,
	},
};
</script>

<style>
.grow-email {
	transition: all 0.2s ease-in-out;
	transform: scaleY(1.5);
}
</style>

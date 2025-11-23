<template>
	<div
		id="panel-compose"
		class="panel w-100 position-fixed pos-bottom pos-right mb-0 z-index-cloud mr-lg-4 shadow-3 border-bottom-left-radius-0 border-bottom-right-radius-0 expand-full-height-on-mobile expand-full-width-on-mobile shadow"
		style="max-width: 40rem; height: 35rem; display: none"
	>
		<div class="position-relative h-100 w-100 d-flex flex-column">
			<!-- desktop view -->
			<div
				class="panel-hdr bg-fusion-600 height-4 d-none d-sm-none d-lg-flex"
			>
				<h4 class="flex-1 fs-lg color-white mb-0 pl-3">New Message</h4>
				<div class="panel-toolbar pr-2">
					<a
						href="javascript:void(0);"
						class="btn btn-icon btn-icon-light fs-xl mr-1 waves-effect waves-themed"
						data-action="panel-fullscreen"
						data-toggle="tooltip"
						data-offset="0,10"
						data-original-title="Fullscreen"
						data-placement="bottom"
					>
						<i class="fal fa-expand-alt"></i>
					</a>
					<a
						href="javascript:void(0);"
						class="btn btn-icon btn-icon-light fs-xl waves-effect waves-themed"
						data-action="toggle"
						data-class="d-flex"
						data-target="#panel-compose"
						data-toggle="tooltip"
						data-original-title="Save &amp; Close"
						data-placement="bottom"
					>
						<i class="fal fa-times"></i>
					</a>
				</div>
			</div>
			<!-- end desktop view -->
			<!-- mobile view -->
			<div
				class="d-flex d-lg-none align-items-center px-3 py-3 bg-faded border-faded border-top-0 border-left-0 border-right-0 flex-shrink-0"
			>
				<!-- button for mobile -->
				<!-- end button for mobile -->
				<h3 class="subheader-title">New message</h3>
				<div class="ml-auto">
					<button
						type="button"
						class="btn btn-outline-danger waves-effect waves-themed"
						data-action="toggle"
						data-class="d-flex"
						data-target="#panel-compose"
					>
						Cancel
					</button>
				</div>
			</div>
			<!-- end mobile view -->
			<div
				class="panel-container show rounded-0 flex-1 d-flex flex-column"
			>
				<div class="px-3">
					<select
						v-if="isMultiAccounts"
						v-model="newMail.account_id"
						class="form-control border-top-0 border-left-0 border-right-0 px-0 rounded-0 fs-md mt-2 pr-5"
					>
						<option
							v-for="v in accounts"
							:key="v.id"
							v-bind:value="v.id"
						>
							{{ v.email }}
						</option>
					</select>
					<input v-else type="hidden" v-model="newMail.account_id" />

					<div class="input-group mt-2 fs-md">
						<input
							v-model="newMail.to"
							type="text"
							placeholder="Recipients"
							class="form-control border-top-0 border-left-0 border-right-0 px-0 rounded-0 mt-2 pr-5"
							tabindex="2"
						/>
						<div class="input-group-append">
							<div class="input-group-append">
								<button
									data-action="toggle"
									data-class="d-block"
									data-target="#message-to-cc"
									data-focus="message-to-cc"
									data-toggle="tooltip"
									title="Add Cc recipients"
									data-placement="bottom"
									class="btn btn-outline-default border-top-0 border-left-0 border-right-0 px-0 rounded-0 mt-2 pl-2 pr-2"
									type="button"
								>
									CC
								</button>
							</div>
						</div>
					</div>

					<input
						v-model="newMail.cc"
						id="message-to-cc"
						type="text"
						placeholder="Cc"
						class="form-control border-top-0 border-left-0 border-right-0 px-0 rounded-0 fs-md mt-2 d-none"
						tabindex="3"
					/>
					<input
						v-model="newMail.subject"
						type="text"
						placeholder="Subject"
						class="form-control border-top-0 border-left-0 border-right-0 px-0 rounded-0 fs-md mt-2"
						tabindex="4"
					/>
				</div>
				<div class="flex-1" style="overflow-y: auto">
					<ckeditor
						class="border-0 pt-2"
						v-model="newMail.html"
						:config="editorConfig"
						tabindex="5"
					></ckeditor>
				</div>
				<div
					class="px-3 py-4 d-flex flex-row align-items-center flex-wrap flex-shrink-0"
				>
					<button
						@click="send"
						class="btn btn-info mr-3 waves-effect waves-themed"
					>
						<span
							v-show="loading"
							class="spinner-border spinner-border-sm"
							role="status"
							aria-hidden="true"
						></span>
						{{ loading ? 'Sending...' : 'Send' }}
					</button>
					<!--                    <a href="javascript:void(0);" class="btn btn-icon fs-xl mr-1 waves-effect waves-themed"-->
					<!--                       data-toggle="tooltip" data-original-title="Formatting options" data-placement="top">-->
					<!--                        <i class="fas fa-font color-fusion-300"></i>-->
					<!--                    </a>-->
					<!--                    <a href="javascript:void(0);" class="btn btn-icon fs-xl mr-1 waves-effect waves-themed"-->
					<!--                       data-toggle="tooltip" data-original-title="Attach files" data-placement="top">-->
					<!--                        <i class="fas fa-paperclip color-fusion-300"></i>-->
					<!--                    </a>-->
					<!--                    <a href="javascript:void(0);" class="btn btn-icon fs-xl mr-1 waves-effect waves-themed"-->
					<!--                       data-toggle="tooltip" data-original-title="Insert photo" data-placement="top">-->
					<!--                        <i class="fas fa-camera color-fusion-300"></i>-->
					<!--                    </a>-->
					<!--                    <div class="ml-auto">-->
					<!--                        <a href="javascript:void(0);" class="btn btn-icon fs-xl waves-effect waves-themed"-->
					<!--                           data-toggle="tooltip" data-original-title="Disregard draft" data-placement="top">-->
					<!--                            <i class="fas fa-trash color-fusion-300"></i>-->
					<!--                        </a>-->
					<!--                        <a href="javascript:void(0);" class="btn btn-icon fs-xl width-1 waves-effect waves-themed"-->
					<!--                           data-toggle="tooltip" data-original-title="More options" data-placement="top">-->
					<!--                            <i class="fas fa-ellipsis-v-alt color-fusion-300"></i>-->
					<!--                        </a>-->
					<!--                    </div>-->
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { ckEditorConfig } from '@/helpers/ckEditorConfig';
import CKEditor from 'ckeditor4-vue';
import { mapGetters } from 'vuex';

export default {
	name: 'Compose',
	components: {
		ckeditor: CKEditor.component,
	},
	data() {
		return {
			loading: false,
			account_id: null,
			newMail: this.emptyMail(),
			editorConfig: {
				...ckEditorConfig(),
				height: '260px',
			},
		};
	},
	computed: {
		...mapGetters({
			isMultiAccounts: 'mailbox/isMultiAccounts',
			accounts: 'mailbox/getAccounts',
			isLoaded: 'mailbox/isLoaded',
		}),
	},
	watch: {
		isLoaded() {
			const [first] = Object.keys(this.accounts);
			this.account_id = this.accounts[first].id;
			this.newMail.account_id = this.accounts[first].id;
		},
	},
	methods: {
		emptyMail() {
			return {
				account_id: this.account_id,
				to: null,
				cc: null,
				subject: null,
				html: this.fakeText(),
			};
		},
		fakeText() {
			return `<br>
                        <br>
                        <div style="font-size: 0.8em; color: #ccc">
                            If you receive this message in
                            error, please notify us immediately and delete the original and any copies and
                            attachments.
                        </div>`;
		},
		send() {
			this.loading = true;
			axios
				.post('/mailbox/send', this.newMail)
				.then((resp) => {
					if (resp.data.success === true) {
						this.newMail = this.emptyMail();
						App.Forms.showAlert('success', 'Msg was sent');
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
				})
				.finally(() => (this.loading = false));
		},
	},
};
</script>

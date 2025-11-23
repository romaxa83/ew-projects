<template>
	<div
		class="panel-content position-relative border-faded border-0 mt-0 pt-0 bg-faded"
		style="height: 100%"
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

		<div
			class="d-flex align-items-center py-2 px-2 bg-white border border-bottom-0 rounded-top"
		>
			<div>
				<input-interface-dropdown :mode="mode" />
			</div>
			<div class="ml-2 d-flex flex-fill">
				<div class="align-items-center mr-1 py-2 fw-500">to</div>
				<div class="flex-grow-1 mr-2">
					<vue-select2
						v-model="form.to"
						:config="configSelect2(Infinity)"
						:component-key="select2Key"
					>
						<!--                    <select v-model="form.to" class="form-control">-->
						<option v-for="phone in phones" :value="phone.value">
							{{ phone.value | formatPhone }}
						</option>
						<!--                    </select>-->
					</vue-select2>
				</div>
			</div>
		</div>
		<textarea
			oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'
			v-model="form.text"
			rows="3"
			class="form-control border border-bottom-left-radius-0 border-bottom-right-radius-0 border-top-left-radius-0 border-top-right-radius-0 overflow-hidden"
			placeholder="Type here..."
		></textarea>
		<div class="py-2 px-2 bg-white border border-top-0 rounded-bottom">
			<div class="d-flex align-items-center">
				<input
					v-if="canSandMedia"
					id="attachSmsFiles"
					type="file"
					ref="files"
					v-on:change="attachHandleFileUpload()"
					class="d-none"
					:accept="acceptType"
				/>
				<label
					v-if="canSandMedia"
					for="attachSmsFiles"
					role="button"
					tabindex="0"
					class="btn btn-icon fs-lg waves-effect waves-themed mr-2"
				>
					<i class="fal fa-paperclip"></i>
				</label>

				<div class="">Characters: {{ charsQty }}</div>
				<button
					@click="submit"
					type="button"
					class="btn btn-primary btn-sm ml-auto waves-effect waves-themed"
				>
					Send SMS
				</button>
			</div>
			<div
				v-if="attachments.length"
				class="d-flex align-items-center flex-wrap mt-2 nmb-1"
			>
				<span
					v-for="(v, i) in attachments"
					:key="v.name"
					class="badge border border-primary text-primary mr-1 mb-1 d-inline-flex"
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
			</div>
		</div>
	</div>
</template>

<script>
import { zadarmaRequest } from '@/api/crm';
import formatPhone from '@/filters/formatPhone.filter';
import inputInterfaceDropdown from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceDropdown';
import VueSelect2 from '@components/VueSelect2';

export default {
	name: 'InputInterfaceSms',
	props: {
		mode: Object,
		phones: Array,
		canSandMedia: {
			type: Boolean,
			default: false,
		},
	},
	data: () => ({
		select2Key: 0,
		loading: false,
		form: {
			to: null,
			text: '',
		},
		totalMaxSizeMB: 5,
		textMessageMaxSizeMB: 0.0048,
		acceptedImageTypes: [
			'image/jpeg',
			'image/jpg',
			'image/png',
			'image/gif',
		],
		acceptedNonImageTypes: [
			// 'audio/basic',
			// 'audio/L2',
			// 'audio/mp',
			// 'audio/mpe',
			// 'audio/og',
			// 'audio/vnd.rn-realaudi',
			// 'audio/vnd.wav',
			// 'audio/3gp',
			// 'audio/3gpp',
			// 'audio/ac',
			// 'audio/web',
			// 'audio/amr-n',
			// 'audio/am',
			// 'video/mpeg',
			// 'video/mp4',
			// 'video/quicktime',
			// 'video/webm',
			// 'video/3gpp',
			// 'video/3gpp2',
			// 'video/3gpp-tt',
			// 'video/H261',
			// 'video/H263',
			// 'video/H263-1998',
			// 'video/H263-2000',
			// 'video/H264',
			// 'image/bmp',
			// 'image/tiff',
			// 'text/vcard',
			// 'text/x-vcard',
			// 'text/csv',
			// 'text/rtf',
			// 'text/richtext',
			// 'text/calendar',
			// 'text/directory',
			// 'application/pdf',
			// 'application/vcard',
			// 'application/vnd.apple.pkpass',
			// 'application/msword',
			// 'application/vnd.ms-excel',
			// 'application/vnd.ms-powerpoint',
			// 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			// 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			// 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			// 'audio/aac',
		],
		nonImageMaxSizeMB: 0.6,
		attachments: [],
	}),
	computed: {
		charsQty() {
			if (this.form.text.length) return this.form.text.length;
			return 0;
		},
		acceptType() {
			return this.acceptedImageTypes
				.concat(this.acceptedNonImageTypes)
				.join(',');
		},
	},
	mounted() {
		this.updFormTo();
	},
	filters: {
		formatPhone,
	},
	methods: {
        updFormTo() {
            const phone =
               this.phones.find((e) => e.is_primary === 1) || this.phones[0];
            if (phone && phone.value !== this.form.to) {
                // if (!!this.form.to) {
                //     window.Swal.fire(
                //         "Recipient's phone number was changed!",
                //         "The recipient's phone number has been updated to match the client's primary phone number",
                //         'warning'
                //     );
                // }
                this.form.to = phone.value;
            }
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

			const imageType = this.isImageFileType(file);
			const nonImageType = this.isNonImageFileType(file);

			if (!(imageType || nonImageType)) {
				App.Forms.showAlert('error', `File type is not supported`);
				return;
			}

			const fileSizeMb = bytesToMB(file.size);
			if (nonImageType && fileSizeMb > this.nonImageMaxSizeMB) {
				App.Forms.showAlert(
					'error',
					`Max size for one non-image file is ${this.nonImageMaxSizeMB}Mb`,
					`Your file size is ${fileSizeMb.toFixed(2)}Mb`
				);
				return;
			}

			const newTotalMB = bytesToMB(total + file.size);
			if (newTotalMB > this.totalMaxSizeMB) {
				const overSize = newTotalMB - this.totalMaxSizeMB;
				App.Forms.showAlert(
					'error',
					`Max total size for all attachments is ${this.totalMaxSizeMB}Mb`,
					`Your file exceeds the limit by ${overSize.toFixed(2)}Mb`
				);
				return;
			}

			this.attachments.push(file);

			function bytesToMB(bytes) {
				return bytes / 1024 / 1024;
			}
		},
		isImageFileType(file) {
			return this.acceptedImageTypes.includes(file.type);
		},
		isNonImageFileType(file) {
			return this.acceptedNonImageTypes.includes(file.type);
		},
		configSelect2(minimumResultsForSearch) {
			let config = {
				containerCssClass: 'border-0',
				dropdownCssClass: 'border-0',
				selectionCssClass: 'border-0',
				minimumResultsForSearch,
			};
			return config;
		},
		submit() {
			return Swal.fire({
				title: 'Are you sure?',
				text: 'Send SMS to <' + this.form.to + '>',
				icon: 'question',
				showCancelButton: true,
				// reverseButtons: true,
				confirmButtonColor: '#4679cc',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Send SMS',
				cancelButtonText: 'Cancel',
				showLoaderOnConfirm: true,
				allowOutsideClick: () => !Swal.isLoading(),
				preConfirm: () => {
					this.loading = true;
					const formData = new FormData();
					formData.append('phone', this.form.to);
					formData.append('text', this.form.text);
					this.attachments.forEach((file) => {
						formData.append('attachments[]', file);
					});
					return zadarmaRequest('sms', formData, {
						headers: {
							'Content-Type': 'multipart/form-data',
						},
					})
						.then((data) => {
							// console.log(data);
							if (data.success == true) {
								$('#send-sms-modal').modal('hide');
								// this.$store.commit('order/pushCommunicationRecord', data.sms)
								App.Forms.showAlert(
									'success',
									'SMS successfully sended to ' + this.form.to
								);
								this.form.text = '';
								this.attachments = [];
							} else {
								App.Forms.simpleErrors(data);
							}
							this.loading = false;
						})
						.catch((error) => {
							this.loading = false;
							App.Forms.showAlert(
								'error',
								'SMS error with ' + this.form.to
							);
						});
				},
			});
		},
		attachRemove(index) {
			this.$delete(this.attachments, index);
		},
	},
	watch: {
        // TODO - It look's redundant, maybe it should be removed
		phones() {
			this.form.to = this.phones[0].value;
			this.select2Key += 1;
		},
	},
	components: {
		inputInterfaceDropdown,
		VueSelect2,
	},
};
</script>

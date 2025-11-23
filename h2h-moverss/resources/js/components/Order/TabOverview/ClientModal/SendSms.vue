<template>
	<div
		class="modal fade"
		id="send-sms-modal"
		role="dialog"
		aria-hidden="true"
	>
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Send SMS to "{{ number }}"
						<!--                        <small class="m-0 text-muted">-->
						<!--                            Send SMS to "{{ number }}"-->
						<!--                        </small>-->
					</h4>
					<button
						type="button"
						class="close"
						data-dismiss="modal"
						aria-label="Close"
					>
						<span aria-hidden="true"
							><i class="fal fa-times"></i
						></span>
					</button>
				</div>
				<div class="modal-body p-0">
					<div class="panel-container show">
						<div
							class="panel-content border-faded border-left-0 border-right-0 border-bottom-0 bg-faded"
						>
							<textarea
								v-model="text"
								rows="3"
								class="form-control rounded-top border-bottom-left-radius-0 border-bottom-right-radius-0 border"
								placeholder="Message Text..."
							></textarea>
							<div
								class="d-flex align-items-center py-2 px-2 bg-white border border-top-0 rounded-bottom"
							>
								<div class="">Characters: {{ charsQty }}</div>
								<div class="ml-auto">
									<button
										@click="sendSMS()"
										class="btn btn-primary btn-sm ml-sm-0 waves-effect waves-themed"
										:disabled="loading"
									>
										<span
											v-if="loading"
											class="spinner-border spinner-border-sm"
											role="status"
											aria-hidden="true"
										></span>
										Send SMS
									</button>
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
import { zadarmaRequest } from '@/api/crm';

export default {
	name: 'SendSms',
	props: ['show', 'options', 'number'],
	data: () => ({
		state: 'closed',
		loading: false,
		// charsQty: 0,
		text: '',
	}),
	computed: {
		charsQty() {
			if (this.text.length) return this.text.length;
			return 0;
		},
	},
	methods: {
		sendSMS() {
			this.loading = true;
			return zadarmaRequest('sms', {
				phone: this.number,
				text: this.text,
			})
				.then((data) => {
					// console.log(data);
					if (data.success == true) {
						$('#send-sms-modal').modal('hide');
						// должна прилететь с бродкастом
						// this.$store.commit('order/pushCommunicationRecord', data.sms)
						App.Forms.showAlert(
							'success',
							'SMS sended to ' + this.number
						);
					} else {
						App.Forms.simpleErrors(data);
					}
					this.loading = false;
				})
				.catch((error) => {
					this.loading = false;
					App.Forms.showAlert(
						'error',
						'SMS error with ' + this.number
					);
				});
		},
	},
	watch: {
		// state(newVal, oldVal) {
		//     if (newVal === 'opened')
		//         $(this.$refs.smsModal).modal('show')
		//     else if (newVal === 'closed')
		//         $(this.$refs.smsModal).modal('hide')
		// },
		// show(newVal, oldVal) {
		//     if (newVal === true)
		//         this.state = 'opened';
		//     // $(this.$refs.smsModal).modal('show')
		//     else if (newVal === false)
		//         this.state = 'closed';
		//     // $(this.$refs.smsModal).modal('hide')
		//     // console.log('Prop changed: ', newVal, ' | was: ', oldVal)
		// }
	},
};
</script>

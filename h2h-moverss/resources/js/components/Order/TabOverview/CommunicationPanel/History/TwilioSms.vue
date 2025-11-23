<template>
	<li
		class="d-flex"
		:class="[direction == 'inbound' ? 'inbound-block' : 'outbound-block']"
	>
		<!--        <div>-->
		<!--            <button class="btn btn-lg btn-white btn-icon activity-timeline-icon-block rounded-circle js-waves-off">-->
		<!--                <i title="messages"-->
		<!--                   class="fal fa-2x fa-sms"></i>-->
		<!--            </button>-->
		<!--        </div>-->
		<div
			class="card card-zoom-hover"
			:class="{ 'border border-primary': record.selected }"
		>
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap"
			>
				<div class="mr-1"><i class="fas fa-sms"></i></div>
				<div class="fs-xs pr-3 text-muted">
					<b
						>[Twilio] {{ smsDirection }} Sms {{ smsType }}
						{{ author }}</b
					>
				</div>
				<div
					class="ml-auto d-flex position-relative pr-2 fs-xs text-muted"
				>
					{{ datetime }}
				</div>
			</div>
			<div class="card-body fs-sm py-2">
				<div class="fs-md"><span v-html="item.body"></span></div>
			</div>
			<div
				v-if="attachments.length"
				data-attachments
				class="card-footer py-2"
			>
				<div class="attachments">
					<button
						v-for="(att, k) in attachments"
						:key="att.url"
						class="attachment"
						type="button"
						data-toggle="modal"
						:data-target="`#${attachmentModalId}`"
						@click="setModalAttachmentIndex(k)"
					>
						<img
							width="150"
							height="100"
							loading="lazy"
							draggable="false"
							:src="att.url | attachmentUrl"
							:alt="att.name"
						/>
						<span class="attachment-name fs-nano">{{
							att.name
						}}</span>
					</button>
				</div>
				<portal :to="portals.TwilioAttachments">
					<div
						:id="attachmentModalId"
						class="example-modal-fullscreen fade modal modal-fullscreen"
						tabindex="-1"
						style="display: none"
						aria-hidden="true"
					>
						<div
							class="modal-dialog modal-dialog-centered"
							role="document"
						>
							<div
								class="modal-content border-0 shadow-0 bg-fusion-800"
							>
								<button
									type="button"
									class="close p-sm-2 p-md-4 text-white fs-xxl position-absolute pos-right mr-sm-2 mt-sm-1 z-index-space"
									data-dismiss="modal"
									aria-label="Close"
								>
									<span aria-hidden="true"
										><i class="fal fa-times"></i
									></span>
								</button>
								<div class="modal-body p-0 h-100">
									<div
										class="carousel slide h-100 d-flex align-items-center justify-content-center"
									>
										<!--										<ol class="carousel-indicators">-->
										<!--											<li-->
										<!--												v-for="(att, k) in attachments"-->
										<!--												:key="att.url"-->
										<!--												:data-slide-to="k"-->
										<!--												class=""-->
										<!--											></li>-->
										<!--										</ol>-->
										<div
											class="carousel-inner mt-6 mb-6 pb-2"
											style="width: 70%"
										>
											<div
												class="carousel-item"
												style="display: block"
											>
												<img
													class="modal-image"
													loading="lazy"
													:src="
														modalAttachment.url
															| attachmentUrl
													"
													:alt="modalAttachment.name"
												/>
												<div class="text-center mt-2">
													<p
														class="color-white opacity-70"
													>
														{{
															modalAttachment.name
														}}
													</p>
												</div>
											</div>
										</div>
										<a
											v-if="attachments.length > 1"
											class="carousel-control-prev attachment-nav-button"
											:class="{
												disabled:
													modalAttachmentIsFirst,
											}"
											:aria-disabled="
												modalAttachmentIsFirst
											"
											role="button"
											@click="prevModalAttachment"
										>
											<span
												class="carousel-control-prev-icon"
												aria-hidden="true"
											></span>
											<span class="sr-only"
												>Previous</span
											>
										</a>
										<a
											v-if="attachments.length > 1"
											class="carousel-control-next attachment-nav-button"
											:class="{
												disabled: modalAttachmentIsLast,
											}"
											:aria-disabled="
												modalAttachmentIsLast
											"
											role="button"
											@click="nextModalAttachment"
										>
											<span
												class="carousel-control-next-icon"
												aria-hidden="true"
											></span>
											<span class="sr-only">Next</span>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</portal>
			</div>
			<div
				v-if="media"
				class="card-footer text-muted py-2 d-flex flex-wrap"
			>
				<div class="mr-2" v-for="(url, k) of media">
					<a :href="url" @click.prevent="openMedia(url)"
						><i class="fal fa-paperclip fs-xs mr-1"></i> Media{{
							k
						}}</a
					>
				</div>
			</div>
			<div
				v-if="direction == 'outbound' && deliveryStatus"
				class="card-footer text-muted py-2 d-flex"
			>
				<div class="mr-2 ml-auto">
					<i
						v-if="deliveryStatus.sent"
						class="fas fa-check"
						title="sent"
					></i>
					<i
						v-else
						class="fas fa-hourglass-half"
						title="not sent"
					></i>
				</div>
				<div class="mr-2">
					<i
						v-if="deliveryStatus.delivered"
						class="fas fa-check"
						title="delivered"
					></i>
					<i
						v-else
						class="fas fa-hourglass-half"
						title="not delivered"
					></i>
				</div>
			</div>
		</div>
	</li>
</template>

<script>
import { VuePortalNames } from '@components/VuePortalNames';

export default {
	name: 'TwilioSms',
	props: ['record', 'datetime', 'interface', 'index', 'v2'],
	data: () => ({
		directionIcon: '',
		orderID: null,
		portals: { ...VuePortalNames },
		currentModalAttachmentIndex: 0,
	}),
	methods: {
		openMedia(url) {
			window.open(
				url,
				'_blank',
				'location=yes,height=600,width=600,scrollbars=yes,status=yes'
			);
		},
		setModalAttachmentIndex(index) {
			if (index < 0) {
				this.currentModalAttachmentIndex = 0;
			} else if (index >= this.attachments.length) {
				this.currentModalAttachmentIndex = this.attachments.length - 1;
			} else {
				this.currentModalAttachmentIndex = index;
			}
		},
		prevModalAttachment() {
			this.setModalAttachmentIndex(this.currentModalAttachmentIndex - 1);
		},
		nextModalAttachment() {
			this.setModalAttachmentIndex(this.currentModalAttachmentIndex + 1);
		},
	},
	filters: {
		attachmentUrl(url) {
			return url;
		},
	},
	computed: {
		deliveryStatus() {
			if (this.item?.statuses) {
				const statuses = { sent: false, delivered: false };
				for (const v of this.item.statuses) {
					statuses[v.status] = true;
				}
				// let statuses = this.item.statuses.map((item) => {
				//     return item.status;
				// });
				return statuses;
			}
			return null;
		},
		media() {
			if (
				this.misc &&
				this.misc.NumMedia &&
				Number(this.misc.NumMedia) > 0
			) {
				let media = [];
				for (let i = 0; i < +this.misc.NumMedia; i++) {
					if (this.misc['MediaUrl' + i])
						media.push(this.misc['MediaUrl' + i]);
				}
				return media;
			}
			return null;
		},
		misc() {
			const data = this.record?.item?.misc;
			if (data) {
				if (typeof data === 'object') {
					return data;
				}
				try {
					return JSON.parse(data);
				} catch (e) {
					console.log(e);
					console.log('misc', data);
					return null;
				}
			}
			return null;
		},
		attachments() {
			const attachments = this.record?.attachments;
			return Array.isArray(attachments) ? attachments : [];
		},
		attachmentModalId() {
			const isNew = this.v2 ? 'v2-' : '';
			return `twilio-attachment-modal-${
				isNew + (this.record?.id || this.index)
			}`;
		},
		modalAttachment() {
			return this.attachments[this.currentModalAttachmentIndex];
		},
		modalAttachmentIsFirst() {
			return this.currentModalAttachmentIndex === 0;
		},
		modalAttachmentIsLast() {
			return (
				this.currentModalAttachmentIndex === this.attachments.length - 1
			);
		},
		item() {
			return this.record.item;
		},
		author() {
			if (this.record?.audit) {
				return ' by ' + this.record.audit.user_name;
			}
			return '';
		},
		direction() {
			if (this.item.direction == 'inbound') return 'inbound';
			else return 'outbound';
		},
		smsDirection() {
			if (this.item.direction == 'inbound') {
				return 'Inbound';
			} else return 'Outbound';
			return '';
		},
		smsType() {
			let text = '';
			if (this.item.direction == 'inbound') {
				text = ' from ' + this.item.from;
			} else {
				text = ' to ' + this.item.to;
			}
			// if (this.item.event == 'NOTIFY_OUT_END') {
			//     text += '<i class="fas fa-long-arrow-left"></i> Outbound call ' + this.callDirection;
			// } else if (this.item.event == 'NOTIFY_END') {
			//     text += '<i class="fas fa-long-arrow-right"></i> Inbound call ' + this.callDirection;
			// }
			return text;
		},
	},
};
</script>

<style scoped lang="scss">
.attachments {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	justify-content: flex-start;
}

.outbound-block .attachments {
	justify-content: flex-end;
}

.attachment {
	width: 162px;
	display: flex;
	flex-direction: column;
	gap: 8px;
	border-width: 0;
	background: #fff;
	padding: 5px 5px 2px;
	margin: 0;
	border-radius: 5px;
	--text: #868e96;
	--border: rgba(0, 0, 0, 0.125);
}

.attachment:hover,
.attachment:focus-visible {
	--text: #111;
	--border: rgba(0, 0, 0, 0.5);
}

.attachment:focus-visible {
	outline: 2px solid #007bff;
}

.attachment:active {
	box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
	outline: none;
}

.attachment img {
	aspect-ratio: 15 / 10;
	object-fit: contain;
	border: 1px solid var(--border);
}

.attachment-name {
	display: block;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	max-width: 100%;
	color: var(--text);
}

.attachment-nav-button.disabled {
	visibility: hidden;
}

.modal-image {
	height: auto;
	max-height: 100%;
	display: block;
	margin: 0 auto;
}
</style>

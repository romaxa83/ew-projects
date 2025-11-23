<template>
	<li
		class="d-flex"
		:class="[direction == 'inbound' ? 'inbound-block' : 'outbound-block']"
	>
		<!--        <div>-->
		<!--            <button class="btn btn-lg btn-white btn-icon activity-timeline-ico-block rounded-circle js-waves-off">-->
		<!--                <i title="messages"-->
		<!--                   class="fal fa-2x fa-envelope"></i>-->
		<!--            </button>-->
		<!--        </div>-->
		<div
			class="card card-zoom-hover"
			:class="{ 'border border-primary': record.selected }"
		>
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap"
			>
				<!--                <h4 v-if="interface == 'flow'" class="my-0 mr-1 cursor-pointer">-->
				<!--                    <span v-if="orderID" class="badge badge-secondary">-->
				<!--                        <a class="text-white" style="text-decoration: none" :href="'/orders/'+orderID" target="_blank">Order #{{ orderID }} <i-->
				<!--                            class="fas fa-external-link"></i></a></span>-->
				<!--                    <span v-else class="badge badge-warning" @click="$emit('assign', index)">Assign</span>-->
				<!--                </h4>-->
				<div class="mr-1"><i class="fas fa-envelope"></i></div>
				<div class="fs-xs pr-3 text-muted">
					<b>Email at mailbox <{{ mailbox }}> {{ sentBy }}</b>
				</div>
				<div
					class="ml-auto d-flex position-relative pr-2 fs-xs text-muted"
				>
					{{ datetime }}
				</div>
			</div>
			<div class="card-body fs-sm py-2">
				<div class="fs-md">
					<span v-html="directionIcon"></span>{{ directionType }}
				</div>
				<a href="preview" v-b-modal="modalId" @click.prevent
					><u>View email: {{ item.subject }}</u></a
				>
				<!--                <del>Description about task. Tra la-la-la-la</del>-->
			</div>
		</div>
		<b-modal
			:id="modalId"
			hide-backdrop
			size="lg"
			scrollable
			:title="directionType"
			title-tag="div"
			ok-title="Close"
			:ok-only="true"
			modal-class="email-modal"
			@show="loadContent"
			@shown="renderIframeContent"
		>
			<template #modal-title>
				<div>
					<h5>{{ directionType }}</h5>
					<p>Subject: {{ item.subject }}</p>
					<p>Mail: {{ isLoaded && !emailContent ? 'No content' : '' }}</p>
				</div>
			</template>
			<div v-if="!isLoaded" class="d-flex justify-content-center">
				<div class="spinner-border" role="status">
					<span class="sr-only">Loading...</span>
				</div>
			</div>
			<iframe
				v-show="isFullHtml"
				ref="mailFrame"
				:style="{
					width: '100%',
					height: iframeHeight + 'px',
					border: 'none',
				}"
			/>
			<div v-if="!isFullHtml" v-html="emailContent" />
		</b-modal>
	</li>
</template>

<script>
import { BModal } from 'bootstrap-vue';
import { VBModal } from 'bootstrap-vue';
import { axiosPromise } from '@/helpers/axiosPromise';

export default {
	name: 'Email',
	props: ['record', 'datetime', 'interface', 'index'],
	data: () => ({
        isLoaded: false,
        emailContent: '',
		iframeHeight: 400,
	}),
	computed: {
		direction() {
			if (this.item.tag == 'sent') {
				return 'outbound';
			} else if (this.item.tag == 'inbox') return 'inbound';
			return null;
		},
		mailbox() {
			if (this.item.account?.miscs?.email)
				return this.item.account?.miscs?.email;
			return '';
		},
		modalId() {
			return 'modal-preview-' + this.item.msg_id;
		},
		orderID() {
			if (this.record.orderID) return this.record.orderID;
			return null;
		},
		item() {
			return this.record.item;
		},
		subject() {
			if (this.item.subject.length < 2) return '(Empty subject)';
			return this.item.subject;
		},
		directionIcon() {
			if (this.item.tag == 'inbox') {
				return '<i class="fas fa-long-arrow-right pr-1"></i> ';
			} else if (this.item.tag == 'sent') {
				return '<i class="fas fa-long-arrow-left pr-1"></i> ';
			}
			return '';
		},
		sentTo() {
			if (
				this.item.tag == 'sent' &&
				this.item.miscs?.to &&
				this.item.miscs.to.length
			) {
				return this.item.miscs.to
					.map((v) => v.name + ' <' + v.email + '>')
					.join(', ');
			}
			return '';
		},
		directionType() {
			if (this.item.tag == 'inbox') {
				return (
					'Inbound email from: ' +
					this.item.miscs?.from?.name +
					' <' +
					this.item.miscs?.from?.email +
					'>'
				);
			} else if (this.item.tag == 'sent') {
				return 'Outbound email to: ' + this.sentTo;
			} else {
				return '[' + this.item.tag + ']';
			}
		},
		sentBy() {
			if (this.item.tag == 'sent') {
				if (this.record?.audit && this.record.audit?.user_id) {
					return '. Sent by ' + this.record.audit?.user_name;
				} else {
					return '. Sent by external email client';
				}
			}
			return '';
		},
		isFullHtml() {
			const html = this.emailContent.trim().toLowerCase();
			return /^<!doctype html>/.test(html) || /<html[\s>]/.test(html);
		},
		// author() {
		//     if (this.item.author.employee)
		//         return this.item.author.employee.name + ' ' + this.item.author.employee.l_name;
		//     return this.item.author.name;
		// }
	},
	methods: {
		renderIframeContent() {
			if (!this.isFullHtml) return;

			this.$nextTick(() => {
				const iframe = this.$refs.mailFrame;
				if (!iframe) return;

				const doc =
					iframe.contentDocument || iframe.contentWindow.document;
				doc.open();
				doc.write(this.emailContent);
				doc.close();
			});
		},
		loadContent() {
			if (this.isLoaded) return;

			return axiosPromise(
				axios.post(`/communications.v2/email/${this.item.id}`)
			)
				.then((response) => {
					this.emailContent = response.data?.data?.text || '';
                    this.isLoaded = true;
				})
				.then(() => this.renderIframeContent())
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});
		},
	},
	directives: {
		'b-modal': VBModal,
	},
	components: {
		BModal,
	},
};
</script>

<style lang="scss">
.email-modal {
	.modal-header {
		padding-bottom: 0;
	}

	.modal-body {
		padding-top: 0;
	}

	p {
		margin: 0 0 0.5rem;
	}
}
</style>

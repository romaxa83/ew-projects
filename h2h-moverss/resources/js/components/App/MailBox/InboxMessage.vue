<template>
	<div
		class="d-flex flex-column border-faded border-top-0 border-left-0 border-right-0 py-3 px-3 px-sm-4 px-lg-0 mr-0 mr-lg-5 flex-shrink-0"
	>
		<div class="d-flex align-items-center flex-row">
			<div class="ml-0 mr-3 mx-lg-3">
				<img
					:src="`https://www.gravatar.com/avatar/${msgFrom.md5}?d=identicon`"
					class="profile-image profile-image-md rounded-circle"
					:alt="msgFrom.email"
				/>
			</div>
			<div class="fw-500 flex-1 d-flex flex-column">
				<div class="fs-lg">
					{{ msgFrom.name }}
					<span
						class="fs-nano fw-400 ml-2"
						v-text="msgFrom.email"
					></span>
				</div>
				<div class="fs-nano">to {{ msgTo.email }}</div>
			</div>
			<div class="color-fusion-200 fs-sm">
				{{ message.created_at | formatDate }}
				<!--                                    <span class="hidden-sm-down">(12 hours ago)</span>-->
			</div>
			<div class="collapsed-reveal">
				<a
					href="javascript:void(0);"
					class="btn btn-icon ml-1 fs-lg rounded-circle"
				>
					<i class="fal fa-reply"></i>
				</a>
			</div>
		</div>
		<div>
			<div
				class="pl-lg-5 ml-lg-5 pt-3 pb-4"
				v-html="message.data.text"
			></div>
		</div>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';

export default {
	name: 'InboxMessage',
	filters: {
		formatDate,
	},
	props: {
		accounts: {
			type: Object,
			required: true,
		},
		message: {
			type: Object,
			required: true,
		},
	},
	computed: {
		account() {
			return this.accounts[this.message.account_id];
		},
		msgFrom() {
			if (this.message.miscs.from) {
				return this.message.miscs.from;
			} else {
				return {
					name: 'You',
					email: this.account.email,
					md5: this.account.md5,
				};
			}
		},
		msgId() {
			return this.message.id;
		},
		msgTo() {
			if (this.message.miscs.to) {
				return this.message.miscs.to;
			} else {
				return {
					name: 'You',
					email: this.account.email,
					md5: this.account.md5,
				};
			}
		},
	},
};
</script>

<template>
	<div
		id="js-inbox-menu"
		class="flex-wrap position-relative bg-white slide-on-mobile slide-on-mobile-left"
	>
		<div class="position-absolute pos-top pos-bottom w-100">
			<div class="d-flex h-100 flex-column">
				<div class="px-3 px-sm-4 px-lg-5 py-4 align-items-center">
					<div
						class="btn-group btn-block"
						role="group"
						aria-label="Button group with nested dropdown "
					>
						<button
							type="button"
							class="btn btn-danger btn-block fs-md waves-effect waves-themed"
							data-action="toggle"
							data-class="d-flex"
							data-target="#panel-compose"
							data-focus="message-to"
						>
							Compose
						</button>
						<div class="btn-group" role="group">
							<button
								id="btnGroupDrop1"
								type="button"
								class="btn btn-danger dropdown-toggle px-2 js-waves-off"
								data-toggle="dropdown"
								aria-haspopup="true"
								aria-expanded="false"
							></button>
							<div
								class="dropdown-menu p-0"
								aria-labelledby="btnGroupDrop1"
							>
								<a class="dropdown-item" href="#"
									>Work group A</a
								>
								<a class="dropdown-item" href="#">New page</a>
								<a class="dropdown-item" href="#"
									>Edit signature</a
								>
								<div class="dropdown-divider m-0"></div>
								<a class="dropdown-item" href="#">
									<i class="fas fa-plus width-1"></i> Add
									more</a
								>
							</div>
						</div>
					</div>
				</div>
				<div class="flex-1 pr-3">
					<button
						@click="changeFolder('inbox')"
						:class="{
							active: activeFolder === 'inbox',
							'font-weight-bold': activeFolder === 'inbox',
						}"
						class="dropdown-item px-3 px-sm-4 pr-lg-3 pl-lg-5 py-2 fs-md d-flex justify-content-between rounded-pill border-top-left-radius-0 border-bottom-left-radius-0"
					>
						<div>
							<i class="fas fa-folder-open width-1"></i>Inbox
						</div>
						<div
							class="fw-400 fs-xs js-unread-emails-count"
							v-if="meta.inbox.new"
						>
							({{ meta.inbox.new }})
						</div>
					</button>
					<!--                    <button @click="changeFolder('starred')" :class="{ active: activeFolder === 'starred', 'font-weight-bold': activeFolder === 'starred'}"-->
					<!--                       class="dropdown-item px-3 px-sm-4 pr-lg-3 pl-lg-5 py-2 fs-md d-flex justify-content-between rounded-pill border-top-left-radius-0 border-bottom-left-radius-0">-->
					<!--                        <div>-->
					<!--                            <i class="fas fa-star width-1"></i>Starred-->
					<!--                        </div>-->
					<!--                        <div class="fw-400 fs-xs">(6)</div>-->
					<!--                    </button>-->
					<!--                    <button @click="changeFolder('draft')"-->
					<!--                            :class="{ active: activeFolder === 'draft', 'font-weight-bold': activeFolder === 'draft'}"-->
					<!--                            class="dropdown-item px-3 px-sm-4 pr-lg-3 pl-lg-5 py-2 fs-md d-flex justify-content-between rounded-pill border-top-left-radius-0 border-bottom-left-radius-0">-->
					<!--                        <div>-->
					<!--                            <i class="fas fa-edit width-1"></i>Draft-->
					<!--                        </div>-->
					<!--                        <div class="fw-400 fs-xs">({{ meta.draft.total }})</div>-->
					<!--                    </button>-->
					<button
						@click="changeFolder('sent')"
						:class="{
							active: activeFolder === 'sent',
							'font-weight-bold': activeFolder === 'sent',
						}"
						class="dropdown-item px-3 px-sm-4 pr-lg-3 pl-lg-5 py-2 fs-md d-flex justify-content-between rounded-pill border-top-left-radius-0 border-bottom-left-radius-0"
					>
						<div>
							<i class="fas fa-paper-plane width-1"></i>Sent
						</div>
					</button>
					<button
						@click="changeFolder('spam')"
						:class="{
							active: activeFolder === 'spam',
							'font-weight-bold': activeFolder === 'spam',
						}"
						class="dropdown-item px-3 px-sm-4 pr-lg-3 pl-lg-5 py-2 fs-md d-flex justify-content-between rounded-pill border-top-left-radius-0 border-bottom-left-radius-0"
					>
						<div>
							<i class="fas fa-exclamation-triangle width-1"></i
							>Spam
						</div>
					</button>
					<button
						@click="changeFolder('trash')"
						:class="{
							active: activeFolder === 'trash',
							'font-weight-bold': activeFolder === 'trash',
						}"
						class="dropdown-item px-3 px-sm-4 pr-lg-3 pl-lg-5 py-2 fs-md font-weight-bold d-flex justify-content-between rounded-pill border-top-left-radius-0 border-bottom-left-radius-0"
					>
						<div><i class="fas fa-trash width-1"></i>Trash</div>
					</button>
				</div>
				<div class="px-5 py-3 fs-nano fw-500">
					Accounts: {{ totalAccounts }}<br />
					<div v-show="lastSync" class="mb-2">
						Last sync: {{ fromNow }}
					</div>

					<button
						@click="changeFolder('settings')"
						:class="{
							active: activeFolder === 'settings',
							'font-weight-bold': activeFolder === 'settings',
						}"
						class="dropdown-item px-3 px-sm-4 pr-lg-3 pl-lg-5 py-2 fs-md d-flex justify-content-between rounded-pill border-top-left-radius-0 border-bottom-left-radius-0"
					>
						<div><i class="fas fa-cog width-1"></i>Manage</div>
					</button>
					<!--                    Test 1.5 GB (10%) of 15 GB used-->
					<!--                    <div class="progress mt-1" style="height: 7px;">-->
					<!--                        <div class="progress-bar" role="progressbar" style="width: 11%;" aria-valuenow="11"-->
					<!--                             aria-valuemin="0" aria-valuemax="100"></div>-->
					<!--                    </div>-->
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
	name: 'Sidebar',
	props: {
		activeFolder: {
			type: String,
			required: true,
		},
	},
	computed: {
		fromNow() {
			return moment
				.utc(this.lastSync, 'YYYY-MM-DD HH:mm:ss')
				.local()
				.fromNow();
		},
		totalAccounts() {
			return this.accounts ? Object.keys(this.accounts).length : 'n/a';
		},
		...mapGetters({
			meta: 'mailbox/meta',
			lastSync: 'mailbox/getLastSync',
			isLoaded: 'mailbox/isLoaded',
			accounts: 'mailbox/getAccounts',
		}),
	},
	methods: {
		changeFolder(value) {
			this.$emit('changeFolder', value);
		},
	},
};
</script>

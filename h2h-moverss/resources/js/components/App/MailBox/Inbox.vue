<template>
	<div class="d-flex flex-column flex-grow-1 bg-white">
		<!-- inbox header -->
		<div class="flex-grow-0">
			<!-- inbox button shortcut -->
			<div
				class="d-flex flex-wrap align-items-center pl-2 pr-3 py-2 px-sm-4 pr-lg-5 pl-lg-0 border-faded border-top-0 border-left-0 border-right-0"
			>
				<div class="flex-1 d-flex align-items-center">
					<button
						@click="back"
						class="btn btn-icon rounded-circle mr-2 mr-lg-3"
					>
						<i class="fas fa-arrow-left fs-lg"></i>
					</button>
					<button
						@click="sync"
						class="btn btn-icon rounded-circle mr-1 waves-effect waves-themed"
					>
						<i class="fas fa-redo fs-md"></i>
					</button>
					<!--                    <a href="page_inbox_general.html" class="btn btn-icon rounded-circle mr-1">-->
					<!--                        <i class="fas fa-exclamation-circle fs-md"></i>-->
					<!--                    </a>-->
					<a
						href="page_inbox_general.html"
						id="js-delete-selected"
						class="btn btn-icon rounded-circle mr-1"
					>
						<i class="fas fa-trash fs-md"></i>
					</a>
				</div>
				<div class="text-muted mr-1 mr-lg-3 ml-auto">
					{{ paginateRange.range }}
					<span class="hidden-lg-down">
						of {{ paginateRange.total }}</span
					>
					<div class="btn-group hidden-lg-up" role="group">
						<button type="button" class="btn btn-default">
							Reply
						</button>
						<div class="btn-group" role="group">
							<button
								id="dropdown-reply"
								type="button"
								class="btn btn-default dropdown-toggle px-2 js-waves-off"
								data-toggle="dropdown"
								aria-haspopup="true"
								aria-expanded="false"
							></button>
							<div
								class="dropdown-menu p-0"
								aria-labelledby="dropdown-reply"
							>
								<a class="dropdown-item" href="#"
									>Reply to all</a
								>
								<a class="dropdown-item" href="#">Forward</a>
								<div class="dropdown-divider m-0"></div>
								<a class="dropdown-item" href="#">
									Move to...</a
								>
							</div>
						</div>
					</div>
				</div>
				<div class="d-flex flex-wrap hidden-lg-down">
					<button class="btn btn-icon rounded-circle">
						<i class="fal fa-chevron-left fs-md"></i>
					</button>
					<button class="btn btn-icon rounded-circle">
						<i class="fal fa-chevron-right fs-md"></i>
					</button>
				</div>
			</div>
			<!-- end inbox button shortcut -->
		</div>
		<!-- end inbox header -->
		<!-- inbox message -->
		<div
			class="flex-wrap align-items-center flex-grow-1 position-relative bg-white"
		>
			<vuescroll
				:ops="vScroll"
				class="position-absolute pos-top pos-bottom w-100"
			>
				<div class="d-flex h-100 flex-column">
					<!-- inbox title -->
					<div
						class="d-flex align-items-center pl-2 pr-3 py-3 pl-sm-3 pr-sm-4 py-sm-4 px-lg-5 py-lg-3 flex-shrink-0"
					>
						<!-- button for mobile -->
						<a
							href="javascript:void(0);"
							class="pl-3 pr-3 py-2 d-flex d-lg-none align-items-center justify-content-center mr-2 btn"
							data-action="toggle"
							data-class="slide-on-mobile-left-show"
							data-target="#js-inbox-menu"
						>
							<i class="fal fa-ellipsis-v h1 mb-0"></i>
						</a>
						<!-- end button for mobile -->
						<h1 class="subheader-title mb-0 ml-2 ml-lg-5">
							<span
								v-if="loading"
								v-text="'Loading Thread ' + openedThreadId"
							></span>
							<span v-else v-text="record.subject"></span>
						</h1>
						<span
							class="badge badge-primary ml-2 hidden-sm-down"
							v-text="activeFolder"
						></span>
						<div class="d-flex position-relative ml-auto">
							<a
								href="#"
								title="starred"
								class="btn btn-icon ml-1 fs-lg"
							>
								<i class="fas fa-star color-warning-500"></i>
							</a>
						</div>
					</div>
					<!-- end inbox title -->

					<div v-if="loading" class="d-flex justify-content-center">
						<div class="spinner-border" role="status">
							<span class="sr-only">Loading...</span>
						</div>
					</div>
					<div v-else>
						<inbox-message
							v-for="message in record.messages"
							:key="message.id"
							:message="message"
							:accounts="accounts"
						></inbox-message>
					</div>
				</div>
			</vuescroll>
		</div>
		<!-- end inbox message -->
	</div>
</template>

<script>
import vuescroll from 'vuescroll';
import { mapGetters } from 'vuex';
import InboxMessage from './InboxMessage';

export default {
	name: 'Inbox',
	components: {
		InboxMessage,
		vuescroll,
	},
	props: {
		activeFolder: {
			type: String,
			required: true,
		},
		loading: {
			type: Boolean,
			required: true,
		},
		openedThreadId: {
			type: String,
			required: true,
		},
		paginateRange: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			vScroll: {
				bar: {
					background: '#000',
				},
			},
		};
	},
	computed: {
		record() {
			return this.opened[this.openedThreadId];
		},
		...mapGetters({
			accounts: 'mailbox/getAccounts',
			opened: 'mailbox/getOpened',
		}),
	},
	methods: {
		back() {
			this.$emit('openThread');
		},
		sync() {
			this.$emit('sync', {
				mode: 'refresh',
			});
		},
	},
};
</script>

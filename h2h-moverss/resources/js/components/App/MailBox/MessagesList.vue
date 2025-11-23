<template>
	<div class="d-flex flex-column flex-grow-1 bg-white">
		<!-- inbox header -->
		<div class="flex-grow-0">
			<!-- inbox title -->
			<div
				class="d-flex align-items-center pl-2 pr-3 py-3 pl-sm-3 pr-sm-4 py-sm-4 px-lg-5 py-lg-4 border-faded border-top-0 border-left-0 border-right-0 flex-shrink-0"
			>
				<!-- button for mobile -->
				<a
					href="javascript:void(0);"
					class="pl-3 pr-3 py-2 d-flex d-lg-none align-items-center justify-content-center mr-2 btn waves-effect waves-themed"
					data-action="toggle"
					data-class="slide-on-mobile-left-show"
					data-target="#js-inbox-menu"
				>
					<i class="fal fa-ellipsis-v h1 mb-0"></i>
				</a>
				<!-- end button for mobile -->
				<h1 class="subheader-title ml-1 ml-lg-0 text-capitalize">
					<span
						v-show="loading"
						class="spinner-border spinner-border-s mr-2"
						role="status"
						aria-hidden="true"
					></span>
					<i class="fas fa-folder-open mr-2 hidden-lg-down"></i>
					{{ activeFolder }}
				</h1>
				<div
					class="d-flex position-relative ml-auto"
					style="max-width: 23rem"
				>
					<i
						class="fas fa-search position-absolute pos-left fs-lg px-3 py-2 mt-1"
					></i>
					<input
						type="text"
						class="form-control bg-subtlelight pl-6"
						placeholder="Filter emails"
					/>
				</div>
			</div>
			<!-- end inbox title -->
			<!-- inbox button shortcut -->
			<div
				class="d-flex flex-wrap align-items-center pl-3 pr-1 py-2 px-sm-4 px-lg-5 border-faded border-top-0 border-left-0 border-right-0"
			>
				<div class="flex-1 d-flex align-items-center">
					<div
						class="custom-control custom-checkbox mr-2 mr-lg-2 d-inline-block"
					>
						<input
							type="checkbox"
							class="custom-control-input"
							id="js-msg-select-all"
						/>
						<label
							class="custom-control-label bolder"
							for="js-msg-select-all"
						></label>
					</div>
					<button
						@click="sync"
						class="btn btn-icon rounded-circle mr-1 waves-effect waves-themed"
					>
						<i class="fas fa-redo fs-md"></i>
					</button>
					<!--                    <a href="javascript:void(0);"-->
					<!--                       class="btn btn-icon rounded-circle mr-1 waves-effect waves-themed">-->
					<!--                        <i class="fas fa-exclamation-circle fs-md"></i>-->
					<!--                    </a>-->
					<a
						href="javascript:void(0);"
						id="js-delete-selected"
						class="btn btn-icon rounded-circle mr-1 waves-effect waves-themed"
					>
						<i class="fas fa-trash fs-md"></i>
					</a>
				</div>
				<div class="text-muted mr-1 mr-lg-3 ml-auto">
					{{ paginateRange.range }}
					<span class="hidden-lg-down">
						of {{ paginateRange.total }}</span
					>
				</div>
				<div class="d-flex flex-wrap">
					<button
						class="btn btn-icon rounded-circle waves-effect waves-themed"
						:disabled="this.page === 1"
						@click="prevPage"
					>
						<i class="fal fa-chevron-left fs-md"></i>
					</button>
					<button
						class="btn btn-icon rounded-circle waves-effect waves-themed"
						@click="nextPage"
						:disabled="isLastPage"
					>
						<i class="fal fa-chevron-right fs-md"></i>
					</button>
				</div>
			</div>
			<!-- end inbox button shortcut -->
		</div>
		<!-- end inbox header -->
		<!-- inbox message -->
		<div
			class="flex-wrap align-items-center flex-grow-1 position-relative bg-gray-50"
		>
			<vuescroll
				:ops="vScroll"
				class="position-absolute pos-top pos-bottom w-100"
			>
				<div
					class="d-flex h-100 flex-column"
					style="overflow: hidden; width: auto; height: 100%"
				>
					<!-- message list (the part that scrolls) -->
					<ul
						id="js-emails"
						class="notification notification-layout-2"
					>
						<li
							v-for="v in messages"
							:key="v.id"
							:class="{ unread: v.tags.includes('unread') }"
						>
							<div
								class="d-flex align-items-center px-3 px-sm-4 px-lg-5 py-1 py-lg-0 height-4 height-mobile-auto"
							>
								<div
									class="custom-control custom-checkbox mr-3 order-1"
								>
									<input
										type="checkbox"
										class="custom-control-input"
										:id="'msg-' + v.id"
									/>
									<label
										class="custom-control-label"
										:for="'msg-' + v.id"
									></label>
								</div>
								<a
									href="#"
									title="starred"
									class="d-flex align-items-center py-1 ml-2 mt-4 mt-lg-0 ml-lg-0 mr-lg-4 fs-lg color-warning-500 order-3 order-lg-2"
									><i class="fas fa-star"></i
								></a>
								<div
									class="d-flex flex-row flex-wrap flex-1 align-items-stretch align-self-stretch order-2 order-lg-3"
								>
									<div class="row w-100">
										<a
											:href="'#openThread' + v.thread_id"
											@click.prevent="
												openThread(v.thread_id)
											"
											class="name d-flex width-sm align-items-center pt-1 pb-0 py-lg-1 col-12 col-lg-auto"
										>
											<span
												v-if="v.miscs.from"
												v-text="v.miscs.from.name"
											></span>
											<span v-else-if="v.miscs.to">{{
												v.miscs.to.name
													? v.miscs.to.name
													: v.miscs.to.email
											}}</span>
											<span v-else>Draft</span>
										</a>
										<a
											:href="'#openThread' + v.thread_id"
											@click.prevent="
												openThread(v.thread_id)
											"
											class="name d-flex align-items-center pt-0 pb-1 py-lg-1 flex-1 col-12 col-lg-auto"
											v-text="v.subject"
										></a>
									</div>
								</div>
								<div
									class="fs-sm text-muted ml-auto hide-on-hover-parent order-4 position-on-mobile-absolute pos-top pos-right mt-2 mr-3 mr-sm-4 mt-lg-0 mr-lg-0"
								>
									<span
										class="mr-4 text-warning fs-nano"
										v-if="isMultiAccounts"
									>
										{{ accounts[v.account_id].email }}
									</span>
									{{ v.created_at | formatDate }}
								</div>
							</div>
						</li>
					</ul>
					<!-- end message list -->
				</div>
			</vuescroll>
		</div>
		<!-- end inbox message -->
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';
import vuescroll from 'vuescroll';
import { mapGetters } from 'vuex';

export default {
	name: 'MessagesList',
	components: {
		vuescroll,
	},
	filters: {
		formatDate,
	},
	props: {
		activeFolder: {
			type: String,
			required: true,
		},
		isLastPage: {
			type: Boolean,
			required: true,
		},
		loading: {
			type: Boolean,
			required: true,
		},
		messages: {
			type: Array,
			required: true,
		},
		page: {
			type: Number,
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
		...mapGetters({
			onPage: 'mailbox/onPage',
			isMultiAccounts: 'mailbox/isMultiAccounts',
			accounts: 'mailbox/getAccounts',
		}),
	},
	methods: {
		nextPage() {
			this.$emit('nextPage');
		},
		openThread(thread_id) {
			this.$emit('openThread', thread_id);
		},
		prevPage() {
			this.$emit('prevPage');
		},
		sync() {
			this.$emit('sync', {
				mode: 'refresh',
			});
		},
	},
};
</script>

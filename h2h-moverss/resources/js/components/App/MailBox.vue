<template>
	<div class="d-flex flex-grow-1 p-0" :class="{ 'cursor-wait': loading }">
		<sidebar
			:active-folder="activeFolder"
			@changeFolder="changeFolder"
		></sidebar>
		<div
			class="slide-backdrop"
			data-action="toggle"
			data-class="slide-on-mobile-left-show"
			data-target="#js-inbox-menu"
		></div>
		<messages-list
			v-if="!openedThreadId && activeFolder !== 'settings'"
			:loading="loading"
			:messages="messages"
			:active-folder="activeFolder"
			:page="page"
			:is-last-page="isLastPage"
			:paginate-range="paginateRange"
			@sync="sync"
			@prevPage="prevPage"
			@nextPage="nextPage"
			@openThread="openThread"
		></messages-list>
		<inbox
			v-if="openedThreadId"
			:loading="loadingMsg"
			:opened-thread-id="openedThreadId"
			:active-folder="activeFolder"
			:paginate-range="paginateRange"
			@openThread="openThread"
			@sync="sync"
		></inbox>
		<settings v-if="activeFolder === 'settings'"></settings>
		<compose></compose>
	</div>
</template>

<script>
import MD5 from 'crypto-js/md5';
import { mapGetters } from 'vuex';
import Compose from './MailBox/Compose';
import Inbox from './MailBox/Inbox';
import MessagesList from './MailBox/MessagesList';
import Settings from './MailBox/Settings';

import Sidebar from './MailBox/Sidebar';

let myTimer = null;
axios.defaults.timeout = 0;

export default {
	name: 'AppMailBox',
	components: { Settings, Inbox, Compose, MessagesList, Sidebar },
	data() {
		return {
			loading: false,
			loadingMsg: false,
			activeFolder: 'inbox',
			openedThreadId: null,
			page: 1,
			max_requests: 0,
			openSettings: false,
		};
	},
	computed: {
		isLastPage() {
			return (
				this.page * this.onPage >
				this.folderMeta(this.activeFolder).total
			);
		},
		messages() {
			let start = (this.page - 1) * this.onPage,
				end = start + this.onPage;

			return this.mailMessages
				.slice()
				.sort((a, b) => {
					// Сортировка по дате
					return new Date(b.created_at) - new Date(a.created_at);
				})
				.filter((item) => {
					return item.tag === this.activeFolder;
				})
				.slice(start, end);
		},
		paginateRange() {
			let start = (this.page - 1) * this.onPage;
			let end = start + this.onPage;
			if (!start) start = 1;
			if (end > this.folderMeta(this.activeFolder).total)
				end = this.folderMeta(this.activeFolder).total;

			return {
				range: `${start} - ${end}`,
				total: this.folderMeta(this.activeFolder).total,
			};
		},
		shownVsNeed() {
			if (this.activeFolder === 'settings') return;

			let start = (this.page - 1) * this.onPage;
			let end = start + this.onPage;

			if (end > this.folderMeta(this.activeFolder).total)
				end = this.folderMeta(this.activeFolder).total;

			let shown = this.messages.length,
				need = end - start;

			// console.log(`shown ${shown}; need ${need}`);
			if (!this.loading && need && shown < need) {
				console.log({
					mode: 'fetch',
					tag: this.activeFolder,
					on_page: this.onPage,
					start: start,
				});

				if (this.max_requests < 500) {
					this.sync({
						mode: 'fetch',
						tag: this.activeFolder,
						on_page: this.onPage,
						start,
					});
					this.max_requests++;
				}
			}

			return need && shown < need;
		},
		...mapGetters({
			folderMeta: 'mailbox/folderMeta',
			mailMessages: 'mailbox/getMessages',
			onPage: 'mailbox/onPage',
			accounts: 'mailbox/getAccounts',
			currentDate: 'mailbox/getCurrentDate',
		}),
	},
	watch: {
		shownVsNeed(v) {
			if (!this.loading) {
				console.log('shownVsNeed', v);
			}
		},
	},
	mounted() {
		initApp.pushSettings('nav-function-minify layout-composed', false);
		this.parseParams();

		this.sync({
			mode: 'init',
		});

		this.runTimer();
	},
	methods: {
		changeFolder(value) {
			this.activeFolder = value;
			this.page = 1;
			this.openedThreadId = null;

			this.urlChangeParams('Change page', {
				folder: value,
				page: 1,
				thread_id: null,
			});
		},
		nextPage() {
			this.page++;

			this.urlChangeParams('Change page ++', {
				page: this.page,
			});
		},
		openMsg(thread_id) {
			this.loadingMsg = true;
			axios
				.post('/mailbox/open', {
					thread_id,
				})
				.then((resp) => {
					if (resp.data.messages) {
						resp.data.messages.forEach((item) => {
							if (item.miscs.from) {
								item.miscs.from.md5 = MD5(
									item.miscs.from.email
								).toString();
							}
							if (item.miscs.to) {
								item.miscs.to.md5 = MD5(
									item.miscs.to.email
								).toString();
							}
							if (item.miscs.delivered_to) {
								item.miscs.delivered_to_md5 = MD5(
									item.miscs.delivered_to
								).toString();
							}
						});

						this.$store.dispatch('mailbox/pushThreadMsg', {
							thread_id,
							messages: resp.data.messages,
						});
					}
				})
				.finally(() => (this.loadingMsg = false));
		},
		openThread(thread_id = null) {
			this.openedThreadId = thread_id;

			this.urlChangeParams('openThread ' + thread_id, {
				thread_id,
			});

			if (thread_id) {
				this.openMsg(thread_id);
			}
		},
		parseParams() {
			let url = new URL(window.location.href),
				folder = url.searchParams.get('folder'),
				page = url.searchParams.get('page');
			if (
				folder !== this.activeFolder &&
				[
					'inbox',
					'draft',
					'sent',
					'spam',
					'trash',
					'settings',
				].includes(folder)
			)
				this.changeFolder(folder);
			if (page && parseInt(page) && page !== this.page)
				this.page = parseInt(page);
		},
		prevPage() {
			this.page--;

			this.urlChangeParams('Change page --', {
				page: this.page,
			});
		},
		runTimer() {
			clearInterval(myTimer);
			myTimer = setInterval(
				() =>
					this.sync({
						mode: 'auto_refresh',
					}),
				30 * 1000
			); // Every 30 sec
		},
		sync(obj = {}) {
			this.runTimer();

			if (obj.mode === 'init') {
				// Дотягиваем нужные записи при инициализации
				obj = {
					...obj,
					tag: this.activeFolder,
					on_page: this.onPage * this.page,
					start: 0,
				};
			}

			obj.currentDate = this.currentDate;

			this.loading = true;
			axios
				.post('/mailbox', obj)
				.then((resp) => {
					if (resp.data.messages.records.length) {
						if (obj.mode === 'init')
							this.$store.dispatch(
								'mailbox/recordsAdd',
								resp.data.messages.records
							);
						else
							this.$store.dispatch(
								'mailbox/recordsUpdate',
								resp.data.messages.records
							);
					}

					if (resp.data.accounts) {
						this.$store.commit(
							'mailbox/setAccounts',
							resp.data.accounts
						);
					}
					if (resp.data.meta) {
						this.$store.dispatch(
							'mailbox/metaUpdate',
							resp.data.meta
						);
					}
					this.$store.commit('mailbox/updateLastSync', {
						lastSync: resp.data.lastSync,
						currentDate: resp.data.currentDate,
					});
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				})
				.finally(() => (this.loading = false));
		},
		urlChangeParams(title, params) {
			let allowedParams = ['folder', 'page', 'thread_id'],
				url = new URL(window.location.href),
				generateParams = {};

			allowedParams.forEach((key) => {
				if (url.searchParams.get(key)) {
					generateParams[key] = url.searchParams.get(key);
				}
			});

			generateParams = {
				...generateParams,
				...params,
			};

			// Удаляем параметры без значений
			let newParams = {};
			for (let k in generateParams) {
				if (generateParams[k]) newParams[k] = generateParams[k];
			}

			window.history.pushState(
				newParams,
				title,
				'/mailbox/?' + jQuery.param(newParams)
			);
		},
	},
};
</script>

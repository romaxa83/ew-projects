<template>
	<div class="root">
		<div class="container">
			<div class="header fw-500">
				<div class="icon bg-gray-300">
					<Icon icon="incoming-call" />
				</div>
				<div class="title">
					Incoming calls
					<span
						v-if="refreshing"
						role="status"
						aria-hidden="true"
						title="Refreshing..."
						class="spinner-border spinner-border-sm fs-nano opacity-50 ml-1"
					/>
				</div>
				<div v-if="!loading" class="qty">{{ calls.length }}</div>
			</div>
			<div class="body">
				<div v-if="loading" class="info-msg">
					<span
						role="status"
						aria-hidden="true"
						class="spinner-border spinner-border-sm mr-1 fs-nano"
					/>
					Loading...
				</div>
				<div v-else-if="!!fetchError" class="error-msg">
					<div><b>Server Error:</b></div>
					{{ fetchError }}
				</div>
				<div v-else-if="calls.length" class="employee">
					<Call
						v-for="(call, i) in calls"
						:key="`${i}-${calls.id}-${call.caller.phone}`"
						:record="call"
					/>
				</div>
				<div v-else class="info-msg">No incoming calls</div>
			</div>
		</div>
	</div>
</template>

<script>
import Icon from '../Icon.vue';
import Call from './Call.vue';

export default {
	name: 'IncomingCalls',
	components: {
		Icon,
		Call,
	},
	data() {
		return {
			loading: true,
			refreshing: false,
		};
	},
	computed: {
		calls() {
			return this.$store.state.communicationsIncomingCalls.calls;
		},
		fetchError() {
			return this.$store.state.communicationsIncomingCalls.fetchError;
		},
	},
	methods: {
		reload() {
			this.refreshing = true;
			this.$store
				.dispatch('communicationsIncomingCalls/fetchCalls')
				.finally(() => {
					this.refreshing = false;
				});
		},
	},
	async mounted() {
		this.loading = true;
		this.$store
			.dispatch('communicationsIncomingCalls/fetchCalls')
			.finally(() => {
				this.loading = false;

				if (window.Echo) {
					const trigger = (event) => {
						console.log(
							`WS: Incoming Calls channel emit event "${event}"! Refreshing...`
						);
						this.reload();
					};
					window.Echo.channel('incoming.call.start').listen(
						'.incoming.call.start',
						() => {
							trigger('.incoming.call.start');
						}
					);
					window.Echo.channel('incoming.call.end').listen(
						'.incoming.call.end',
						() => {
							trigger('.incoming.call.end');
						}
					);
				}
			});
	},
};
</script>

<style lang="scss" scoped>
.root {
	height: var(--incoming-calls-panel-height);
	padding: 12px;
	position: relative;
	border-bottom: 4px solid rgba(0, 0, 0, 0.04);
}

.container {
	display: flex;
	flex-direction: column;
	flex-shrink: 0;
	position: relative;
	height: 100%;
	padding: 16px 16px 10px;
	border-radius: 12px;
	border: 1px solid #e0e0e0;
	box-shadow: 0 0 13px 0 rgba(62, 44, 90, 0.08);
}

.header {
	display: flex;
	gap: 12px;
	align-items: safe center;
	padding-bottom: 7px;

	& > * {
		flex-shrink: 0;
	}

	.icon {
		width: 28px;
		height: 28px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		color: #495057;

		svg {
			width: 12px;
			height: 12px;
		}
	}

	.title {
		flex-grow: 1;
	}
}

.body {
	flex-grow: 1;
	position: relative;
	overflow: auto;
}

.info-msg {
	text-align: center;
	padding: 16px;
}

.error-msg {
	padding: 16px;
	color: red;
}
</style>

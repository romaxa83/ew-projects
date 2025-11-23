<template>
	<div class="root">
		<div class="header border-bottom">
			<div class="title fs-lg fw-500">
				Employee statuses
				<span
					v-if="refreshing"
					role="status"
					aria-hidden="true"
					title="Refreshing..."
					class="spinner-border spinner-border-sm fs-nano opacity-50 ml-1"
				/>
			</div>
			<div v-if="!loading" class="d-contents">
				<label
					class="status-switcher custom-control custom-switch"
					:class="{ disabled: refreshing }"
				>
					<span>Show offline</span>
					<span class="switcher">
						<input
							type="checkbox"
							class="custom-control-input"
							v-model="showOffline"
							:disabled="refreshing"
						/>
						<span class="custom-control-label"></span>
					</span>
				</label>
				<div class="employee-bar">
					<div class="stats">
						<Stat
							v-if="!fetchError"
							v-for="stat in stats"
							:key="stat.status"
							:stat="stat"
							class="stat"
						/>
					</div>
					<button
						class="update"
						:class="refreshing && 'disabled'"
						@click="reload"
					>
						<i class="fas fa-sync"></i>
						<span class="fw-300 fs-nano">Update</span>
					</button>
				</div>
			</div>
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
			<div v-else-if="employees.length" class="employee">
				<Record
					v-for="employee in employees"
					:key="employee.id"
					:record="employee"
				/>
			</div>
			<div v-else class="info-msg">No employee available</div>
		</div>
	</div>
</template>

<script>
import Record from './Record.vue';
import Stat from './Stat.vue';

export default {
	name: 'Panel',
	components: {
		Record,
		Stat,
	},
	data() {
		return {
			loading: true,
			refreshing: false,
		};
	},
	computed: {
		stats() {
			return this.$store.state.communicationsEmployee.stats;
		},
		employees() {
			return this.$store.state.communicationsEmployee.employees;
		},
		fetchError() {
			return this.$store.state.communicationsEmployee.fetchError;
		},
		showOffline: {
			get() {
				return this.$store.state.communicationsEmployee.fetchParams
					.showOffline;
			},
			set(value) {
				this.$store.commit(
					'communicationsEmployee/setShowOffline',
					value
				);
				this.reload();
			},
		},
	},
	methods: {
		reload() {
			this.refreshing = true;
			this.$store
				.dispatch('communicationsEmployee/fetchEmployees')
				.finally(() => {
					this.refreshing = false;
				});
		},
		refresh() {
			this.refreshing = true;
			this.$store
				.dispatch('communicationsEmployee/refreshEmployees')
				.finally(() => {
					this.refreshing = false;
				});
		},
	},
	async mounted() {
		this.loading = true;
		this.$store
			.dispatch('communicationsEmployee/fetchEmployees')
			.finally(() => {
				this.loading = false;
				if (window.Echo) {
					const channel = 'employee.change.communication.status';
					const event = '.employee.change.communication.status';
					window.Echo.channel(channel).listen(event, () => {
						console.log(
							'WS: Employee status changed! Refreshing...'
						);
						this.refresh();
					});
				}
			});
	},
};
</script>

<style lang="scss" scoped>
.root {
	display: flex;
	flex-direction: column;
	flex-shrink: 0;
	height: var(--employee-panel-height);
}

.header {
	display: flex;
	flex-direction: column;
	gap: 12px;
	padding: 16px;
}

.status-switcher {
	border-radius: 12px;
	padding: 8px 12px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 8px;
	cursor: pointer;
	background: #f3f3f3;

	&.disabled {
		pointer-events: none;
	}

	&:not(.disabled):hover {
		background: #e1ebdc;
	}

	&:not(.disabled):active {
		background: #e7e0e0;
	}
}

.switcher {
	height: 20px;
}

.employee-bar {
	display: flex;
	gap: 12px;
}

.update {
	border-width: 0;
	background: none;
	display: flex;
	flex-direction: column;
	flex-shrink: 0;
	gap: 4px;
	align-items: center;
	width: 54px;
	height: 48px;
	justify-content: center;
	border-radius: 8px;
	padding-top: 5px;
	color: inherit;
}

.update,
.status-switcher {
	background: #f3f3f3;

	&:focus-visible {
		outline: 1px solid blue;
	}

	&.disabled {
		pointer-events: none;
	}

	&:not(.disabled):hover {
		background: #e1ebdc;
	}

	&:not(.disabled):active {
		background: #e7e0e0;
	}
}

.stats {
	flex-grow: 1;
	display: flex;
	align-items: flex-start;
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

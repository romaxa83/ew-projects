<template>
	<div class="d-inline-block">
		<a
			href="#"
			class="header-icon cursor-pointer"
			data-toggle="dropdown"
			title="Zadarma settings"
		>
			<i
				:class="[isCallWidgetEnabled ? 'fas' : 'fal']"
				class="fa-phone"
			></i>
			<span
				v-if="activeCallsCount"
				style="left: 2rem"
				class="badge badge-icon"
				>{{ activeCallsCount }}</span
			>
		</a>
		<div class="dropdown-menu dropdown-menu-animated dropdown-lg">
			<div
				class="dropdown-header bg-trans-gradient justify-content-center rounded-top pt-3 pb-2"
			>
				<h4 class="text-center color-white">PBX account</h4>
			</div>
			<div class="settings-panel mb-3">
				<div class="mt-2 d-table w-100 px-5">
					<div class="d-table-cell align-middle">
						<h5 class="p-0">Settings</h5>
					</div>
				</div>
				<div class="list" id="f4452">
					<div class="switch-checkbox">
						<b-form-checkbox
							v-model="widgetStatus"
							name="check-button"
							switch
						>
						</b-form-checkbox>
					</div>
					<span class="onoffswitch-title"
						>Enable floating widget</span
					>
					<span class="onoffswitch-title-desc"
						>widget with caller info</span
					>
				</div>
				<hr class="mb-0 mt-4" />
				<div class="mt-1 d-table w-100 px-5">
					<div class="d-table-cell align-middle">
						<h5 class="p-0 text-primary">PBX credentials</h5>
					</div>
				</div>
				<div class="mt-1 w-100 px-5">
					<div class="form-label">User</div>
					<div class="input-group input-group-sm">
						<input
							id="input-group-sm-size"
							@focus="$event.target.select()"
							type="text"
							class="form-control"
							readonly
							aria-describedby="input-group-sm-size"
							:value="user"
							ref="user"
						/>
						<div class="input-group-append">
							<span
								class="input-group-text cursor-pointer"
								title="Click to copy"
								@click="copyText('user')"
							>
								<i class="fal fa-copy"></i>
							</span>
						</div>
					</div>
					<div class="form-label mt-2">Password</div>
					<div class="input-group input-group-sm">
						<input
							id="input-group-sm-size"
							@focus="$event.target.select()"
							type="text"
							class="form-control"
							readonly
							aria-describedby="input-group-sm-size"
							:value="password"
							ref="password"
						/>
						<div class="input-group-append">
							<span
								class="input-group-text cursor-pointer"
								title="Click to copy"
								@click="copyText('password')"
							>
								<i class="fal fa-copy"></i>
							</span>
						</div>
					</div>
					<!--                    <div class="form-label mt-2">-->
					<!--                        Token-->
					<!--                    </div>-->
					<!--                    <div class="input-group input-group-sm">-->
					<!--                        <input id="input-group-sm-size" type="text" class="form-control" readonly aria-describedby="input-group-sm-size" value="3654654-45">-->
					<!--                        <div class="input-group-append">-->
					<!--                                                        <span class="input-group-text cursor-pointer" title="Click to copy">-->
					<!--                                                            <i class="fal fa-copy"></i>-->
					<!--                                                        </span>-->
					<!--                        </div>-->
					<!--                    </div>-->
					<div class="mt-2">
						<u
							><a
								href="https://zadarma.com/en/support/instructions/"
								target="_blank"
								rel="”nofollow”"
								class="font"
								>VoIP app guide</a
							></u
						>
					</div>
				</div>
			</div>
		</div>

		<!--        <a href="/toggle-call-widget" @click.prevent="toggleWidget()" class="header-icon cursor-pointer" data-toggle="tooltip" data-placement="top" title=""-->
		<!--           data-original-title="Call Widget">-->
		<!--            <i :class="[isCallWidgetEnabled ? 'fas' : 'fal']" class="fa-phone"></i>-->
		<!--            <span v-if="activeCallsCount" style="left: 2rem;" class="badge badge-icon">{{ activeCallsCount }}</span>-->
		<!--        </a>-->
	</div>
</template>

<script>
import { BFormCheckbox } from 'bootstrap-vue';

export default {
	name: 'CallWidgetStatus',
	props: ['user', 'password'],
	components: { BFormCheckbox },
	data() {
		return {
			checked: false,
		};
	},
	computed: {
		widgetStatus: {
			get() {
				return this.$store.state.calls.isWidgetEnabled;
			},
			set() {
				this.$store.commit('calls/toggleCallWidget');
			},
		},
		isCallWidgetEnabled() {
			return this.$store.state.calls.isWidgetEnabled;
		},
		activeCallsCount() {
			return this.$store.state.calls.records.length;
		},
	},
	methods: {
		copyText(ref) {
			// this.$refs[ref].focus();
			// console.log(this.$refs[ref].value);
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(this.$refs[ref].value);
			} else {
				this.$refs[ref].focus();
				try {
					document.execCommand('copy');
				} catch (error) {
					console.error(error);
				}
			}
		},
		toggleWidget() {
			this.$store.commit('calls/toggleCallWidget');
		},
	},
};
</script>

<style scoped>
.switch-checkbox {
	position: absolute;
	right: 1rem;
	margin: 0;
	top: 30%;
}
</style>

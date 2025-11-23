<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>
				<span
					v-show="calls.loading"
					class="spinner-border spinner-border-sm mr-2"
					role="status"
					aria-hidden="true"
				></span>
				Calls (via RingCentral)<span
					class="ml-2 badge badge-warning"
					v-text="calls.total"
				></span>
			</h2>
		</div>
		<div class="panel-container collapse show">
			<div class="panel-content pt-0">
				<ul class="activity-timeline">
					<li v-for="(v, index) in calls.records">
						<button
							class="btn btn-sm btn-default btn-icon activity-timeline-icon rounded-circle js-waves-off"
							@click="toggle(index)"
						>
							<i
								class="fas"
								:title="`${v.direction} - ${v.result}`"
								:class="{ 'fa-microphone': v.recording }"
							></i>
						</button>
						<div class="card mb-2">
							<div
								class="card-header bg-white py-2 pr-2 d-flex align-items-center flex-wrap"
							>
								<div class="fs-xs">
									{{ v.direction }}
									<template v-if="v.direction === 'Inbound'">
										call from: {{ v.from.phoneNumber }}
										<span
											v-if="v.from.name"
											v-text="v.from.name"
										></span>
									</template>
									<template
										v-else="v.direction === 'Outbound'"
									>
										call to: {{ v.to.phoneNumber }}
										<span
											v-if="v.to.name"
											v-text="v.to.name"
										></span>
									</template>
									<span
										v-if="
											v.result !== 'Accepted' &&
											v.result !== 'Call connected'
										"
										class="text-danger"
										v-text="v.result"
									></span>

									<i
										v-if="v.recording"
										@click="toggle(index)"
										class="fas ml-4 cursor-pointer color-primary"
										:class="{
											'fa-chevron-down': !v.opened,
											'fa-chevron-up': v.opened,
										}"
									></i>
								</div>
								<div
									class="d-flex position-relative ml-auto pr-2"
								>
									<span class="fs-xs text-muted">
										{{ v.startTime | formatDate }}
										<span class="font-weight-bold">{{
											getDuration(v.duration)
										}}</span>
									</span>
								</div>
							</div>
							<div v-if="v.opened" class="card-body fs-xs py-2">
								<audio
									controls
									:src="`/orders/calls/record/${v.recording.id}`"
								>
									Your browser does not support the
									<code>audio</code> element. o_0
								</audio>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';
import { mapGetters } from 'vuex';

export default {
	name: 'CommunicationsCalls',
	filters: {
		formatDate,
	},
	computed: {
		...mapGetters({
			calls: 'orderActivity/callRecords',
		}),
	},
	methods: {
		getDuration(duration) {
			return (
				'[ ' + moment.utc(duration * 1000).format('mm:ss [sec]') + ' ]'
			);
		},
		toggle(index) {
			if (!this.calls.records[index].recording) {
				App.Forms.simpleErrors({ msg: 'No recording' });
				return;
			}

			this.$store.commit('orderActivity/toggleCall', index);
		},
	},
};
</script>

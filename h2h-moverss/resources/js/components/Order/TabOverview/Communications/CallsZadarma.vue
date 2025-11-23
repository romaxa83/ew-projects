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
				Calls (via Zadarma)<span
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
							class="btn btn-sm btn-icon activity-timeline-icon rounded-circle js-waves-off"
							:class="[
								v.call_id_with_rec
									? 'btn-success'
									: 'btn-danger',
							]"
							@click="
								toggle(v.call_id_with_rec, v.pbx_call_id, index)
							"
						>
							<i
								:class="[
									v.call_id_with_rec
										? 'fas fa-microphone'
										: 'fas fa-phone',
								]"
							></i>
						</button>
						<div class="card mb-2">
							<div
								class="card-header bg-white py-2 pr-2 d-flex align-items-center flex-wrap"
							>
								<div class="fs-xs mr-auto">
									<template v-if="v.event === 'NOTIFY_END'">
										Call from: {{ v.caller_id }}
										<!--                                        <span v-if="v.from.name" v-text="v.from.name"></span>-->
									</template>
									<template
										v-else="v.event === 'NOTIFY_OUT_END'"
									>
										Call to: {{ v.destination }}
										<!--                                        <span v-if="v.to.name" v-text="v.to.name"></span>-->
									</template>
									<span
										v-if="v.disposition === 'answered'"
										class="text-success pl-1"
									>
										{{ v.disposition.capitalize() }}
										<span class="pl-1" v-if="v.employeeData"
											>({{
												v.employeeData.name +
												' ' +
												v.employeeData.l_name
											}})</span
										>
									</span>
									<span v-else class="text-danger pl-1"
										>{{ v.disposition.capitalize() }}
										<span class="pl-1" v-if="v.employeeData"
											>({{
												v.employeeData.name +
												' ' +
												v.employeeData.l_name
											}})</span
										>
									</span>
								</div>
								<div class="d-flex position-relative pr-2">
									<span class="fs-xs text-muted">
										{{ v.call_start | formatDate }}
										<span class="font-weight-bold">{{
											getDuration(v.duration)
										}}</span>
									</span>
								</div>
							</div>
							<div v-if="v.opened" class="card-body fs-xs py-2">
								<div v-if="v.links.length == 0">Loading...</div>
								<div v-if="v.links.length > 0">
									<audio
										v-for="(
											recordLink, recordLinkID
										) in v.links"
										controls
										:src="recordLink"
									>
										Your browser does not support the
										<code>audio</code> element. o_0
									</audio>
								</div>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';
import formatDate from '@/filters/formatDate.filter';

Object.defineProperty(String.prototype, 'capitalize', {
	value: function () {
		return this.charAt(0).toUpperCase() + this.slice(1);
	},
	enumerable: false,
});

export default {
	name: 'CallsZadarma',
	filters: {
		formatDate,
	},
	computed: {
		...mapGetters({
			calls: 'orderActivity/getZadarmaCalls',
		}),
	},
	methods: {
		getDuration(duration) {
			return (
				'[ ' + moment.utc(duration * 1000).format('mm:ss [sec]') + ' ]'
			);
		},
		toggle(call_id_with_rec, pbx_call_id, index) {
			if (!call_id_with_rec) {
				App.Forms.simpleErrors({ msg: 'No recording' });
				return;
			}
			axios
				.get(
					'/orders/recordZadarma?callID=' +
						call_id_with_rec +
						'&pbx_callID=' +
						pbx_call_id +
						'&orderID=' +
						this.$store.state.session.order.id
				)
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store.commit(
							'orderActivity/setZadarmaCallRecord',
							{ index, links: resp.data.links }
						);
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.finally(() => (this.loading = false))
				.catch((error) => App.Forms.simpleErrors(error.response.data));
			this.$store.commit('orderActivity/toggleZadarmaCall', index);
		},
	},
};
</script>

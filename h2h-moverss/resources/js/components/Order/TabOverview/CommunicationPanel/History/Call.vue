<template>
	<li
		class="d-flex"
		:class="[direction == 'inbound' ? 'inbound-block' : 'outbound-block']"
	>
		<!--        <div>-->
		<!--        <button class="btn btn-lg btn-white btn-icon activity-timeline-icon-block rounded-circle js-waves-off">-->
		<!--            <i title="messages"-->
		<!--               class="fal fa-2x fa-phone"></i>-->
		<!--        </button>-->
		<!--        </div>-->

		<div
			class="card card-zoom-hover"
			:class="{ 'border border-primary': record.selected }"
		>
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap"
			>
				<!--                <h4 v-if="interface == 'flow'" class="my-0 mr-1 cursor-pointer">-->
				<!--                    <span v-if="orderID" class="badge badge-secondary">-->
				<!--                        <a class="text-white" style="text-decoration: none" :href="'/orders/'+orderID" target="_blank">Order #{{ orderID }} <i-->
				<!--                            class="fas fa-external-link"></i></a></span>-->
				<!--                    <span v-else class="badge badge-warning" @click="$emit('assign', index)">Assign</span>-->
				<!--                </h4>-->
				<div class="mr-1"><i class="fas fa-phone"></i></div>
				<div class="fs-xs pr-3 text-muted">
					<b>[Zadarma] Call {{ callLine }} {{ titleTextBy }}</b>
				</div>
				<div
					class="ml-auto d-flex position-relative pr-2 fs-xs text-muted"
				>
					{{ datetime }}
				</div>
			</div>
			<div class="card-body fs-sm py-2">
				<div class="d-flex fs-md flex-wrap">
					<div class="mr-2" v-html="callType"></div>
					<h5 class="mt-0">
						<span
							class="badge ttu"
							:class="{
								'badge-danger': item.disposition !== 'answered',
								'badge-success': item.disposition === 'answered',
							}"
						>
							{{ item.disposition }}
						</span>
					</h5>
					<div v-if="item.is_recorded" class="ml-2">
						<button
							@click="getRecord"
							class="btn btn-outline-secondary waves-effect waves-themed px-1 py-0"
						>
							Listen {{ duration }}
						</button>
					</div>
				</div>
				<div class="mt-1">
					<div v-if="callRecords.length > 0">
						<audio
							v-for="(recordLink, recordLinkID) in callRecords"
							controls
							:src="recordLink"
						>
							Your browser does not support the
							<code>audio</code> element. o_0
						</audio>
					</div>
				</div>

				<!--                <del>Description about task. Tra la-la-la-la</del>-->
			</div>
		</div>
	</li>
</template>

<script>
import { axiosPromise } from '@/helpers/axiosPromise';
import { mapGetters } from 'vuex';

export default {
	name: 'Call',
	props: ['record', 'datetime', 'interface', 'index'],
	data: () => ({
		loading: false,
		callRecords: [],
	}),
	computed: {
		...mapGetters({
			orderID: 'getOrderId',
		}),
		item() {
			return this.record.item;
		},
		orderID() {
			if (this.record.orderID) return this.record.orderID;
			return null;
		},

		callHeader() {
			if (this.item.event == 'NOTIFY_OUT_END') {
				return '<b>Call</b> to: ' + this.item.destination;
			} else if (this.item.event == 'NOTIFY_END') {
				return '<b>Call</b> from: ' + this.item.called_did;
			}
		},
		direction() {
			if (this.item.event == 'NOTIFY_END') return 'inbound';
			else if (this.item.event == 'NOTIFY_OUT_END') return 'outbound';
			return null;
		},
		titleTextBy() {
			if (this.anweredBy) return this.anweredBy;
			if (this.byEmployeeName) return this.byEmployeeName;
			return '';
		},
		anweredBy() {
			if (
				this.direction == 'inbound' &&
				this.item.internal &&
				this.item.disposition == 'answered' &&
				this.item?.internal_pbx_data?.employee
			) {
				return (
					' Answered by ' +
					this.item.internal_pbx_data.employee.name +
					' ' +
					this.item.internal_pbx_data.employee.l_name
				);
			}
			return null;
		},
		byEmployeeName() {
			if (
				this.direction == 'outbound' &&
				this.item.internal &&
				this.item?.internal_pbx_data?.employee
			) {
				return (
					' by ' +
					this.item.internal_pbx_data.employee.name +
					' ' +
					this.item.internal_pbx_data.employee.l_name
				);
			}
			return null;
		},
		callDirection() {
			if (this.item.event == 'NOTIFY_OUT_END') {
				return (
					'to: <span class="phone-link">' +
					this.item.destination +
					'</span>'
				);
			} else if (this.item.event == 'NOTIFY_END') {
				return (
					'from: <span class="phone-link">' +
					this.item.caller_id +
					'</span>'
				);
			}
		},
		callLine() {
			if (this.item.event == 'NOTIFY_END') {
				return 'to line: ' + this.item.called_did;
			}
			return '';
		},
		callType() {
			let text = '';
			if (this.item.event == 'NOTIFY_OUT_END') {
				text +=
					'<i class="fas fa-long-arrow-left"></i> Outbound call ' +
					this.callDirection;
			} else if (this.item.event == 'NOTIFY_END') {
				text +=
					'<i class="fas fa-long-arrow-right"></i> Inbound call ' +
					this.callDirection;
			}
			return text;
		},
		duration() {
			return moment.utc(+this.item.duration * 1000).format('mm:ss');
		},

		// author() {
		//     if (this.item.author.employee)
		//         return this.item.author.employee.name + ' ' + this.item.author.employee.l_name;
		//     return this.item.author.name;
		// }
	},
	methods: {
		getRecord() {
			this.loading = true;
			return axiosPromise(
				axios.get(
					'/orders/recordZadarma?callID=' +
						this.item.call_id_with_rec +
						'&pbx_callID=' +
						this.item.pbx_call_id +
						'&orderID=' +
						this.orderID
				)
			)
				.then(({ links }) => {
					this.callRecords = links;
					this.loading = false;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});
		},
	},
};
</script>

<template>
	<li
		class="d-flex"
		:class="[direction == 'inbound' ? 'inbound-block' : 'outbound-block']"
	>
		<div
			class="card card-zoom-hover"
			:class="{ 'border border-primary': record.selected }"
		>
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap"
			>
				<div class="mr-1"><i class="fas fa-phone"></i></div>
				<div class="fs-xs pr-3 text-muted">
					<b>[Ringostat] Call {{ callLine }} {{ titleTextBy }}</b>
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
								'badge-danger': item.status === statusNoAnswer,
								'badge-success':
									item.status === statusAnswered ||
									item.status === 'PROPER',
							}"
						>
							{{ item.status }}
						</span>
					</h5>
					<div v-if="item.recording_presence" class="ml-2">
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
			</div>
		</div>
	</li>
</template>

<script>
import {
	COMMUNICATION_STATUS_ANSWERED,
	COMMUNICATION_STATUS_NO_ANSWER,
} from '@/store/modules/constants';
import { mapGetters } from 'vuex';

export default {
	name: 'RingostatCall',
	props: ['record', 'datetime', 'interface', 'index'],
	data: () => ({
		loading: false,
		callRecords: [],
		statusNoAnswer: COMMUNICATION_STATUS_NO_ANSWER,
		statusAnswered: COMMUNICATION_STATUS_ANSWERED,
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
			if (this.item.type == 'out') {
				return '<b>Call</b> to: ' + this.item.destination;
			} else if (this.item.type == 'in') {
				return '<b>Call</b> from: ' + this.item.caller_number;
			}
		},
		direction() {
			if (this.item.type == 'in') return 'inbound';
			else if (this.item.type == 'out') return 'outbound';
			return null;
		},
		titleTextBy() {
			return this.anweredBy || '';
		},
		anweredBy() {
			switch (this.direction) {
				case 'inbound':
					return getEmployeeName(this.item, true);
				case 'outbound':
					return getEmployeeName(this.item, false);
				default:
					return null;
			}

			function getEmployeeName(item, answer) {
				const { employee_estension: number, employee } = item;
				const by = answer ? 'Answered by' : 'by';
				return +number ? ` ${by} ${employee} (${number})` : number;
			}
		},
		// byEmployeeName() {
		//     if (this.direction == 'outbound' && this.item.internal && this.item?.internal_pbx_data?.employee) {
		//         return ' by ' + this.item.internal_pbx_data.employee.name + ' ' + this.item.internal_pbx_data.employee.l_name;
		//     }
		//     return null;
		// },
		callDirection() {
			if (this.item.type == 'out') {
				return (
					'to: <span class="phone-link">' +
					this.item.destination +
					'</span>'
				);
			} else if (this.item.type == 'in') {
				return (
					'from: <span class="phone-link">' +
					this.item.caller_number +
					'</span>'
				);
			}
		},
		callLine() {
			if (this.direction == 'inbound') {
				return 'to line: ' + this.item.destination;
			} else if (this.direction == 'outbound') {
				return 'from line: ' + this.item.caller_number;
			}
			return '';
		},
		callType() {
			let text = '';
			if (this.item.type == 'out') {
				text +=
					'<i class="fas fa-long-arrow-left"></i> Outbound call ' +
					this.callDirection;
			} else if (this.item.type == 'in') {
				text +=
					'<i class="fas fa-long-arrow-right"></i> Inbound call ' +
					this.callDirection;
			}
			return text;
		},
		duration() {
			return moment
				.utc(+this.item.duration_conversation * 1000)
				.format('mm:ss');
		},
	},
	methods: {
		getRecord() {
			if (this.item.recording_wav)
				this.callRecords.push(this.item.recording_wav);
		},
	},
};
</script>

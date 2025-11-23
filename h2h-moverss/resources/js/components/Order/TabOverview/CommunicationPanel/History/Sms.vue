<template>
	<li
		class="d-flex"
		:class="[direction == 'inbound' ? 'inbound-block' : 'outbound-block']"
	>
		<!--        <div>-->
		<!--            <button class="btn btn-lg btn-white btn-icon activity-timeline-icon-block rounded-circle js-waves-off">-->
		<!--                <i title="messages"-->
		<!--                   class="fal fa-2x fa-sms"></i>-->
		<!--            </button>-->
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
				<div class="mr-1"><i class="fas fa-sms"></i></div>
				<div class="fs-xs pr-3 text-muted">
					<b>[Zadarma] Sms {{ smsType }}</b>
				</div>
				<div
					class="ml-auto d-flex position-relative pr-2 fs-xs text-muted"
				>
					{{ datetime }}
				</div>
			</div>
			<div class="card-body fs-sm py-2">
				<div class="fs-md"><span v-html="item.text"></span></div>
				<!--                <del>Description about task. Tra la-la-la-la</del>-->
			</div>
		</div>
	</li>
</template>

<script>
export default {
	name: 'Sms',
	props: ['record', 'datetime', 'interface', 'index'],
	data: () => ({
		directionIcon: '',
		orderID: null,
	}),
	computed: {
		item() {
			return this.record.item;
		},
		direction() {
			if (this.item.direction == 'inbound' || this.item.inbound == 1)
				return 'inbound';
			else return 'outbound';
		},
		smsType() {
			let text = '';
			if (this.item.inbound) {
				text = ' from ' + this.item.caller_id;
			} else {
				text = ' to ' + this.item.caller_did;
			}
			// if (this.item.event == 'NOTIFY_OUT_END') {
			//     text += '<i class="fas fa-long-arrow-left"></i> Outbound call ' + this.callDirection;
			// } else if (this.item.event == 'NOTIFY_END') {
			//     text += '<i class="fas fa-long-arrow-right"></i> Inbound call ' + this.callDirection;
			// }
			return text;
		},
	},
};
</script>

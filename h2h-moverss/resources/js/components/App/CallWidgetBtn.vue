<template>
	<div
		class="call-widget-footer-btn p-2"
		:class="{ active: isActive }"
		@click="changeActiveRecord"
	>
		<i class="fal fa-phone-alt"></i> {{ callAbbr }}
	</div>
</template>

<script>
export default {
	name: 'CallWidgetBtn',
	props: ['callRecord', 'index'],
	computed: {
		isActive() {
			return +this.activeRecordIdx == +this.index;
		},
		callAbbr() {
			let str = '';
			if (this.callRecord?.client) {
				if (
					this.callRecord.client.name &&
					this.callRecord.client.name.length > 0
				)
					str = this.callRecord.client.name.charAt(0);
				if (
					this.callRecord.client.lname &&
					this.callRecord.client.lname.length > 0
				)
					str += this.callRecord.client.lname.charAt(0);
			} else {
				str = 'C' + this.index;
			}
			return str;
		},
		// activeRecord() {
		//     return this.$store.state.calls.activeRecord;
		// },
		activeRecordIdx() {
			return this.$store.state.calls.activeRecordIdx;
		},
		callRecords() {
			return this.$store.state.calls.records;
		},
	},
	methods: {
		changeActiveRecord() {
			this.$store.commit('calls/setActiveRecordIdx', this.index);
		},
	},
};
</script>

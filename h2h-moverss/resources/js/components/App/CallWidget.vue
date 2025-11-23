<template>
	<div
		id="call-widget"
		ref="callWidget"
		class="call-widget position-absolute"
		:class="{
			'd-none': !currentRecord || !employeeInternal || !isWidgetEnabled,
		}"
		style="top: 400px; right: 50px"
	>
		<!-- Include a header DIV with the same name as the draggable DIV, followed by "header" -->
		<div
			ref="callWidgetHeader"
			class="call-widget--header py-3 px-3 bg-trans-gradient d-flex justify-content-center align-items-center rounded-top mb-2"
		>
			<h4 v-if="currentRecord" class="m-0 text-center color-white">
				{{ clientFullName }}
				<small class="mb-0 opacity-80">{{ timerStatus }}</small>
			</h4>
		</div>
		<div class="m-3" style="min-height: 100px" v-if="currentRecord">
			<div class="mb-4">
				<div class="d-flex mb-1">
					<div class="mr-2 mt-auto">
						<i class="fas fa-phone-alt"></i>
					</div>
					<div>
						<div>
							<small>{{ callDirectionText }}</small>
						</div>
						<span class="phone-link">{{
							currentRecord.channelContact
						}}</span>
					</div>
				</div>
				<div v-if="internalExtension" class="d-flex mb-1">
					<div class="mr-2 mt-auto">
						<i class="fas fa-phone-alt"></i>
					</div>
					<div>
						<div><small>PBX Extension</small></div>
						<span class="phone-link">{{ internalExtension }}</span>
					</div>
				</div>
			</div>

			<client-tags
				class="tags-box mt-2"
				v-if="clientTags"
				:tags="clientTags"
			></client-tags>
			<!--            <div v-if="clientTags" class="tags-box mt-2">-->
			<!--                <button v-for="v in clientTags" :key="v.id" type="button"-->
			<!--                        class="btn btn-xs mb-1 btn-secondary waves-effect waves-themed mr-1">-->
			<!--                    <i class="fas fa-tag"></i> {{ v.title }}-->
			<!--                </button>-->
			<!--            </div>-->

			<!--            <div class="tags-box my-2">-->
			<!--                <button type="button" class="btn btn-xs mb-1 btn-secondary waves-effect waves-themed mr-1"><i class="fas fa-tag"></i> Cool guy-->
			<!--                </button>-->
			<!--                <button type="button" class="btn btn-xs mb-1 btn-secondary waves-effect waves-themed mr-1"><i class="fas fa-tag"></i> Amigo-->
			<!--                </button>-->
			<!--            </div>-->

			<div v-if="clientOrdersCount" class="btn-group btn-group-sm">
				<a
					target="_blank"
					:href="'/orders?filter-client[]=' + currentRecord.client.id"
					class="btn btn-default waves-effect waves-themed"
				>
					Orders
					<span class="badge bg-primary-500 ml-2">{{
						clientOrdersCount
					}}</span></a
				>
			</div>
			<div class="mt-2" v-if="clientNotes">
				<div
					v-for="v in clientNotes"
					:key="v.id"
					class="panel-tag mb-3 fs-xs position-relative"
				>
					<p class="mb-0 mt-2">{{ v.value }}</p>
					<span
						class="fs-xs opacity-70 pt-1 pr-2 position-absolute pos-right pos-top color-success-700"
					>
						{{ v.author.name }}
					</span>
				</div>
			</div>
			<!--            <div class="mt-2">-->
			<!--                <div class="panel-tag mb-3 fs-xs position-relative"><p class="mb-0 mt-2">Новый коммент!</p> <span-->
			<!--                    class="fs-xs opacity-70 pt-1 pr-2 position-absolute pos-right pos-top color-success-700">-->
			<!--                        Alexander Yurchenko-->
			<!--                    </span></div>-->
			<!--                <div class="panel-tag mb-3 fs-xs position-relative"><p class="mb-0 mt-2">Test 123</p> <span-->
			<!--                    class="fs-xs opacity-70 pt-1 pr-2 position-absolute pos-right pos-top color-success-700">-->
			<!--                        Alexander Yurchenko-->
			<!--                    </span></div>-->
			<!--            </div>-->
		</div>
		<div class="fs-xs call-widget-footer" style="background-color: #f7f9fa">
			<div class="d-flex">
				<call-widget-btn
					v-for="(callRecord, callIndex) in callRecords"
					:call-record="callRecord"
					:index="callIndex"
					:key="callIndex"
				></call-widget-btn>
				<!--                <div class="call-widget-footer-btn p-2" >-->
				<!--                    <i class="fal fa-phone-alt"></i> AY-->
				<!--                </div>-->
				<!--                <div class="call-widget-footer-btn p-2">-->
				<!--                    <i class="fal fa-phone-alt"></i> AY-->
				<!--                </div>-->
			</div>
		</div>
		<!--        <div ref="callWidgetHeader" class="call-widget&#45;&#45;header">Click here to move</div>-->
	</div>
</template>

<script>
// import {BTabs, BTab} from 'bootstrap-vue'
import callWidgetBtn from '@components/App/CallWidgetBtn';
import ClientTags from '@components/Order/TabOverview/Client/ClientTags';

export default {
	name: 'CallWidget',
	data: () => ({
		timerSeconds: 0,
		// activeRecord: null,
		position: {
			pos1: 0,
			pos2: 0,
			pos3: 0,
			pos4: 0,
		},
	}),
	computed: {
		isWidgetEnabled() {
			return this.$store.state.calls.isWidgetEnabled;
		},
		secondsToTime() {
			const h = Math.trunc(this.timerSeconds / (60 * 60));
			const m = Math.trunc((this.timerSeconds - h * 60 * 60) / 60);
			const s = this.timerSeconds - h * 60 * 60 - m * 60;
			return (
				this.zeroPad(h, 2) +
				':' +
				this.zeroPad(m, 2) +
				':' +
				this.zeroPad(s, 2)
			);
		},
		clientNotes() {
			if (this.currentRecord?.client?.notes) {
				return this.currentRecord?.client?.notes;
			}
			return null;
		},
		clientTags() {
			if (this.currentRecord?.client?.tags) {
				return this.currentRecord?.client?.tags;
			}
			return null;
		},
		clientFullName() {
			if (this.currentRecord?.client) {
				return (
					this.currentRecord.client.name +
					' ' +
					this.currentRecord.client.lname
				);
			} else if (this.currentRecord?.channelContact) {
				return this.currentRecord?.channelContact;
			}
			return '';
		},
		clientOrdersCount() {
			if (this.currentRecord?.client) {
				return this.currentRecord.client.orders_count;
			}
			return null;
		},
		currentRecord() {
			if (this.$store.state.calls.activeRecordIdx !== null)
				return this.callRecords[
					this.$store.state.calls.activeRecordIdx
				];
			return null;
		},
		callDirection() {
			// if (this.currentCallPbxId) {
			if (this.currentCallPbxId.startsWith('in_')) return 'Inbound';
			if (this.currentCallPbxId.startsWith('out_')) return 'Outbound';
			// }
			return null;
		},
		currentCallPbxId() {
			if (this.currentRecord?.item?.pbx_call_id) {
				return this.currentRecord.item.pbx_call_id.toString();
			}
			return '';
		},
		callDirectionText() {
			if (this.currentRecord) {
				if (this.callDirection == 'Inbound')
					return this.callDirection + ' call from';
				if (this.callDirection == 'Outbound')
					return this.callDirection + ' call to';
			}
			return '';
		},
		internalExtension() {
			return this.currentRecord?.item?.internal;
		},
		clientName() {
			return this.currentRecord?.client
				? this.currentRecord.client.name +
						' ' +
						this.currentRecord.client.lname
				: 'phone';
		},
		timerStatus() {
			if (
				this.currentRecord?.item?.event == 'NOTIFY_START' ||
				this.currentRecord?.item?.event == 'NOTIFY_OUT_START'
			) {
				return 'Calling...';
			}
			return this.secondsToTime;
		},
		currentRecordEvent() {
			if (this.currentRecord?.item) return this.currentRecord.item.event;
			return null;
		},
		currentRecordUID() {
			if (this.currentRecord) return this.currentRecord.uid;
			return null;
		},
		callRecords() {
			return this.$store.state.calls.records;
		},
		employeeInternal() {
			return this.$store.state.calls.employeeInternal;
		},
	},
	async mounted() {
		try {
			await this.$store.dispatch('calls/fetchActiveCalls');
		} catch (e) {
			App.Forms.showAlert(
				'error',
				'fetchActiveCalls error',
				e && e.error ? e.error : ''
			);
		}
		if (this.currentRecord) this.initCallTimer();
		if (window.Echo)
			window.Echo.channel(
				`calls.${this.$store.state.calls.divisionID}`
			).listen('.communications.event', (e) => {
				this.processEvent(e.data);
			});

		this.$nextTick(() => {
			this.$refs.callWidgetHeader.onmousedown = this.dragMouseDown;
		});
	},
	methods: {
		processEvent(callData) {
			// console.log(callData);
			// console.log(+callData.item.internal == +this.employeeInternal);
			if (callData.item.event == 'NOTIFY_START') {
				// add new record
				this.$store.commit('calls/addRecord', callData);
			} else if (
				callData.item.event == 'NOTIFY_OUT_END' ||
				callData.item.event == 'NOTIFY_END'
			) {
				// remove records
				this.$store.commit('calls/removeRecord', callData);
			} else if (callData.item.event == 'NOTIFY_OUT_START') {
				// show only own internal number
				if (
					this.employeeInternal &&
					+callData.item.internal == +this.employeeInternal
				) {
					this.$store.commit('calls/addRecord', callData);
				}
			} else if (callData.item.event == 'NOTIFY_ANSWER') {
				// replace or add
				if (
					this.employeeInternal &&
					+callData.item.internal == +this.employeeInternal
				) {
					this.$store.commit('calls/addOrReplaceRecord', callData);
				}
			}
		},
		// dragElement() {
		//     // var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
		//     if ($(this.$refs.callWidgetHeader)) {
		//         // if present, the header is where you move the DIV from:
		//         this.$refs.callWidgetHeader.onmousedown = this.dragMouseDown;
		//     }
		//     // else {
		//     //     // otherwise, move the DIV from anywhere inside the DIV:
		//     //     elmnt.onmousedown = this.dragMouseDown;
		//     // }
		//
		// },

		dragMouseDown(e) {
			e = e || window.event;
			e.preventDefault();
			// get the mouse cursor position at startup:
			this.position.pos3 = e.clientX;
			this.position.pos4 = e.clientY;
			document.onmouseup = this.closeDragElement;
			// call a function whenever the cursor moves:
			document.onmousemove = this.elementDrag;
		},

		elementDrag(e) {
			e = e || window.event;
			e.preventDefault();
			// calculate the new cursor position:
			this.position.pos1 = this.position.pos3 - e.clientX;
			this.position.pos2 = this.position.pos4 - e.clientY;
			this.position.pos3 = e.clientX;
			this.position.pos4 = e.clientY;
			// set the element's new position:
			this.$refs.callWidget.style.top =
				this.$refs.callWidget.offsetTop - this.position.pos2 + 'px';
			this.$refs.callWidget.style.left =
				this.$refs.callWidget.offsetLeft - this.position.pos1 + 'px';
		},
		closeDragElement() {
			// stop moving when mouse button is released:
			document.onmouseup = null;
			document.onmousemove = null;
		},
		zeroPad(num, places) {
			return String(num).padStart(places, '0');
		},
		initCallTimer() {
			if (this.currentRecord.item.event == 'NOTIFY_ANSWER') {
				this.timerSeconds = moment()
					.utc()
					.diff(moment.unix(this.currentRecord.timestamp), 'seconds');
				setInterval(() => {
					this.timerSeconds += 1;
				}, 1000);
			}
		},
	},
	watch: {
		currentRecordEvent(newValue, oldValue) {
			if (newValue == 'NOTIFY_ANSWER') {
				this.initCallTimer();
			}
		},
	},
	components: {
		callWidgetBtn,
		ClientTags,
	},
};
</script>

<style lang="scss" scoped>
.call-widget {
	width: 250px;
	z-index: 9;
	background-color: #fff;
	//border: 1px solid #d3d3d3;
	//text-align: center;
	-webkit-box-shadow: 0 0 15px 1px rgb(90 80 105 / 20%);
	box-shadow: 0 0 15px 1px rgb(90 80 105 / 20%);

	&--header {
		//padding: 10px;
		cursor: move;
		z-index: 10;
		//background-color: #2196F3;
		//color: #fff;
	}

	.call-widget-footer-btn {
		cursor: pointer;
		border-right: 1px solid rgba(0, 0, 0, 0.15);

		&.active,
		&:hover {
			background-color: #6c757d;
			color: #fff;
		}
	}

	//.call-widget-footer-btn:hover,   {
	//    background-color: #6c757d;
	//    color: #fff;
	//}
}
</style>

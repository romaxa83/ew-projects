<template>
    <ul class="activity-timeline-lg mx-4">
        <template v-for="(record, key) in sortedFlow">
            <li v-if="record.divider">
                <div class="d-flex justify-content-center">
                    <div
                        class="bg-white shadow-3 px-3 py-1 rounded-pill fs-xs fw-500"
                    >
                        {{ record.divider }}
                    </div>
                </div>
            </li>

            <component
                :is="detectComponent(record)"
                :key="key"
                :index="key"
                :record="record"
                :datetime="localDT(record.timestamp)"
                interface="flow"
                @assign="assignModal"
            />
        </template>
    </ul>
</template>

<script>
import formatDateTime from '@/filters/formatDateTime.filter';
import attributeChange
    from '@components/Order/TabOverview/CommunicationPanel/History/AttributeChange.vue';
import Call
    from '@components/Order/TabOverview/CommunicationPanel/History/Call.vue';
import ConversationMark
    from '@components/Order/TabOverview/CommunicationPanel/History/ConversationMark.vue';
import Email
    from '@components/Order/TabOverview/CommunicationPanel/History/Email.vue';
import EmailActivity
    from '@components/Order/TabOverview/CommunicationPanel/History/EmailActivity.vue';
import Empty
    from '@components/Order/TabOverview/CommunicationPanel/History/Empty.vue'; // import InventorySave from '@components/Order/TabOverview/CommunicationPanel/History/InventorySave.vue';
import InventoryActivity
    from '@components/Order/TabOverview/CommunicationPanel/History/InventoryActivity.vue';
import OrderCreateEvent
    from '@components/Order/TabOverview/CommunicationPanel/History/OrderCreateEvent.vue';
import RingostatCall
    from '@components/Order/TabOverview/CommunicationPanel/History/RingostatCall.vue';
import Sms
    from '@components/Order/TabOverview/CommunicationPanel/History/Sms.vue';
import TwilioSms
    from '@components/Order/TabOverview/CommunicationPanel/History/TwilioSms.vue';
import Concierge
    from '@components/Order/TabOverview/CommunicationPanel/History/Concierge.vue';

export default {
    name: 'CommunicationHistoryFlow',
    data: () => ({
        prevRecordMoment: false,
    }),
    computed: {
        sortedFlow() {
            return this.$store.getters['communicationsFlow/sortedFlowRecords'];
        },
        timezone() {
            return this.$store.state.communicationsFlow.timezone;
        },
    },
    updated() {
        this.$emit('updated');
    },
    methods: {
        assignModal() {
            this.$emit('assign');
        },
        localDT(timestampUTC) {
            return formatDateTime(timestampUTC, this.timezone);
        },

        detectComponent(record) {
            if (record.type === 'InventoryActivity') {
                return InventoryActivity;
            } else if (record.timestamp)
                this.prevRecordMoment = moment.unix(record.timestamp);
            if (record.type == 'CallsEvents') {
                return Call;
            } else if (record.type == 'TwilioSms') {
                return TwilioSms;
            } else if (record.type == 'SmsEvents') {
                return Sms;
            } else if (record.type == 'Message') {
                return Email;
            } else if (record.type == 'Order') {
                return OrderCreateEvent;
            } else if (record.type == 'ConversationMark') {
                return ConversationMark;
            } else if (
                record.type == 'Activity' &&
                record.item.type == 'email'
            ) {
                // } else if (record.type == 'Activity') {
                // 	return InventorySave;
                return EmailActivity;
            } else if (
                record.type == 'Activity' &&
                record.item.type != 'email'
            ) {
                return attributeChange;
            } else if (record.type == 'EventAfterCall') {
                return RingostatCall;
            } else if (record.type === 'CallEvent') {
                return Concierge;
            }
            console.log(`Unhandled record "${record.type}"`, record);
            return Empty;
        },
    },
};
</script>

<style scoped>
.activity-timeline-lg li {
    margin: 20px 0;
}
</style>

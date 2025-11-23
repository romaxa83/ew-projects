<template>
    <div
        class="panel-content panel-interface-history bg-faded"
        ref="scrollarea"
        id="communications-scrollarea"
    >
        <div
            class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
            :class="{ 'd-none': !loading }"
        >
            <div class="d-flex justify-content-center">
                <div
                    class="spinner-border text-info position-absolute"
                    style="top: 30%"
                    role="status"
                >
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <!--    <div class="panel-content panel-interface-history bg-faded" ref="scrollarea">-->
        <ul class="activity-timeline-lg">
            <infinite-loading
                :identifier="infiniteId"
                direction="top"
                @infinite="infiniteCommunicationHistoryHandler"
            >
                <!--                <div slot="spinner"></div>-->
                <div slot="no-more">
                    <h6 class="text-muted mt-3">no more history</h6>
                </div>
                <div slot="no-results">
                    <h6 class="text-muted mt-3">All history loaded</h6>
                </div>
            </infinite-loading>
            <template v-for="(record, key) in sortedHistoryRecords">
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
                    :record="record"
                    :datetime="localDT(record.timestamp)"
                    :v2="v2"
                    interface="order"
                />
            </template>
        </ul>
    </div>
</template>

<script>
import formatDateTime from '@/filters/formatDateTime.filter';
import attributeChange
    from '@components/Order/TabOverview/CommunicationPanel/History/AttributeChange';
import Call
    from '@components/Order/TabOverview/CommunicationPanel/History/Call';
import ConversationMark
    from '@components/Order/TabOverview/CommunicationPanel/History/ConversationMark';
import Email
    from '@components/Order/TabOverview/CommunicationPanel/History/Email';
import EmailActivity
    from '@components/Order/TabOverview/CommunicationPanel/History/EmailActivity';
import Empty
    from '@components/Order/TabOverview/CommunicationPanel/History/Empty';
import InventoryActivity
    from '@components/Order/TabOverview/CommunicationPanel/History/InventoryActivity.vue';
import Note
    from '@components/Order/TabOverview/CommunicationPanel/History/Note';
import RingostatCall
    from '@components/Order/TabOverview/CommunicationPanel/History/RingostatCall.vue';
import Sms from '@components/Order/TabOverview/CommunicationPanel/History/Sms';
import Task
    from '@components/Order/TabOverview/CommunicationPanel/History/Task';
import TwilioSms
    from '@components/Order/TabOverview/CommunicationPanel/History/TwilioSms';
import Concierge
    from '@components/Order/TabOverview/CommunicationPanel/History/Concierge.vue';
import InfiniteLoading from 'vue-infinite-loading';

import {mapGetters} from 'vuex';

export default {
    name: 'ScrollContainer',
    data: () => ({
        scrollDown: false,
        prevRecordMoment: false,
        records: [],
        untill: null,
        loading: true,
        flowPage: 1,
        infiniteId: +new Date(),
    }),
    props: {
        v2: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        timezone() {
            return this.$store.state.tasksCalendar.timezone;
        },
        allLoaded() {
            // TODO: Remove prev version after migration
            return this.v2
                ? this.$store.state.order.communicationPanelV2.allLoaded
                : this.$store.state.order.communicationPanel.allLoaded;
        },
        disableLoading() {
            return this.allLoaded || this.loading;
        },
        sortedHistoryRecords() {
            return this.descRecords.slice().map((elm, i, arr) => {
                elm.divider =
                    i > 1
                        ? this.getRecordsDivider(
                            elm.timestamp,
                            arr[i - 1].timestamp
                        )
                        : false;
                return elm;
            });
        },
        descRecords() {
            return this.v2 ? this.byDescRecordsV2 : this.byDescRecords;
        },
        ...mapGetters({
            byDescRecords: 'order/getCommunicationRecordsByDesc',
            byDescRecordsV2: 'order/getCommunicationRecordsByDescV2',
            orderID: 'getOrderId',
        }),
    },
    mounted() {
        if (window.Echo) {
            window.Echo.channel(`order.${this.orderID}.communications`).listen(
                '.communications.event',
                (e) => {
                    // TODO: Remove prev version after migration
                    const type = this.v2
                        ? 'order/pushCommunicationRecordV2'
                        : 'order/pushCommunicationRecord';
                    this.$store.commit(type, e.data);
                    this.$nextTick(() => {
                        this.jumpToLatest();
                    });
                }
            );
        }
    },
    methods: {
        jumpToLatest() {
            if (this.$refs?.scrollarea)
                this.$refs.scrollarea.scrollTop =
                    this.$refs.scrollarea.scrollHeight;
        },
        infiniteCommunicationHistoryHandler($state) {
            // TODO: Remove prev version after migration
            const type = this.v2
                ? 'order/fetchCommunicationRecordsV2'
                : 'order/fetchCommunicationRecords';
            this.$store.dispatch(type).then(() => {
                if (!this.allLoaded) {
                    $state.loaded();
                    this.flowPage += 1;
                } else {
                    $state.complete();

                    if (this.flowPage == 1) {
                        this.jumpToLatest();
                    }
                }
                this.loading = false;
            });
        },
        loadHistory(isInit = false) {
            this.loading = true;
            const prevHeight = this.$refs.scrollarea.scrollHeight;

            // TODO: Remove prev version after migration
            const type = this.v2
                ? 'order/fetchCommunicationRecordsV2'
                : 'order/fetchCommunicationRecords';
            return this.$store.dispatch(type).then(() => {
                this.$nextTick(() => {
                    if (isInit) {
                        console.log(this.$refs.scrollarea.scrollHeight);
                        this.$refs.scrollarea.scrollTop =
                            this.$refs.scrollarea.scrollHeight;
                    } else {
                        this.$refs.scrollarea.scrollTop =
                            this.$refs.scrollarea.scrollHeight - prevHeight;
                    }
                    this.loading = false;
                });
            });
        },
        historyScrollDown() {
            if (this.scrollDown) {
                this.$refs.scrollarea.scrollTop =
                    this.$refs.scrollarea.scrollHeight;
                this.scrollDown = false;
            }
        },
        getRecordsDivider(recordTimestamp, prevTimestamp) {
            const recordMoment = moment.unix(recordTimestamp).local();
            if (recordMoment.isAfter(moment())) return 'Planned';
            if (
                !recordMoment.isSame(
                    moment.unix(prevTimestamp).local(),
                    'month'
                )
            )
                return recordMoment.format('MMMM YYYY');
            return false;
        },
        localDT(timestampUTC) {
            return formatDateTime(timestampUTC, this.timezone);
        },
        detectComponent(record) {
            if (record.type === 'InventoryActivity') {
                return InventoryActivity;
            } else if (record.timestamp)
                this.prevRecordMoment = moment.unix(record.timestamp).local();
            if (record.type == 'Notes') {
                return Note;
            } else if (record.type == 'Task') {
                return Task;
            } else if (record.type == 'CallsEvents') {
                return Call;
            } else if (record.type == 'SmsEvents') {
                return Sms;
            } else if (record.type == 'TwilioSms') {
                return TwilioSms;
            } else if (record.type == 'Message') {
                return Email;
            } else if (
                record.type == 'Activity' &&
                record.item.type == 'email'
            ) {
                return EmailActivity;
            } else if (
                record.type == 'Activity' &&
                record.item.type != 'email'
            ) {
                return attributeChange;
            } else if (record.type == 'ConversationMark') {
                return ConversationMark;
            } else if (record.type == 'EventAfterCall') {
                return RingostatCall;
            } else if (record.type === 'CallEvent') {
                return Concierge;
            }

            console.log(`Unhandled record "${record.type}"`, record);
            return Empty;
        },
    },
    components: {
        InfiniteLoading,
    },
};
</script>

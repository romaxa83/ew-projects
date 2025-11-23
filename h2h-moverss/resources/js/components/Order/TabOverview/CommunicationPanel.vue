<template>
    <div class="panel">
        <div class="panel-hdr panel-communications color-warning-800">
            <h2 class="mr-4">
                Communication Panel
<!--                <span class="fw-300">-->
<!--					<i v-if="!v2">OLD</i>-->
<!--					<i v-if="v2" class="red">NEW</i>-->
<!--				</span>-->
            </h2>
            <call-info
                :data="callInfo"
                class="mr-4"
                v-if="v2 && !!callInfo"
            />
            <div class="panel-toolbar">
                <order-status-dropdown
                    v-if="status && orderStatusId"
                    :order_id="orderID"
                    :orderStatusId="orderStatusId"
                    :statuses="status"
                    size="md"
                    interface="communicationPanel"
                    :loading="$store.state.order.status.loading"
                />
                <button
                    @click="closeOrder"
                    class="btn btn-icon btn-secondary btn-danger ml-1 shadow-0 waves-effect waves-themed"
                    data-toggle="tooltip"
                    title="Close order"
                >
                    <i class="fas fa-lock"></i>
                </button>
                <div
                    class="px-3 text-black-50 cursor-pointer"
                    @click="toggleCollapse"
                >
                    <i class="fal fa-3x" :class="collapseAngleClass"></i>
                </div>
            </div>
        </div>
        <div
            v-if="orderID && !isCollapsed"
            class="panel-container show"
            :class="mode.panelInterfaceClass"
        >
            <div
                class="panel-interface-container"
                :class="{ 'grow-bottom-2': mode.current == 'email' }"
            >
                <scroll-container v-if="tasksEnvironment" :v2="v2"/>
                <div ref="inputArea" class="panel-interface-input">
                    <input-interface-task
                        v-if="mode.current == 'task' && tasksEnvironment"
                        :v2="v2"
                        :mode="mode"
                    />
                    <input-interface-note
                        v-if="mode.current == 'note'"
                        :v2="v2"
                        :mode="mode"
                    />
                    <input-interface-sms
                        v-if="hasPhones && mode.current == 'sms'"
                        :mode="mode"
                        :phones="clientPhones"
                        :can-sand-media="canSandMedia"
                    />
                    <input-interface-email
                        v-if="hasEmails && mode.current == 'email'"
                        @sent="mode.current = 'task'"
                        :v2="v2"
                        :mode="mode"
                        :emails="clientEmails"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {axiosPromise} from '@/helpers/axiosPromise';
import DemoHistory
    from '@components/Order/TabOverview/CommunicationPanel/DemoHistory';
import ScrollContainer
    from '@components/Order/TabOverview/CommunicationPanel/History/ScrollContainer';
import InputInterfaceEmail
    from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceEmail';
import InputInterfaceNote
    from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceNote';
import InputInterfaceSms
    from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceSms';
import InputInterfaceTask
    from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceTask';
import orderStatusDropdown
    from '@components/Order/TabOverview/OrderStatusDropdown';
import CallInfo from '@components/App/CallInfo.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import {mapGetters} from 'vuex';

export default {
    name: 'CommunicationPanel',
    data: () => ({
        canSandMedia: true,
        closing_statuses: window.closing_statuses,
        closing_statuses_with_groups: window.closing_statuses_with_groups,
        history: {
            scrollDown: false,
            prevRecordMoment: false,
            records: [],
            untill: null,
            loading: true,
        },
        mode: {
            panelInterfaceClass: 'panel-interface-default',
            current: 'note',
            list: {
                task: {
                    title: 'Task',
                },
                note: {
                    title: 'Note',
                },
                sms: {
                    title: 'SMS',
                },
                // email: {
                //     title: 'Email',
                //     config: {
                //         historyY: '200px'
                //     }
                // },
            },
        },
    }),
    props: {
        v2: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        clientPhones() {
            if (this.hasPhones) return this.client.phones;
            return [];
        },
        clientEmails() {
            if (this.hasEmails) return this.client.emails;
            return [];
        },
        tasksEnvironment() {
            return this.$store.state.tasksCalendar.env;
        },
        firstStage() {
            if (
                this.session?.order?.status_id &&
                this.session?.order?.status_id == 1
            )
                return true;
            return null;
        },
        isCollapsed() {
            if (this.$store.state.order.forcePanelInterface) {
                if (this.$store.state.order.forcePanelInterface == 'collapsed')
                    return true;
                return false;
            }
            if (this.firstStage) return true;
            return false;
        },
        collapseAngleClass() {
            if (this.isCollapsed) return 'fa-angle-down';
            return 'fa-angle-up';
        },
        orderStatusId() {
            return this.session?.order?.status_id;
        },
        historyLoading() {
            return this.history.loading;
        },
        currentMode() {
            return this.mode.current;
        },
        hasPhones() {
            if (this.client) return this.client?.phones.length > 0;
            return null;
        },
        hasEmails() {
            if (this.client) return this.client?.emails.length > 0;
            return null;
        },
        callInfo() {
            return this.v2
                ? this.$store.state.order.communicationPanelV2.callInfo
                : null;
        },
        ...mapGetters({
            tasksIsInitialized: 'appTasks/isInitialized',
            orderID: 'getOrderId',
            client: 'clients/record',
            session: 'getSession',
            status: 'order/status',
        }),
    },
    async mounted() {
        this.$store.dispatch('tasksCalendar/fetchEnvironment');
        await (() => this.$store.state.loading)();
    },
    methods: {
        toggleCollapse() {
            let newValue = '';
            if (this.isCollapsed) newValue = 'expanded';
            else newValue = 'collapsed';
            this.$store
                .dispatch('order/setOrderCommunicationPanelPreset', {
                    orderID: this.orderID,
                    presets: {communicationPanelView: newValue},
                })
                .then(() => {
                    this.$store.commit(
                        'order/updateForcePanelInterface',
                        newValue
                    );
                });
        },
        async closeOrder() {
            const {value: closing_reason_id} = await Swal.fire({
                icon: 'question',
                title: 'Close this order?',
                input: 'select',
                inputOptions: this.closing_statuses_with_groups,
                inputPlaceholder: 'Choose a reason',
                showCancelButton: true,
                inputValidator: (value) => {
                    return new Promise((resolve) => {
                        if (parseInt(value) > 0) {
                            resolve();
                        } else {
                            resolve('You must choose a reason for closing');
                        }
                    });
                },
            });

            if (closing_reason_id) {
                axiosPromise(
                    axios.post(
                        `/orders/${this.orderID}/order/set-status-closed`,
                        {
                            order_id: this.orderID,
                            closing_reason_id,
                        }
                    )
                )
                    .then((data) => {
                        App.Forms.showAlert('success', data.msg);
                        // FIXME отрисовать налету причину
                        setTimeout(() => location.reload(), 800);
                    })
                    .catch((error) => {
                        App.Forms.simpleErrors(error);
                    });
            }
        },
        // TODO: remove if unused
        historyScrollDown() {
            if (this.history.scrollDown) {
                this.$refs.scrollarea.scrollTop =
                    this.$refs.scrollarea.scrollHeight;
                this.history.loading = false;
            }
        },
        localDT(timestampUTC) {
            const localMoment = moment.unix(timestampUTC).local();
            const today = moment().startOf('day');
            const yesterday = moment().subtract(1, 'days').startOf('day');
            const tomorrow = moment().add(1, 'days').startOf('day');
            let dtFormat = 'll';
            if (localMoment.isSame(today, 'year')) {
                dtFormat = 'MMM D [at]';
                if (localMoment.isSame(today, 'day')) {
                    dtFormat = '[Today]';
                } else if (localMoment.isSame(yesterday, 'day')) {
                    dtFormat = '[Yesterday]';
                } else if (localMoment.isSame(tomorrow, 'day')) {
                    dtFormat = '[Tomorrow]';
                }
            }
            return moment
                .unix(timestampUTC)
                .local()
                .format(dtFormat + ' h:mm a');
        },
        loadMore() {
            if (this.loading) return;
            console.log('LoadMore');
        },
        // TODO: remove if unused
        loadHistory() {
            this.history.loading = true;
            if (!this.history.untill) this.history.scrollDown = true;
            else this.history.scrollDown = false;
            axios
                .post('/orders/communicationsPanelHistory', {
                    orderID: this.orderID,
                    historyTill: this.history.untill,
                })
                .then(({data}) => {
                    this.history.records = [
                        ...this.history.records,
                        ...data.data.records,
                    ];
                    this.history.untill = data.data.recordsTill;
                    // this.autoScrollDown();
                    // this.historyLoading = false;
                });
        },
    },
    components: {
        ScrollContainer,
        orderStatusDropdown,
        InputInterfaceSms,
        InputInterfaceTask,
        InputInterfaceNote,
        InputInterfaceEmail,
        DemoHistory,
        CallInfo,
    },
    watch: {
        hasEmails(newVal) {
            if (newVal) {
                this.$set(this.mode.list, 'email', {
                    title: 'Email',
                    config: {historyY: '200px'},
                });
            }
        },
    },
};
</script>

<style lang="scss">
.panel-communications {
    justify-content: space-between;
    flex-wrap: wrap;

    h2 {
        flex: none;
    }
}
</style>

import {axiosPromise} from "@/helpers/axiosPromise";
import Vue from 'vue'

const state = () => ({
    groups: {},
    statuses: {},
    statusRoutes: {},
    managers: [],
    divisions: [],
    // orderRecords: null,
    // countGroupRecords: {}
    timezone: null,
    orders: {},
    // additional: null,
    // env: null,
    filters: {
        divisions: [2],
        manager: null,
        orderByCreated: 'desc'
    },
    minmaxID: {},
    totalOrders: 0,
    totalLoaded: 0
    // recordsToday: [],
    // recordsTom: [],
    // week: [],
})

// getters

const getters = {}

// actions
const actions = {
    fetchSettings({commit}) {
        return axiosPromise(axios.post('/orders/pipeline/settings'))
            .then((data) => {
                // commit('setSettings', data.records);
                commit('setStatuses', data.statuses);
                commit('setGroups', data.groups);
                commit('setTimezone', data.timezone);
                commit('setManagers', data.managers);
                commit('setDivisions', data.divisions);
            })
    },
    fetchOrders({commit, state}, payload) {
        if (payload && payload.reload)
            commit("resetLoaded");
        return axiosPromise(axios.post('/orders/pipeline/records', {filters: state.filters, loaded: state.minmaxID}))
            .then((data) => {
                commit('setTotalCount', data.total);
                commit('setRecords', data.records);
                // commit('setStatuses', Object.values(data.status.records));
            })
    },
}

// mutations
const mutations = {
    setDivisions(state, payload) {
        state.divisions = payload;
    },
    resetLoaded(state) {
        state.minmaxID = {};
        state.orders = {};
        state.totalOrders = 0;
        state.totalLoaded = 0;
    },
    setManagers(state, payload) {
        state.managers = payload;
    },
    updateFilterManager(state, payload) {
        state.filters.manager = payload;
    },
    updateFilterDivision(state, payload) {
        state.filters.divisions = payload;
    },
    updateFilterOrderByCreated(state, payload) {
        state.filters.orderByCreated = payload;
    },
    updateOrderStatus(state, {orderID, oldStatusID, newStatusID}) {
        const oldGroupID = state.statuses[oldStatusID].group_id;
        const newGroupID = state.statuses[newStatusID].group_id;
        // find in old records
        const index = state.orders[oldGroupID].records.findIndex(item => {
            return (orderID === item.id)
        })
        if (index) {
            const record = state.orders[oldGroupID].records[index];
            record.status_id = newStatusID;
            state.orders[newGroupID].records.push(record)
            state.orders[oldGroupID].records.splice(index, 1)
            //state.minmaxID[groupData.groupID].max = Math.max(state.minmaxID[groupData.groupID].max, max)
            // state.records.splice(index, 1, record)
        }
    },
    setTotalCount(state, payload) {
        // clear total
        state.totalOrders = 0;
        for (const k of Object.keys(state.groups)) {
            state.groups[k].ordersCount = 0;
        }
        for (const k of Object.keys(payload)) {
            state.totalOrders += payload[k].ordersCount;
            if (state.groups[k])
                state.groups[k].ordersCount = payload[k].ordersCount;
        }

    },
    setRecords(state, payload) {

        for (const groupData of payload) {
            if (!state.orders[groupData.groupID]) {
                Vue.set(state.orders, groupData.groupID, {records: groupData.records})
                Vue.set(state.minmaxID, groupData.groupID, {min: +Infinity, max: -Infinity})
            } else {
                state.orders[groupData.groupID].records = [...state.orders[groupData.groupID].records, ...groupData.records];
            }
            //state.groups[groupData.groupID].ordersCount =
            if (groupData.records.length) {
                const max = Math.max.apply(Math, groupData.records.map(v => v.id));
                const min = Math.min.apply(Math, groupData.records.map(v => v.id));
                state.minmaxID[groupData.groupID].max = Math.max(state.minmaxID[groupData.groupID].max, max)
                state.minmaxID[groupData.groupID].min = Math.min(state.minmaxID[groupData.groupID].min, min)
            }
            //console.log(state.groups[groupData.groupID]);
            state.totalLoaded += groupData.records.length;
            state.groups[groupData.groupID].loaded += groupData.records.length;
        }
    },
    setTimezone(state, payload) {
        state.timezone = payload;
    },
    setGroups(state, payload) {
        for (const group of Object.values(payload))
            state.totalOrders += group.ordersCount;
        state.groups = payload;
    },
    setStatuses(state, payload) {
        state.statuses = payload;
        let statusRoutes = {};
        for (const S of Object.values(payload)) {
            statusRoutes[S.id] = S.routes;
        }
        state.statusRoutes = statusRoutes;
    }

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

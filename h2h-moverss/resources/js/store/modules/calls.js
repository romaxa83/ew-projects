import {axiosPromise} from "@/helpers/axiosPromise";
import {AxiosHelper} from "@/helpers/axiosHelper";
// state
const state = () => ({
    records: [],
    activeRecord: null,
    activeRecordIdx: null,
    divisionID: null,
    employeeInternal: null,
    isWidgetEnabled: false
})

// getters
const getters = {}

// actions
const actions = {
    fetchActiveCalls({commit}) {
        return axiosPromise(axios.get('/pbx/activeCalls'))
            .then((data) => {
                commit('setRecords', data.records);
                commit('setSettings', data.settings);
                if (data.records.length > 0)
                    commit('setActiveRecordIdx', 0);
                // commit('setFlowRecords', data.records);
            })
    }

}

// mutations
const mutations = {
    toggleCallWidget(state) {
        state.isWidgetEnabled = !state.isWidgetEnabled;
    },
    setRecords(state, records) {
        state.records = records
        // if(records.length > 0)
    },
    addRecord(state, newCall) {
        state.records.push(newCall)
        if (state.activeRecordIdx === null)
            state.activeRecordIdx = 0
    },
    removeRecord(state, newCall) {
        const recordIdx = state.records.find((call) => call.item.pbx_call_id === newCall.item.pbx_call_id);
        if (recordIdx)
            state.records.splice(recordIdx, 1)
        if (state.activeRecordIdx == recordIdx) {
            if (state.records.length > 0)
                state.activeRecordIdx = 0;
            else
                state.activeRecordIdx = null
        }

        //state.records.push(newCall)
    },
    addOrReplaceRecord(state, newCall) {
        const recordIdx = state.records.find((call) => call.item.pbx_call_id === newCall.item.pbx_call_id);
        if (recordIdx)
            state.records.splice(recordIdx, 1, newCall)
        else
            state.records.push(newCall)
        if (state.activeRecordIdx === null)
            state.activeRecordIdx = 0
    },
    setActiveRecord(state, record) {
        state.activeRecord = record
    },
    setActiveRecordIdx(state, payload) {
        state.activeRecordIdx = payload
    },
    setSettings(state, payload) {
        state.divisionID = payload.divisionID
        state.employeeInternal = payload.internal
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

import {axiosPromise} from "@/helpers/axiosPromise";
import {AxiosHelper} from "@/helpers/axiosHelper"
import axios from "axios";

const state = () => ({
    records: [],
    timezone: null,
    whoami: null,
    additional: null,
    env: null,
    filter: {}
    // recordsToday: [],
    // recordsTom: [],
    // week: [],
})

// getters

const getters = {
    sortedRecords: (state => state.records
        .slice()
        .sort((a, b) => a.timestamp - b.timestamp)),

    activeTypes: state => {
        return state?.additional?.types ? Object.values(state.additional.types)
            .sort((a, b) => a.sort - b.sort)
            .filter(item => item.active) : []
    },
    activeUsers: state => {
        return state?.additional?.users ? Object.values(state.additional.users)
            .sort((a, b) => a.name.localeCompare(b.name))
            .filter(item => item.active) : []
    },

    tasksToday(state, getters) {
        const startTS = moment().tz(state.timezone).utc().format('X');
        const endTS = moment().tz(state.timezone).endOf('day').utc().format('X');
        return getters.sortedRecords
            .filter((record) => {
                if (record.timestamp > startTS && record.timestamp <= endTS)
                    return true;
                return false;
            })
    },

    tasksTommorow(state, getters) {
        const startTS = moment().tz(state.timezone).add(1, 'day').startOf('day').utc().format('X');
        const endTS = moment().tz(state.timezone).add(1, 'day').endOf('day').utc().format('X');
        return getters.sortedRecords
            .filter(record => {
                if (record.timestamp > startTS && record.timestamp <= endTS)
                    return true;
                return false;
            })
    },

    tasksWeek(state, getters) {
        // if Sat and Sun return []
        // if(moment().format('e'))
        if (moment().format('e') == 0)
            return [];
        const startTS = moment().tz(state.timezone).add(2, 'day').startOf('day').utc().format('X');
        const endTS = moment().tz(state.timezone).endOf('week').utc().format('X');
        return getters.sortedRecords
            .filter((record) => {
                if (record.timestamp > startTS && record.timestamp <= endTS)
                    return true;
                return false;
            })
    },

    tasksOverdued(state, getters) {
        const nowTS = moment().tz(state.timezone).utc().format('X');
        return getters.sortedRecords
            .filter((record) => {
                if (record.timestamp < nowTS)
                    return true;
                return false;
            })
    },

}

// actions
const actions = {
    fetchTasks({commit}, payload) {
        return axiosPromise(axios.post('/tasks/pipeline', payload))
            .then((data) => {
                commit('setTaskRecords', data.records);
            })
    },
    fetchEnvironment({commit}) {
        return axiosPromise(axios.get('/tasks/environment'))
            .then((data) => {
                commit('setAdditional', data.additional)
                commit('setWhoami', data.whoami)
                commit('setTimezone', data.timezone)
                commit('initEnvonronment')
            })
    },
    removeTask({commit}, payload) {
        return axiosPromise(axios.post('/tasks/removeTask', payload))
    },
    completeTask({commit}, payload) {
        return axiosPromise(axios.post('/tasks/modifyTask', payload))
    },
}

// mutations
const mutations = {
    setTaskRecords(state, payload) {
        state.records = payload
    },
    removeTask(state, taskID) {
        let records = [];
        for (const i in state.records) {
            if (state.records[i].type == 'Task' && state.records[i].item.id == taskID)
                continue;
            records.push(state.records[i]);
        }
        state.records = records;

    },
    updateTask(state, record) {
        const index = state.records.findIndex(item => {
            return +record.item.id === +item.item.id
        })
        if (index >= 0)
            state.records.splice(index, 1, record)
        else {
            console.log('updateTask', record);
            console.log('task index not found :(');
        }
        // // let records = state.records.slice();
        //
        // // const index = state.records.findIndex(item => {
        // //     return (replacementItem.id === item.id)
        // // })
        //
        // for (const i in records) {
        //     if (records[i].item.id == record.item.id) {
        //         records[i] = record;
        //         break;
        //     }
        // }
        // state.records = state.records.filter((r) => );

    },
    pushTaskRecord(state, record) {
        state.records.push(record);
    },
    setTimezone(state, payload) {
        state.timezone = payload
    },
    setWhoami(state, payload) {
        state.whoami = payload
    },
    setAdditional(state, payload) {
        state.additional = payload
    },
    initEnvonronment(state) {
        state.env = true;
    }


}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

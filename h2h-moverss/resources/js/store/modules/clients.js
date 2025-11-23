import {AxiosHelper} from "@/helpers/axiosHelper";

const state = () => ({
    loading: false,
    updating: false,
    client_id: null, // current client
    ids: [],
    records: {},
    types: {},
})

// getters
const getters = {

    clientId: state => state.client_id,
    loading: state => state.loading,
    updating: state => state.updating,
    types: state => state.types,

    record: state => state.records[state.client_id],

}

// actions
const actions = {

    fetchClient({commit, state, getters}, client_id) {
        let index = state.ids.indexOf(client_id);
        if (index !== -1) {
            commit('setClientID', client_id);

            return getters.record;
        }
        commit('setLoadingStatus', true);

        return AxiosHelper({
            url: '/client/profile',
            data: {id: client_id},
        })
            .then(({record, types}) => {
                commit('setClientID', client_id);
                commit('pushClientID', client_id);
                commit('pushRecord', record);
                commit('setTypes', types);
                commit('setLoadingStatus', false);

                return record;
            })
    },

    updateClient({commit, state, getters}, payload) {
        commit('setUpdatingStatus', true);

        return AxiosHelper({
            url: '/client/profile/save',
            data: payload,
        })
            .then(({record, msg}) => {
                commit('pushRecord', record);

                return {record, msg};
            })
            .finally(() => commit('setUpdatingStatus', false))
    }

}

// mutations
const mutations = {

    setLoadingStatus: (state, status) => state.loading = status,
    setUpdatingStatus: (state, status) => state.updating = status,

    setTypes: (state, payload) => state.types = payload,

    setClientID: (state, client_id) => state.client_id = client_id,
    pushClientID: (state, client_id) => state.ids.push(client_id),

    pushRecord: (state, payload) => state.records = {...state.records, [payload.id]: payload},

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

const state = () => ({
    activities: {
        gmail: {
            total: 0,
            ids: [],
            records: [],
        },
        messages: {
            total: 0,
            ids: [],
            records: [],
        },
    },
    calls: {
        total: 0,
        loading: true,
        records: [],
    },
    callsZadarma: {
        total: 0,
        loading: true,
        records: [],
    }
})

// getters
const getters = {

    callRecords: state => state.calls,
    getZadarmaCalls: state => state.callsZadarma,

    records: state => (take = null) => {
        let records = [...state.activities.gmail.records, ...state.activities.messages.records]
            .sort((a, b) => {
                // Сортировка по дате
                return new Date(b.created_at) - new Date(a.created_at);
            });

        if (take)
            records = records.slice(0, take);

        return records;
    },

    total: state => {
        return state.activities.gmail.total + state.activities.messages.total;
    },

}

// actions
const actions = {

    mergeExistsIds({commit, state}, {section, ids}) {
        commit('setMessagesIds', {
            section,
            payload: [
                ...state.activities[section].ids,
                ...ids
            ]
        })
    },

    addRecords: ({commit, state, dispatch}, {section, records, total}) => {
        let exists_ids = state.activities[section].ids,
            ids = [];
        records
            .forEach(function (payload) {
                let index = exists_ids.indexOf(payload.id);
                payload.section = section;
                if (index !== -1) {
                    commit('updateRecord', {
                        section,
                        index,
                        payload,
                    })
                } else {
                    // Push
                    commit('pushRecord', {
                        section,
                        payload,
                    });
                    ids.push(payload.id);
                }
            });

        if (ids) {
            dispatch('mergeExistsIds', {
                section,
                ids
            });
        }

        if (!total) {
            total = state.activities[section].ids.length;
        }

        commit('setTotal', {
            section,
            total,
        });
    },

    setCalls({commit, state}, payload) {
        payload
            .forEach(item => {
                item.opened = false;
            });

        commit('setCalls', {
            records: payload,
            total: payload.length,
        });
    },

    setZadarmaCalls({commit, state}, payload) {
        payload
            .forEach(item => {
                item.opened = false;
                item.links = [];
            });

        commit('setZadarmaCalls', {
            records: payload,
            total: payload.length,
        });
    },


}

// mutations
const mutations = {

    setMessagesIds(state, {section, payload}) {
        state.activities[section].ids = payload;
    },

    setTotal(state, {section, total}) {
        state.activities[section].total = total;
    },

    pushRecord(state, {section, payload}) {
        state.activities[section].records.push(payload);
    },

    updateRecord(state, {section, index, payload}) {
        state.activities[section].records[index] = payload;
    },

    setCalls(state, {total, records}) {
        state.calls.total = total;
        state.calls.records = records;
        state.calls.loading = false;
    },
    setZadarmaCalls(state, {total, records}) {
        state.callsZadarma.total = total;
        state.callsZadarma.records = records;
        state.callsZadarma.loading = false;
    },

    toggleCall(state, index) {
        state.calls.records[index].opened = !state.calls.records[index].opened;
    },
    toggleZadarmaCall(state, index) {
        state.callsZadarma.records[index].opened = !state.callsZadarma.records[index].opened;
    },
    setZadarmaCallRecord(state, payload) {
        state.callsZadarma.records[payload.index].links = payload.links;
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

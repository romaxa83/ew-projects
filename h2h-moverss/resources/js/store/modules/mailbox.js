const state = () => ({
    onPage: 40,
    loading: true,
    accounts: {},
    messages: {
        ids: [], // Храним ID сообщения, для понимания надо обновить или добавить новую
        records: [],
    },
    openedBuffer: {}, // Для писем которые ранее открывались
    lastSync: null, // Дата последней синхронизации CRON
    currentDate: null, // Дата получения данных с бека (надо для получения недавно измененных сообщений)
    meta: {
        inbox: {
            page: 1,
            new: 0,
            total: 0,
            records: [],
        },
        draft: {
            page: 1,
            total: 0,
            records: [],
        },
        sent: {
            page: 1,
            total: 0,
            records: [],
        },
        spam: {
            page: 1,
            total: 0,
            records: [],
        },
        trash: {
            page: 1,
            total: 0,
            records: [],
        }
    }
})

// getters
const getters = {

    isLoaded: state => !state.loading,
    getLastSync: state => state.lastSync,
    getCurrentDate: state => state.currentDate,

    isMultiAccounts: state => Object.keys(state.accounts).length > 1,
    getAccounts: state => state.accounts,

    onPage: state => state.onPage,

    getMessages: state => state.messages.records,

    getOpened: state => state.openedBuffer,

    meta: state => state.meta,

    folderMeta: state => folder => state.meta[folder],

    // cartProducts: (state, getters, rootState) => {
    //     return state.items.map(({ id, quantity }) => {
    //         const product = rootState.products.all.find(product => product.id === id)
    //         return {
    //             title: product.title,
    //             price: product.price,
    //             quantity
    //         }
    //     })
    // },
    //
    // cartTotalPrice: (state, getters) => {
    //     return getters.cartProducts.reduce((total, product) => {
    //         return total + product.price * product.quantity
    //     }, 0)
    // }
}

// actions
const actions = {

    mergeExistsIds({commit, state}, ids) {
        commit('setMessagesIds', [
            ...state.messages.ids,
            ...ids
        ])
    },

    // Раскидываем или обновляем письма по папках
    recordsAdd({commit, state, dispatch}, records) {
        let ids = [];
        records
            .forEach(function (item) {
                item.tags = item.tags.split(',');
                ids.push(item.id);
            });

        commit('setMessagesRecords', records);
        dispatch('mergeExistsIds', ids);
    },

    recordsUpdate({commit, state, dispatch}, records) {
        let ids = state.messages.ids,
            newIds = [];
        records
            .forEach(function (item) {
                item.tags = item.tags.split(',');

                let index = ids.indexOf(item.id);
                if (index !== -1) {
                    commit('updateRecord', {
                        index,
                        payload: item,
                    })
                } else {
                    // Push
                    commit('pushRecord', item);
                    newIds.push(item.id);
                }
            });

        if (newIds) {
            dispatch('mergeExistsIds', newIds);
        }
    },

    metaUpdate({commit, state}, payload) {
        for (let folder in payload) {
            if (state.meta[folder].total !== payload[folder].total) {
                // upd total
                commit('updateTotal', {
                    folder,
                    total: payload[folder].total,
                    total_new: payload[folder].new ?? null,
                });
            }
        }
    },

    pushThreadMsg({commit, state}, {thread_id, messages}) {
        let subject = messages[(messages.length - 1)].subject;

        let payload = {
            messages,
            subject,
        };

        commit('pushThreadMsg', {thread_id, payload});
    },
}

// mutations
const mutations = {

    updateLastSync(state, {lastSync, currentDate}) {
        state.lastSync = lastSync;
        state.currentDate = currentDate;
    },

    updateTotal(state, {folder, total, total_new}) {
        if (total_new) {
            state.meta[folder].total = total;
            state.meta[folder].new = total_new;
        } else
            state.meta[folder].total = total;
    },

    setMessagesRecords(state, records) {
        state.messages.records = records;
    },

    setAccounts(state, payload) {
        state.accounts = payload;
        state.loading = false;
    },

    setMessagesIds(state, payload) {
        state.messages.ids = payload;
    },

    updateRecord(state, {index, payload}) {
        state.messages.records[index] = payload;
    },

    pushRecord(state, payload) {
        state.messages.records.push(payload);
    },

    pushThreadMsg(state, {thread_id, payload}) {
        state.openedBuffer[thread_id] = payload;
    },

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

import {AxiosHelper} from "@/helpers/axiosHelper";

const state = () => ({
    lastWeekDay: 7, // Day - End Of Week
    is_initialized: false,
    loading: false,
    records: [],
    ids: [],
    whoami: {},
    additional_initialized: false,
    additional: {
        statuses: {},
        types: {},
        users: {},
    },
    filters: {
        status: 1, // In work
        division_id: null,
        user: null,
        dateFrom: null,
        dateTo: null,
    },
})

// getters
const getters = {

    loading: state => state.loading,

    isInitialized: state => state.is_initialized,
    isAdditionalInitialized: state => state.additional_initialized,

    users: state => state.additional.users,
    statuses: state => state.additional.statuses,
    types: state => state.additional.types,

    filters: state => state.filters,

    whoami: state => state.whoami,

    activeTypes: state =>
        Object.values(state.additional.types)
            .sort((a, b) => a.sort - b.sort)
            .filter(item => item.active),

    activeUsers: state =>
        Object.values(state.additional.users)
            .sort((a, b) => a.name.localeCompare(b.name))
            .filter(item => item.active),

    records: state => state.records
        .slice()
        .filter(item => item.status_id === state.filters.status)
        .filter(item => {
            if (state.filters.division_id) {
                let b_id = item.miscs.relation?.branch_id ?? null;
                if (state.filters.division_id === b_id)
                    return true;
            } else
                return true;
        })
        .filter(item => state.filters.user ? (state.filters.user === item.user_id || state.filters.user === item.executor_id) : true)
        .filter(item => item.dueDate.isBetween(state.filters.dateFrom, state.filters.dateTo))
        .sort((a, b) => a.dueDate.valueOf() - b.dueDate.valueOf()),

    sidebarRecords: state =>
        state.records
            .slice()
            .filter(item => item.status_id === 1)
            .filter(item => state.whoami.uid === item.user_id || state.whoami.uid === item.executor_id)
            .sort((a, b) => a.dueDate.valueOf() - b.dueDate.valueOf()),

    overdue: state => {
        let to = moment().utc();

        return state.records
            .slice()
            .filter(item => item.status_id === state.filters.status)
            .filter(item => {
                if (state.filters.division_id) {
                    let b_id = item.miscs.relation?.branch_id ?? null;
                    if (state.filters.division_id === b_id)
                        return true;
                } else
                    return true;
            })
            .filter(item => state.filters.user ? (state.filters.user === item.user_id || state.filters.user === item.executor_id) : true)
            .filter(item => item.dueDate < to)
            .sort((a, b) => a.dueDate.valueOf() - b.dueDate.valueOf())
    },

    today: (state, getters) => {
        let from = moment().utc(),
            to = moment().utc().endOf('day');

        return getters.records
            .filter(item => item.dueDate > from && item.dueDate < to)
    },

    tomorrow: (state, getters) => {
        let from = moment().utc().add(1, 'day').startOf('day'),
            to = moment().utc().add(1, 'day').endOf('day');

        return getters.records
            .filter(item => item.dueDate > from && item.dueDate < to)
    },

    thisWeek: (state, getters) => {
        let from = moment().utc().add(2, 'day').startOf('day'),
            to = moment().utc().weekday(state.lastWeekDay).endOf('day');

        return getters.records
            .filter(item => item.dueDate > from && item.dueDate < to)
    },

}

// actions
const actions = {

    async initDates({commit}) {
        let dateFrom = moment().day(1).format('YYYY-MM-DD'),
            dateTo = moment().day(7).format('YYYY-MM-DD');

        await commit('setFilterDates', {
            dateFrom,
            dateTo,
        });
    },

    async initData({commit, state, dispatch}) {
        let isInit = false;
        commit('setLoading', true);
        if (!state.filters.dateFrom) {
            isInit = true;
            await dispatch('initDates');
        }
        await AxiosHelper({
            url: '/tasks',
            data: {
                isInit,
                ...state.filters
            },
        })
            .then(resp => {
                dispatch('updateRecords', resp.records);

                if (isInit) {
                    commit('setWhoami', resp.whoami);
                    commit('setAdditional', resp.additional);
                    commit('setAdditionalInitialized');
                    commit('setFilterUser', resp.whoami.uid);
                    commit('setInitialized');
                }
            })
            .finally(() => commit('setLoading', false))
    },

    mergeExistsIds({commit, state}, ids) {
        commit('setMessagesIds', [
            ...state.ids,
            ...ids
        ])
    },

    updateRecords({commit, state, dispatch}, records) {
        let ids = state.ids,
            newIds = [];

        records
            .forEach(function (item) {
                item.dueDate = moment.utc(item.due_date, 'YYYY-MM-DD HH:mm:ss').local();
                item.dueTime = item.due_time ? moment.utc(item.due_time, 'HH:mm:ss').local() : null;

                if (item.dueDate.isSame(moment(), 'year')) {
                    if (item.dueDate.isSame(moment(), 'isoWeek')) {
                        item.fullDueDate = item.dueDate.calendar().split(' at ')[0];

                        if (item.dueTime)
                            item.fullDueDate += ', between ' + item.dueDate.format('h:mm A')
                                + ' - ' + item.dueTime.format('h:mm A');
                        else
                            item.fullDueDate += ', at ' + item.dueDate.format('h:mm A');
                    } else {
                        item.fullDueDate = item.dueDate.format('DD MMM YYYY');
                    }
                } else
                    item.fullDueDate = item.dueDate.format('DD MMM YYYY');

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

}

// mutations
const mutations = {

    setInitialized: state => state.is_initialized = true,
    setAdditionalInitialized: state => state.additional_initialized = true,
    setLoading: (state, is_loading) => state.loading = is_loading,

    setWhoami: (state, payload) => state.whoami = payload,

    setAdditional: (state, payload) => state.additional = payload,

    updateRecord: (state, {index, payload}) => Object.assign(state.records[index], payload),
    pushRecord: (state, payload) => state.records.push(payload),

    setMessagesIds: (state, payload) => state.ids = payload,

    setFilterDates: (state, {dateFrom, dateTo}) => {
        state.filters.dateFrom = dateFrom;
        state.filters.dateTo = dateTo;
    },
    setFilterStatus: (state, status_id) => state.filters.status = status_id,
    setFilterUser: (state, user_id) => state.filters.user = user_id,
    setFilterDivision: (state, division_id) => state.filters.division_id = division_id,

}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

import { axiosPromise } from '@/helpers/axiosPromise';
import {
	COMMUNICATION_STATUS_ANSWERED,
	COMMUNICATION_STATUS_NO_ANSWER,
} from '@/store/modules/constants';
import cloneDeep from 'lodash.clonedeep';
import Vue from 'vue';

const mapArrayToObjValues = (object = {}, keys = []) => {
	const obj = cloneDeep(object);
	if (keys.length) {
		for (const k in obj) {
			if (keys.includes(k)) {
				obj[k] =
					Array.isArray(obj[k]) && obj[k].length
						? obj[k].map((v) => v.value)
						: obj[k];
			}
		}
	}
	return obj;
};

const state = () => ({
	filters: {
		// mode: 'all',
		channels: [],
		period: null,
		communications: 'all',
		starred: 'all',
		contacts: 'all',
		untill: null,
		ignoreList: null,
		searchTerm: null,
		responsible: null,
		page: 1,
		entity: null,
	},
	tempFilters: {
		channels: null,
		period: null,
		communications: null,
		starred: null,
		contacts: null,
		untill: null,
		ignoreList: null,
		searchTerm: null,
		responsible: null,
		page: null,
		entity: null,
	},
	tempFiltersNullable: {
		channels: null,
		period: null,
		communications: null,
		starred: null,
		contacts: null,
		untill: null,
		ignoreList: null,
		searchTerm: null,
		responsible: null,
		page: null,
		entity: null,
	},
	initialFilters: {
		// mode: 'all',
		channels: [],
		period: null,
		communications: 'all',
		starred: 'all',
		contacts: 'all',
		untill: null,
		ignoreList: null,
		searchTerm: null,
		responsible: null,
		page: 1,
		entity: null,
	},
	filterParams: {
		periodOptions: [],
		channelOptions: [],
		entityOptions: [],
	},
	infiniteContactsId: 1,
	showFilterWindow: null,
	userID: null,
	divisionID: null,
	moreContacts: null,
	moreFlow: null,
	timezone: 'UTC',
	contacts: [],
	flow: [],
	flowPage: 1,
	flowFetchAbort: null,
	selectedContact: null,
	flowUntill: null,
    flowCallInfo: null,
	responsibleList: [],
	contactsRecordsLoading: null,
	filtersBackup: null,
	// records: []
});

// getters

const getters = {
	sortedFlowRecords(state) {
		// console.log('STATE 1');
		return state.flow.slice().sort((a, b) => a.timestamp - b.timestamp);
	},
	flowRecords(state) {
		// console.log('STATE 1');
		return state.flow.slice();
	},
	sortedContactsRecords(state) {
		// console.log('STATE 2');
		return state.contacts.slice().sort((a, b) => b.timestamp - a.timestamp);
	},
	contactsRecords(state) {
		// console.log('STATE 2');
		return state.contacts; // .slice();
	},
	// getAllRecords(state) {
	//     return state.contacts;
	// },
	// getUnassignRecords(state) {
	//     return state.contacts.filter(record => !record.orderID);
	// }
};

// actions
const actions = {
	fetchFlow({ commit, state }, payload) {
		if (state.flowFetchAbort) {
			state.flowFetchAbort.abort();
		}
		const controller = new AbortController();
		commit('setFlowAbortController', controller);
		return axiosPromise(
			axios.post(
				'/communications/flow',
				{
					contact: state.selectedContact,
					untill: state.flowUntill,
				},
				{ signal: controller.signal }
			)
		)
			.then((data) => {
				commit('setFlowVars', data);
				commit('setFlowRecords', data.records);
			})
			.catch((error) => {
				if (
					!error ||
					(axios.isAxiosError(error) && error.code === 'ERR_CANCELED')
				) {
				} else App.Forms.simpleErrors(error);
			});
	},
	fetchFlowNew({ commit, state }, payload) {
		if (state.flowFetchAbort) {
			state.flowFetchAbort.abort();
		}

		const nextPage = state.moreFlow ? state.flowPage + 1 : 1;
		const controller = new AbortController();
		commit('setFlowAbortController', controller);
		return axiosPromise(
			axios.post(
				'/communications.v2/flow',
				{
					contact: state.selectedContact,
					page: nextPage,
				},
				{ signal: controller.signal }
			)
		)
			.then((data) => {
				commit('setFlowVars', data);
				commit('setFlowRecords', data.records);
				commit('setFlowCallInfo', data.meta?.callInfo);
			})
			.catch((error) => {
				if (
					!error ||
					(axios.isAxiosError(error) && error.code === 'ERR_CANCELED')
				) {
				} else App.Forms.simpleErrors(error);
			});
	},

	fetchContactsRecords({ commit, state }, payload) {
		commit('setContactsRecordsSpinner', true);
		const { page, ...filters } = mapArrayToObjValues(state.filters, [
			'channels',
			'responsible',
		]);
		return axiosPromise(axios.post('/communications/records', { filters }))
			.then((data) => {
				// commit('setTotalCount', data.total);
				commit('setContactVars', data);
				commit('setContactsRecords', { records: data.records });
				commit('setContactsRecordsSpinner', false);
				// commit('setUntill', data.untill);
				// commit('setStatuses', Object.values(data.status.records));
			})
			.catch((error) => {
				commit('setContactVars', { more: false });
				commit('setContactsRecordsSpinner', false);
				App.Forms.simpleErrors(error);
			});
	},

	fetchContactsRecordsNew({ commit, state }, payload) {
		commit('setContactsRecordsSpinner', true);
		const { page = 1, ...filters } = mapArrayToObjValues(state.filters, [
			'channels',
			// 'responsible',
		]);
		const loadMore = payload === 'loadMore' && state.moreContacts;
		const currentPage = loadMore ? page + 1 : 1;
		return axiosPromise(
			axios.post('/communications.v2/records', {
				page: currentPage,
				filters,
			})
		)
			.then((data) => {
				commit('setContactVars', data);
				commit('setContactsRecords', {
					records: data.records,
					clearPrevious: !loadMore,
				});
				commit('setContactsRecordsSpinner', false);
				commit('setFiltersPage', currentPage);
				commit('setFiltersIgnoreList', data.ignore || null);
			})
			.catch((error) => {
				commit('setContactVars', { more: false });
				commit('setContactsRecordsSpinner', false);
				App.Forms.simpleErrors(error);
			});
	},
};

// mutations
const mutations = {
	setFiltersBackup(state, payload) {
		state.filtersBackup = payload;
	},
	setFilters(state, payload) {
		state.filters = payload;
	},
	setContactsRecordsSpinner(state, payload) {
		state.contactsRecordsLoading = payload;
	},
	setResponsiblesList(state, payload) {
		state.responsibleList = payload;
	},
	setFilterWindowState(state, payload) {
		state.showFilterWindow = payload;
	},
	setFlowAbortController(state, data) {
		state.flowFetchAbort = data;
	},
	silentUpdateFilters(state, payload) {
		for (const k in payload) Vue.set(state.filters, k, payload[k]);
	},
	updateFilters(state, payload) {
		const url = new URL(window.location);

		for (const k in payload) {
			const value = payload[k];
			Vue.set(state.filters, k, value);

			url.searchParams.delete(k);
			if (
				value === null ||
				value === undefined ||
				value === '' ||
				(k === 'entity' && value === state.initialFilters.entity) ||
				(k === 'page' && value === 1) ||
				(k === 'communications' && value === 'all') ||
				(k === 'starred' && value === 'all') ||
				(k === 'contacts' && value === 'all')
			) {
				continue;
			}

			if (Array.isArray(value)) {
				url.searchParams.delete(k);
				value.forEach((v) => {
					if (typeof v === 'object' && v?.value) {
						url.searchParams.append(k, v.value);
					} else if (typeof v === 'string' || typeof v === 'number') {
						url.searchParams.append(k, v);
					} else {
						console.log(
							'Unknown array item type',
							v,
							'in',
							k,
							value
						);
					}
				});
			} else if (typeof value === 'object') {
				console.log('Unknown object value', k, value);
			} else {
				url.searchParams.set(k, value);
			}
		}
		window.history.pushState({}, '', url);
	},
	updateFilterParams(state, payload) {
		Vue.set(
			state.filterParams,
			'channelOptions',
			toOptions(payload.channels)
		);
		Vue.set(state.filterParams, 'periodOptions', toOptions(payload.period));

		const entityOptions = toOptions(payload.entities);
		const initialEntity = entityOptions[0]?.value || null;
		Vue.set(state.filterParams, 'entityOptions', entityOptions);
		Vue.set(state.initialFilters, 'entity', initialEntity);

		if (state.filters.entity === null) {
			Vue.set(state.filters, 'entity', initialEntity);
		}

		function toOptions(record = {}) {
			return Object.entries(record).map(([value, label]) => ({
				label,
				value,
			}));
		}
	},
	updateTempFilters(state, payload) {
		for (const k in payload) {
			Vue.set(state.tempFilters, k, payload[k]);
		}
	},
	clearTempFilters(state) {
		for (const k in state.tempFiltersNullable) {
			Vue.set(state.tempFilters, k, state.tempFiltersNullable[k]);
		}
	},
	pushUpdateContactRecord(state, record) {
		record.is_unread = true;
		let updateIndex = -1;
		// find by client then type+channelContact. if finded replace
		if (record.client?.id) {
			updateIndex = state.contacts.findIndex((v) => {
				return v.client?.id == record.client.id;
			});
		}
		if (updateIndex < 0)
			updateIndex = state.contacts.findIndex((v) => {
				let types = ['TwilioSms', 'CallsEvents'];
				if (
					record.type == 'ConversationMark' &&
					record.item.contact_type == 'phone'
				) {
					types.push('ConversationMark');
				} else if (
					record.type == 'ConversationMark' &&
					record.item.contact_type == 'email'
				) {
					types = ['ConversationMark', 'Message'];
				}
				return (
					v.channelContact == record.channelContact &&
					(v.type == record.type ||
						(types.includes(v.type) && types.includes(record.type)))
				);
			});

		//
		if (updateIndex >= 0) {
			// update flow
			if (
				state.selectedContact &&
				state.selectedContact.uid == state.contacts[updateIndex].uid
			) {
				state.flow.push(record);
			}
			if (record.type == 'ConversationMark') {
				// marks as read only
				Vue.set(state.contacts[updateIndex], 'isAnswered', true);
			} else state.contacts.splice(updateIndex, 1, record);
			return true;
		}

		if (record.type !== 'ConversationMark') state.contacts.push(record);
	},
	setFiltersCommunications(state, value) {
		state.filters.communications = value;
	},
	setFiltersStarred(state, value) {
		state.filters.starred = value;
	},
	setFiltersContacts(state, value) {
		state.filters.contacts = value;
	},
	setFiltersPage(state, value) {
		state.filters.page = value;
	},
	setFiltersIgnoreList(state, value) {
		state.filters.ignoreList = value;
	},
	resetFlow(state) {
		state.flow = [];
		state.flowUntill = null;
        state.flowPage = 1;
        state.flowCallInfo = null;
	},
	changeRecordStarredState(state, { uid, starred }) {
		const index = state.contacts.findIndex((v) => v.uid === uid);
		if (index >= 0) {
			Vue.set(state.contacts[index], 'starred', starred);
		}
	},
	changeCurrentContactAnsweredState(state, { uid, isAnswered }) {
		const index = state.contacts.findIndex((v) => v.uid === uid);
		Vue.set(state.contacts[index], 'isAnswered', isAnswered);
		const itemStatus = state.contacts[index]?.item?.status;
		if (itemStatus === COMMUNICATION_STATUS_NO_ANSWER) {
			Vue.set(
				state.contacts[index].item,
				'status',
				COMMUNICATION_STATUS_ANSWERED
			);
		}
		Vue.set(state.selectedContact, 'isAnswered', isAnswered);
	},
	setSelected(state, uid) {
		const index = state.contacts.findIndex((v) => v.uid === uid);
		state.flow = [];
		state.flowUntill = null;
		state.flowCallInfo = null;
        state.moreFlow = null;
		if (state.contacts[index].is_unread)
			Vue.set(state.contacts[index], 'is_unread', null);
		// state.contacts[index].is_unread = null

		// console.log('STATE setSelected');
		// console.log(state);

		state.selectedContact = {
			// recordsIndex: index,
			uid: uid,
			channelContact: state.contacts[index].channelContact,
			client: state.contacts[index].client,
			isAnswered: state.contacts[index].isAnswered,
			type: state.contacts[index].type,
			id: state.contacts[index].id,
		};
	},
	removeRecord(state, { uid }) {
		state.contacts = state.contacts.filter((item) => item.uid != uid);
	},
	assignRecordClient(state, { uid, client, channelContact }) {
		state.contacts.forEach((v) => {
			if (v.uid === uid || v.channelContact === channelContact) {
				v.client = client;
			}
		});
	},
	assignRecordOrder(state, { orderID, uid }) {
		const key = state.contacts.findIndex((item) => {
			return item.uid === uid;
		});

		state.contacts[key].orderID = orderID;
		// перебираем похожие записи без заказов
		for (const k of state.contacts.keys()) {
			if (
				state.contacts[k].orderID ||
				state.contacts[k].type != state.contacts[key].type
			)
				continue;
			if (
				state.contacts[key].type == 'CallsEvents' &&
				state.contacts[key].item.caller_id ==
					state.contacts[k].item.caller_id
			) {
				state.contacts[k].orderID = orderID;
			} else if (
				state.contacts[key].type == 'Message' &&
				state.contacts[k].item.miscs?.from?.email &&
				state.contacts[k].item.miscs.from.email ==
					state.contacts[key].item.miscs.from.email
			) {
				state.contacts[k].orderID = orderID;
			}
		}
	},
	updateSelected(state, { uid, value }) {
		for (const k of state.contacts.keys()) {
			if (state.contacts[k].uid == uid) {
				Vue.set(state.contacts[k], 'selected', value);
				break;
			}
		}

		//state.records[]
	},
	setFlowVars(state, payload) {
		// console.log('STATE setFlowVars');
		state.moreFlow = payload.more;
		state.flowUntill = payload.untill;
		state.flowPage = payload.page;
	},

	setContactVars(state, payload) {
		// console.log('STATE setContactVars');
		if ('timezone' in payload) state.timezone = payload.timezone;
		if ('untill' in payload) state.filters.untill = payload.untill;
		if ('ignoreList' in payload)
			state.filters.ignoreList = payload.ignoreList;
		if ('more' in payload) state.moreContacts = payload.more;
	},

	// setTimezone(state, payload) {
	//     state.timezone = payload;
	// },
	// setUntill(state, payload) {
	//     state.filters.untill = payload;
	// },
	setFlowRecords(state, payload) {
		// console.log('STATE 4');
		for (const record of payload) {
			state.flow.push(record);
		}
	},
	setFlowCallInfo(state, payload) {
		state.flowCallInfo = payload || null;
	},
	clearContactsRecords(state) {
		// console.log('STATE 5');

		state.selectedContact = null;
		state.filters.ignoreList = null;
		state.filters.untill = null;
		state.flow = [];
		state.contacts = [];
		state.infiniteContactsId = state.infiniteContactsId + 1;
	},
	setContactsRecords(state, payload) {
		// console.log('STATE 6');
		// console.log(state);
		if (payload.clearPrevious) {
			state.contacts = [];
		}
		(payload.records || []).forEach((record) => {
			state.contacts.push(record);
		});
	},
};

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
};

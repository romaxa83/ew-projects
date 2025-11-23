// initial state
import { AxiosHelper } from '@/helpers/axiosHelper';
import { axiosPromise } from '@/helpers/axiosPromise';
import {
	DEFAULT_ALL,
	DEFAULT_SORT,
	refetchChangelog,
} from '@/services/changelog';
import axios from 'axios';

const state = () => ({
	items: [],
	permissions: {
		canManageOrder: false,
		canManageClients: false,
		canViewOrderList: false,
		canViewChangelog: false,
		canViewEmployeeCard: false,
	},
	calculatedTotal: null,
	checkoutStatus: null,
	notes: [],
	tasks: [],
	payroll: null,
    comments: [],
	calculationWarnings: [],
	settings: {
		estimate: {},
		estimate_options: {},
		zadarma: {},
	},
	changelog: {
		data: [],
		api: '/orders/logs',
		hasMore: false,
		sort: DEFAULT_SORT,
		all: DEFAULT_ALL,
		refetching: false,
		staticRequestParams: {},
	},
	status: {
		loading: true,
		records: {},
		routes: {},
		prev_status: 0,
	},
	communicationPanel: {
		pinnedNotes: [],
		showPinnedNotes: false,
		records: [],
		untill: null,
		allLoaded: null,
	},
	communicationPanelV2: {
		pinnedNotes: [],
		showPinnedNotes: false,
		records: [],
		allLoaded: null,
        callInfo: null,
	},
	dataSourcesLoading: new $.Deferred(),
	dataSources: {
		sources: null,
		divisions: null,
		moveSizes: null,
		moveTypes: null,
	},
	inventories_processing: false,
    inventoryIsAutosaveMode: true,
	forcePanelInterface: null,
	materials: {
		records: [],
		customs: [],
	},
});

// getters
const getters = {
	getCalculatedTotal(state) {
		return state.calculatedTotal;
	},
	// Communication Panel V1
	pinnedNotesText(state) {
		if (state.communicationPanel.pinnedNotes.length == 1) {
			return '1 pinned note';
		} else if (state.communicationPanel.pinnedNotes.length > 1) {
			return (
				state.communicationPanel.pinnedNotes.length + ' pinned notes'
			);
		}
		return null;
	},
	isShowPinnedNotes(state) {
		return state.communicationPanel.showPinnedNotes;
	},
	getOrderPinnedNotes(state) {
		return state.communicationPanel.pinnedNotes;
	},
	getCommunicationRecordsByDesc(state) {
		return state.communicationPanel.records.slice().sort((a, b) => {
			return a.timestamp - b.timestamp;
		});
	},
	// Communication Panel V2
	pinnedNotesTextV2(state) {
		if (state.communicationPanelV2.pinnedNotes.length == 1) {
			return '1 pinned note';
		} else if (state.communicationPanelV2.pinnedNotes.length > 1) {
			return (
				state.communicationPanelV2.pinnedNotes.length + ' pinned notes'
			);
		}
		return null;
	},
	isShowPinnedNotesV2(state) {
		return state.communicationPanelV2.showPinnedNotes;
	},
	getOrderPinnedNotesV2(state) {
		return state.communicationPanelV2.pinnedNotes;
	},
	getCommunicationRecordsByDescV2(state) {
		return state.communicationPanelV2.records.slice().sort((a, b) => {
			return a.timestamp - b.timestamp;
		});
	},
	settingsEstimate: (state) => (type) => {
		return state.settings.estimate[type]
			? state.settings.estimate[type]
			: {};
	},
	optionsEstimate: (state) => (type) => {
		return state.settings.estimate_options[type]
			? state.settings.estimate_options[type]
			: {};
	},

	status: (state) => state.status,

	dataSourcesLoading: (state) => state.dataSourcesLoading,

	inventories: (state, getters, rootState) => {
		return {
			records: rootState.session
				? rootState.session.order.inventories
				: [],
			sizing_is_auto: rootState.session
				? !!rootState.session.order.sizing_is_auto
				: null,
			sizing_volume: rootState.session
				? rootState.session.order.sizing_volume
				: null,
			sizing_weight: rootState.session
				? rootState.session.order.sizing_weight
				: null,
		};
	},

	inventoriesProcessing: (state) => {
		return state.inventories_processing;
	},

    inventoryIsAutosaveMode: (state) => {
        return state.inventoryIsAutosaveMode;
    },

	inventoriesFormat: (state, getters) => {
		let noGroups = getters.inventories.records.filter(function (v) {
				return v.hasOwnProperty('section_id')
					? !v.section_id && !v.is_section
					: v.type === 'item';
			}),
			groups = getters.inventories.records
				.filter(function (v) {
					return v.hasOwnProperty('section_id')
						? v.is_section
						: v.type === 'room';
				})
				.map(function (v) {
					let volume = v.children
							? v.children.reduce(function (sum, v) {
									return (
										sum +
										(parseFloat(v.volume)
											? parseFloat(v.volume) * v.qty
											: 0)
									);
							  }, 0)
							: 0,
						weight = v.children
							? v.children.reduce(function (sum, v) {
									return (
										sum +
										(parseFloat(v.weight)
											? parseFloat(v.weight) * v.qty
											: 0)
									);
							  }, 0)
							: 0;

					return {
						id: v.id,
						title: v.title,
						volume,
						weight,
					};
				});

		if (noGroups.length > 0) {
			let volume = noGroups.reduce(function (sum, v) {
					return (
						sum +
						(parseFloat(v.volume)
							? parseFloat(v.volume) * v.qty
							: 0)
					);
				}, 0),
				weight = noGroups.reduce(function (sum, v) {
					return (
						sum +
						(parseFloat(v.weight)
							? parseFloat(v.weight) * v.qty
							: 0)
					);
				}, 0);

			groups.push({
				title: '—',
				volume,
				weight,
			});
		}

		return groups;
	},

	materials: (state) => state.materials.records,
	customsExtras: (state) => state.materials.customs,

	totalMaterials: (state) => {
		return state.materials.records.reduce(function (sum, v) {
			let packing_price =
					v.need_packing && parseFloat(v.packing_price)
						? parseFloat(v.packing_price)
						: 0,
				unpacking_price =
					v.need_unpacking && parseFloat(v.unpacking_price)
						? parseFloat(v.unpacking_price)
						: 0;

			return (
				sum +
				(parseFloat(v.price) + packing_price + unpacking_price) * v.qty
			);
		}, 0);
	},

	totalCustomExtras: (state) => {
		return state.materials.customs.reduce(function (sum, item) {
			return sum + (item.price ? parseFloat(item.price) : 0);
		}, 0);
	},

	works: (state, getters, rootState) => {
		return {
			records: rootState.session ? rootState.session.order.works : [],
		};
	},

	waypoints: (state, getters, rootState) => {
		return {
			records: rootState.session ? rootState.session.order.waypoints : [],
		};
	},

	estimate: (state, getters, rootState) => {
		return rootState.session ? rootState.session.order.estimate : null;
	},

	calculated: (state, getters, rootState) => {
		return rootState.session ? rootState.session.order.calculated : null;
	},

	notes: (state) => {
		return state.notes;
	},

	tasks: (state) => {
		return state.tasks;
	},
	payroll: (state) => {
		return state.payroll;
	},
    comments: (state) => {
        return state.comments;
    },
};

// actions
const actions = {
	fetchFirstCalculationWarning({ commit, state }) {
		return new Promise((resolve) => {
			const warning = state.calculationWarnings[0];
			commit('removeFirstCalculationWarning');
			resolve(warning);
		});
	},
	// TODO move to app_tasks or tasks_calendar
	completeTask({ commit }, payload) {
		return axiosPromise(axios.post('/tasks/modifyTask', payload)).then(
			(data) => {
				if (data.record)
					commit('updateCommunicationRecord', data.record);
				// this.$store.commit('order/updateCommunicationTask', data.record)
				if (data.new_record)
					commit('pushCommunicationRecord', data.new_record);

				// this.$store.commit('order/pushCommunicationRecord', data.new_record)
				// commit('updateCommunicationRecords', data);
			}
		);
	},
	fetchCommunicationRecords({ commit, state }) {
		return axiosPromise(
			axios.post('/orders/communicationsPanelHistory', {
				orderID: this.state.session.order.id,
				historyTill: state.communicationPanel.untill,
			})
		).then(({ data }) => {
			commit('updateCommunicationRecords', data);
		});
	},
	fetchCommunicationRecordsV2({ commit, state }) {
		return axiosPromise(
			axios.post('/orders/communicationsPanelHistoryNew', {
				orderID: this.state.session.order.id,
			})
		)
			.then(({ data }) => {
				commit('updateCommunicationRecordsV2', data);
			})
			.catch((e) => {
				const msg = e?.response?.data?.message || e?.message || 'Error';
				App.Forms.showAlert('error', msg);
				commit('updateCommunicationRecordsV2', {
					records: [],
					pinnedNotes: [],
					recordsTill: null,
					more: false,
				});
			});
	},
	removeOrderNote({ commit }, payload) {
		return axiosPromise(axios.post('/orders/notes/remove', payload));
	},
	updateOrderNote({ commit }, payload) {
		return axiosPromise(axios.post('/orders/notes/update', payload));
	},
	fetchDatasources({ commit }, payload) {
		return axiosPromise(axios.post('/orders/info-statuses', payload)).then(
			(data) => {
				commit('setDatasources', data.dataSources);
				let records = data.status.records,
					routes = {};

				// Переформатируем роуты
				Object.keys(data.status.routes).forEach(function (key) {
					let ids = [];
					data.status.routes[key].forEach((item) =>
						ids.push(item.route_to_status_id)
					);

					routes[key] = ids;
				});
				commit('setStatusList', { records, routes });
				commit('setStatusPrev', data.prev_status);
			}
		);
	},
	setStatusList({ state, commit }, payload) {
		let records = payload.records,
			routes = {};

		// Переформатируем роуты
		Object.keys(payload.routes).forEach(function (key) {
			let ids = [];
			payload.routes[key].forEach((item) =>
				ids.push(item.route_to_status_id)
			);

			routes[key] = ids;
		});

		commit('setStatusList', { records, routes });
		commit('setStatusPrev', payload.prev_status);
	},

	refetchChangelog({ state, commit }, payload) {
		return refetchChangelog({
			payload,
			current: state.changelog,
			onStart: () => commit('setChangelogRefetching', true),
			onEnd: () => commit('setChangelogRefetching', false),
			onSuccess: (logs) => commit('setChangelog', logs),
		});
	},

	triggerChangelog({ state, commit }) {
		const link = state.changelog.links.find(
			(link) => link.active && !!link.url
		);
		if (link) {
			commit('refetchChangelog', {
				url: link.url,
				sort: state.changelog.sortType,
			});
		} else {
			console.warn('No active link found');
		}
	},

	updateInventory({ state, commit }, payload) {
		commit('setInventory', payload);
	},

	updateWorks({ state, commit }, payload) {
		commit('setWorks', payload);
	},

	updateWaypoints({ state, commit }, payload) {
		commit('setWaypoints', payload);
	},

	// Обновить время и растаяние маршрута
	updateEstimateTimes({ state, commit }, payload) {
		commit('setEstimateTimes', payload);
	},

	updateEstimate({ state, commit }, payload) {
		commit('setEstimate', payload);
	},

	updateCalculated({ state, commit }, payload) {
		commit('setCalculated', payload);
	},

	updateNotes({ state, commit }, payload) {
		commit('setNotes', payload);
	},

	updateTasks({ state, commit }, payload) {
		commit('setTasks', payload);
	},

	setOrderCommunicationPanelPreset({ state, commit }, { orderID, presets }) {
		return axiosPromise(
			axios.post('/orders/view-preset-save', {
				orderID,
				presets,
			})
		);
	},

	saveMaterials({ commit, rootState, dispatch }, payload) {
		return AxiosHelper({
			url: '/orders/materials/save',
			data: {
				order_id: rootState.session.order.id,
				...payload,
			},
		})
			.then(({ record, msg }) => {
				commit('setMaterials', record.materials);
				commit('setCustomsExtras', record.customs_extras);

				return {
					record,
					msg,
				};
			})
			.then(() => {
				dispatch('refetchChangelog', 'update');
			});
	},
};

// mutations
const mutations = {
	removeFirstCalculationWarning(state) {
		state.calculationWarnings.splice(0, 1);
	},
	updateForcePanelInterface(state, value) {
		state.forcePanelInterface = value;
	},
	storeCalculationWarnings(state, warnings) {
		if (warnings.length > 0)
			for (const warning of warnings)
				state.calculationWarnings.push(warning);
	},
	// Communication Panel V1
	toggleShowPinnedNotes(state) {
		state.communicationPanel.showPinnedNotes =
			!state.communicationPanel.showPinnedNotes;
	},
	removeCommunicationRecord(state, { type, id }) {
		const r = state.communicationPanel.records.slice();
		let records = [];
		for (const i in r) {
			if (r[i].type == type && r[i].item.id == id) continue;
			records.push(r[i]);
		}
		state.communicationPanel.records = records;
	},
	updateCommunicationRecord(state, record) {
		// ищем совпадения по
		// record.type и record.item.id
		let records = state.communicationPanel.records.slice();
		for (const i in records) {
			if (
				records[i].type == record.type &&
				records[i].item.id == record.item.id
			) {
				records[i] = record;
				break;
			}
		}
		state.communicationPanel.records = records;
		//state.communicationPanel.records.push(record);
	},
	pushCommunicationRecord(state, record) {
		state.communicationPanel.records.push(record);
		if (record.type == 'Notes' && record.item.is_pinned) {
			state.communicationPanel.pinnedNotes.push(record);
		}
	},
	updateCommunicationRecords(state, data) {
		// console.log(data);
		state.communicationPanel.records = [
			...state.communicationPanel.records,
			...data.records,
		];
		state.communicationPanel.untill = data.recordsTill;
		state.communicationPanel.pinnedNotes = data.pinnedNotes;
		state.communicationPanel.allLoaded = !data.more;
	},
	// Communication Panel V2
	toggleShowPinnedNotesV2(state) {
		state.communicationPanelV2.showPinnedNotes =
			!state.communicationPanelV2.showPinnedNotes;
	},
	removeCommunicationRecordV2(state, { type, id }) {
		const r = state.communicationPanelV2.records.slice();
		let records = [];
		for (const i in r) {
			if (r[i].type == type && r[i].item.id == id) continue;
			records.push(r[i]);
		}
		state.communicationPanelV2.records = records;
	},
	updateCommunicationRecordV2(state, record) {
		// ищем совпадения по
		// record.type и record.item.id
		let records = state.communicationPanelV2.records.slice();
		for (const i in records) {
			if (
				records[i].type == record.type &&
				records[i].item.id == record.item.id
			) {
				records[i] = record;
				break;
			}
		}
		state.communicationPanelV2.records = records;
		//state.communicationPanelV2.records.push(record);
	},
	pushCommunicationRecordV2(state, record) {
		state.communicationPanelV2.records.push(record);
		if (record.type == 'Notes' && record.item.is_pinned) {
			state.communicationPanelV2.pinnedNotes.push(record);
		}
	},
	updateCommunicationRecordsV2(state, data) {
		state.communicationPanelV2.records = data.records;
		state.communicationPanelV2.pinnedNotes = data.pinnedNotes;
		state.communicationPanelV2.allLoaded = !data.more;
        state.communicationPanelV2.callInfo = data.meta?.callInfo || null;
	},

	setDatasources(state, data) {
		state.dataSources = data;

		state.dataSourcesLoading.resolve(true);
	},

	setPermissions(state, data = {}) {
		const { is_partner } = data;
		state.permissions = {
			canManageOrder: !is_partner,
			canManageClients: !is_partner,
			canViewOrderList: !is_partner,
			canViewChangelog: !is_partner,
			canViewEmployeeCard: !is_partner,
		};
	},

	setChangelogStaticRequestParams(state, payload) {
		state.changelog.staticRequestParams = payload;
	},

	setChangelog(
		state,
		{ data, hasMore, sort = DEFAULT_SORT, all = DEFAULT_ALL }
	) {
		state.changelog.data = data;
		state.changelog.hasMore = hasMore;
		state.changelog.sort = sort;
		state.changelog.all = all;
	},

	setChangelogRefetching(state, value) {
		state.changelog.refetching = value;
	},

	setStatusList(state, { records, routes }) {
		state.status.records = records;
		state.status.routes = routes;
		state.status.loading = false;
	},

	setStatusPrev(state, prev_id) {
		state.status.prev_status = prev_id;
	},

	setSettingsZadarma(state, payload) {
		state.settings.zadarma = payload;
	},

	setSettingsEstimate(state, payload) {
		let records = {};
		payload.forEach(function (item) {
			if (!(item.estimate_type in records))
				records[item.estimate_type] = {};

			records[item.estimate_type][item.name] = item.value;
		});

		state.settings.estimate = records;
	},
	setSettingsOptions(state, payload) {
		let records = {};
		payload.forEach(function (item) {
			if (!(item.estimate_type in records))
				records[item.estimate_type] = {};
			records[item.estimate_type][item.name] = item.value;
		});
		state.settings.estimate_options = records;
	},

	setInventory(state, payload) {
        if (payload) {
            this.state.session.order.inventories = payload.inventories;
            this.state.session.order.sizing_is_auto = payload.sizing_is_auto;
            this.state.session.order.sizing_volume = payload.sizing_volume;
            this.state.session.order.sizing_weight = payload.sizing_weight;
        }
	},

	setInventoryProcessing(state, status) {
		state.inventories_processing = status;
	},

	changeClientId(state, id) {
		this.state.session.order.client_id = id;
	},

	setInventoryRecords(state, records) {
		this.state.session.order.inventories = records;
	},

	setSizingIsAuto(state, value) {
		this.state.session.order.sizing_is_auto = value;
	},

	setSizingVolume(state, value) {
		this.state.session.order.sizing_volume = value;
	},

	setSizingWeight(state, value) {
		this.state.session.order.sizing_weight = value;
	},

	setMaterials(state, payload) {
		state.materials.records = payload.map((item) => {
			// Need for saving data when click remove item
			item.id = item.material_id;
			item.group_id = item.type_id;
			item.checked = {
				checked: true,
				packing_checked: item.need_packing,
				unpacking_checked: item.need_unpacking,
			};

			return item;
		});
	},

	setPayroll(state, payload) {
		state.payroll = payload;
	},

    setComments(state, payload) {
        state.comments = payload;
    },

	setCustomsExtras(state, payload) {
		state.materials.customs = payload;
	},

	setWorks(state, payload) {
		this.state.session.order.works = payload;
	},

	setWaypoints(state, payload) {
		this.state.session.order.waypoints = payload;
	},

	setEstimate(state, payload) {
		this.state.session.order.estimate = payload;
	},

	setCalculated(state, payload) {
		this.state.session.order.calculated = payload;
		// total
		state.calculatedTotal = payload.find((item) => item.title === 'total');
	},

	setEstimateTimes(state, payload) {
		this.state.session.order.estimate.calculated_moving_distance =
			payload.calculated_moving_distance;
		this.state.session.order.estimate.calculated_moving_distance_is_auto =
			payload.calculated_moving_distance_is_auto;
		this.state.session.order.estimate.calculated_moving_distance_auto =
			payload.calculated_moving_distance_auto;
		this.state.session.order.estimate.calculated_moving_time =
			payload.calculated_moving_time;
	},

	setNotes(state, payload) {
		state.notes = payload;
	},

	setTasks(state, payload) {
		state.tasks = payload;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
};

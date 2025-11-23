// Пример кода https://github.com/vuejs/vuex/tree/dev/examples/shopping-cart
import { axiosPromise } from '@/helpers/axiosPromise';
import { getLogsApiResult } from '@/services/changelog';
import clients from '@/store/modules/clients';
import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import { getPeaksDates, getSession } from '../api/crm';
import appTasks from './modules/app_tasks';
import calls from './modules/calls';
import communicationsEmployee from './modules/communications_employee';
import communicationsFlow from './modules/communications_flow';
import communicationsIncomingCalls from './modules/communications_incoming-calls';
import dispatch from './modules/dispatch';
import mailbox from './modules/mailbox';
import order from './modules/order';
import orderActivity from './modules/order_activity';
import ordersPipeline from './modules/orders_pipeline';
import salesTeamsPlans from './modules/sales_teams_plans';
import tasksCalendar from './modules/tasks_calendar';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

const vuexLocal = new VuexPersistence({
	storage: window.localStorage,
	modules: ['calls'],
});
export default new Vuex.Store({
	state: {
		error: null,
		session: null,
		loading: new $.Deferred(),
		peaks: {
			dates: [],
			weeks: [],
		},
		divisionID: null,
		user: null,
		zadarma: null,
		employer: null,
	},
	mutations: {
		setUserEnvironment(state, data) {
			state.user = data.user;
			state.employer = data.employer;
			state.divisionID = data.divisionID;
			state.zadarma = data.zadarma;
		},
		setError(state, error) {
			state.error = error;
		},
		clearError(state) {
			state.error = null;
		},
		setSession(state, data) {
			state.session = data;
			state.order.calculatedTotal = data.order.calculated.find(
				(v) => v.title === 'total'
			);
		},
		setPeaksRecords(state, payload) {
			state.peaks = payload;
		},
		setOrderStatus(state, status_id) {
			state.session.order.status_id = status_id;
		},
	},
	actions: {
		fetchEnvironment({ commit, state }, payload) {
			let qs = '';
			if (payload) qs = '?' + new URLSearchParams(payload).toString();
			return axiosPromise(axios.get('/userEnvironment' + qs)).then(
				(data) => {
					commit('setUserEnvironment', data);
					if (payload.page && payload.page == 'communications') {
						commit(
							'communicationsFlow/setResponsiblesList',
							data.responsibles
						);
					}
				}
			);
		},
		initSession({ state, commit, dispatch }, { id }) {
			getSession(id).then((resp) => {
				commit('setSession', {
					order: resp.order,
					types: resp.types,
					payroll: resp.payroll,
				});
				commit('order/setPermissions', resp.auth_user);
				commit('order/setChangelogStaticRequestParams', {
					order_id: resp.order.id,
				});
				commit('order/setChangelog', getLogsApiResult(resp.logs));
				commit('order/setSettingsEstimate', resp.settings.estimate);
				commit(
					'order/setSettingsOptions',
					resp.settings.allowed_options
				);
				commit('order/setSettingsZadarma', resp.settings.zadarma);
				commit('order/setTasks', resp.order.tasks);
				commit('order/setMaterials', resp.order.materials);
				commit('order/setPayroll', resp.payroll);
				commit('order/setComments', resp.order.foreman_notes);
				commit('order/setCustomsExtras', resp.order.customs_extras);

				if (resp.presets) {
					if (resp.presets?.communicationPanelView) {
						commit(
							'order/updateForcePanelInterface',
							resp.presets.communicationPanelView
						);
					}
				}

				if (resp.activities.gmail.total)
					dispatch('orderActivity/addRecords', {
						section: 'gmail',
						...resp.activities.gmail,
					});

				if (resp.activities.messages.total)
					dispatch('orderActivity/addRecords', {
						section: 'messages',
						...resp.activities.messages,
					});

				state.loading.resolve(true);
			});
		},
		initPeaksDates({ commit }) {
			return getPeaksDates().then(
				({ records: dates, weeklyPeaks: weeks }) => {
					commit('setPeaksRecords', {
						dates,
						weeks,
					});
				}
			);
		},
		getSession: ({ state }) => {
			return state.loading.promise().then(() => state.session);
		},
		updatePeaksDates({ state, commit }, payload) {
			commit('setPeaksDates', payload);
		},
		// changeOrderStatus({state, commit}, status_id) {
		//     commit('setOrderStatus', status_id)
		// },
	},
	getters: {
		error: (s) => s.error,
		getOrderId: (state) => {
			return state.session ? state.session.order.id : null;
		},
		getClientId: (state) => {
			return state.session?.order?.client_id
				? state.session.order.client_id
				: null;
		},
		getDivisionId: (state) => {
			return state.session?.order?.division_id
				? state.session.order.division_id
				: null;
		},
		getSession: (state) => {
			return state.session ? state.session : false;
		},
		getPeaksDates: (state) => {
			return state.peaks;
		},
	},
	modules: {
		order,
		calls,
		dispatch,
		mailbox,
		orderActivity,
		appTasks,
		clients,
		tasksCalendar,
		ordersPipeline,
		communicationsEmployee,
		communicationsIncomingCalls,
		communicationsFlow,
        salesTeamsPlans,
	},
	strict: debug,
	// plugins: debug ? [createLogger(), vuexLocal.plugin] : [vuexLocal.plugin]
	plugins: [vuexLocal.plugin],
});

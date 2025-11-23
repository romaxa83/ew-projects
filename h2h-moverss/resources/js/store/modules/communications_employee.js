import { axiosPromise } from '@/helpers/axiosPromise';

const ON_CALL = {
	status: 'on-call',
	label: 'On call',
	api_key: 'count_oncall',
};
const ONLINE = { status: 'online', label: 'Online', api_key: 'count_online' };
const OFFLINE = {
	status: 'offline',
	label: 'Offline',
	api_key: 'count_offline',
};

const SHOW_OFFLINE_KEY = 'cp-show-offline-employees';

/** @returns {boolean} */
function getSavedShowOfflineValue() {
    const value = localStorage.getItem(SHOW_OFFLINE_KEY);
    return value === 'true';
}

/** @param {boolean} value */
function saveShowOfflineValue(value) {
    localStorage.setItem(SHOW_OFFLINE_KEY, String(value));
}

const state = () => ({
	fetchParams: {
		showOffline: getSavedShowOfflineValue(),
	},
	fetchError: null,
	stats: [],
	employees: [],
});

const getters = {};

function _fetch(commit, params) {
	return axiosPromise(axios.get('/communications/employees', { params }))
		.then((response) => {
			if (response.success) {
				commit('clearError');
				commit('setStats', response.meta);
				commit('setEmployees', response.records);
			} else {
				throw new Error('Response is not successful!');
			}
		})
		.catch((e) => {
			commit('setError', e);
		});
}

const actions = {
	fetchEmployees({ state, commit }) {
		return _fetch(commit, {
			reload_sip_status: true,
			show_offline: state.fetchParams.showOffline,
		});
	},
	refreshEmployees({ state, commit }) {
		return _fetch(commit, {
			show_offline: state.fetchParams.showOffline,
		});
	},
};

const mutations = {
	setStats(state, meta) {
		state.stats = [ON_CALL, ONLINE, OFFLINE].map((stat) => ({
			status: stat.status,
			label: stat.label,
			qty: meta[stat.api_key],
		}));
	},

	setShowOffline(state, showOffline) {
        saveShowOfflineValue(showOffline);
		state.fetchParams.showOffline = showOffline;
	},

	setEmployees(state, records) {
		state.employees = records.map((record) => {
			const employee = {
				id: record.id,
				name: record.user.name,
				status: OFFLINE.status,
				statusLabel: OFFLINE.label,
			};

			if (
				typeof record.call === 'object' &&
				record.call !== null &&
				!Array.isArray(record.call)
			) {
				employee.status = ON_CALL.status;
				employee.statusLabel = ON_CALL.label;

				const clientId = callValue('client_id');
				const clientName = callValue('client_name');
				employee.callDetails = {
					number: callValue('number'),
					startedAt: callValue('start_at'),
					client:
						clientId && clientName
							? { id: clientId, name: clientName }
							: null,
				};

				function callValue(value) {
					return record.call[value] || null;
				}
			} else if (record.is_online) {
				employee.status = ONLINE.status;
				employee.statusLabel = ONLINE.label;
			}

			return employee;
		});
	},

	clearError(state) {
		state.fetchError = null;
	},

	setError(state, error) {
		state.fetchError =
			error instanceof Error ? error.message : String(error);
	},
};

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
};

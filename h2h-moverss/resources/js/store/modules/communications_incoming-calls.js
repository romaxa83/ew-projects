import { axiosPromise } from '@/helpers/axiosPromise';

const state = () => ({
	calls: [],
	fetchError: null,
});

const getters = {};

const actions = {
	fetchCalls({ state, commit }) {
		return axiosPromise(axios.get('/communications/incoming-calls'))
			.then((response) => {
				if (response.success) {
					commit('clearError');
					commit('setCalls', response.records);
				} else {
					throw new Error('Response is not successful!');
				}
			})
			.catch((e) => {
				commit('setError', e);
			});
	},
};

const mutations = {
	setCalls(state, records) {
		state.calls = records.map((record) => {
			return {
				id: record.id,
				caller: {
					id: record.client?.id || null,
					name: getCallerName(record.client),
					phone: record.phone,
				},
				startTimestampInSeconds: record.created_at,
			};
		});

		function getCallerName(client) {
			const { first_name, last_name } = client || {};
			const name = [first_name, last_name]
				.filter(Boolean)
				.join(' ')
				.trim();
			return name || 'Unknown caller';
		}
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

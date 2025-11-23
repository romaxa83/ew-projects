import { axiosPromise } from '@/helpers/axiosPromise';
import { DateService } from '@/services/date';
import {
	hasSalesErrorInterstate,
	hasSalesErrorLocal,
	invalidEfficiencyConversation,
} from '@/services/sales_teams_plans';
import axios from 'axios';

const state = () => ({
	sales_plans: {
		local: [],
		long: [],
	},
	efficiency_plan: {},
	hasSalesErrors: false,
	hasEfficiencyErrors: false,
	submitting: false,
});

const getters = {
	salesPlansLocal: (state) => state.sales_plans.local,
	salesPlansLong: (state) => state.sales_plans.long,
	efficiencyLocal: (state) => state.efficiency_plan.conversion_local_team,
	efficiencyLong: (state) => state.efficiency_plan.conversion_long_team,
	hasSalesErrors: (state) => state.hasSalesErrors,
	hasEfficiencyErrors: (state) => state.hasEfficiencyErrors,
	submitting: (state) => state.submitting,
};

const actions = {
	fetchPlans({ commit, dispatch }, payload) {
		const date = new DateService(payload.date);
		return axiosPromise(
			axios.post('/company/sales-team', {
				date: date.format({ preset: 'monthFilter', convert: false }),
			})
		).then(({ data }) => {
			commit('setPlans', data);
			dispatch('validateValues');
		});
	},
	updateSales({ commit, state, dispatch }, { team, index, type, value }) {
		const field = type === 'interstate' ? 'intrestate' : type;
		commit('updateSalesPlans', { team, index, field, value });
		dispatch('validateValues');
	},
	updateEfficiency({ commit, state, dispatch }, { team, value }) {
		commit('updateEfficiencyPlan', {
			field: `conversion_${team}_team`,
			value,
		});
		dispatch('validateValues');
	},
	validateValues({ commit, state }) {
		commit('hasErrors', {
			sales: !!(
				state.sales_plans.local.some(hasSalesErrorLocal) ||
				state.sales_plans.long.some(
					(r) => hasSalesErrorLocal(r) || hasSalesErrorInterstate(r)
				)
			),
			efficiency: !!(
				invalidEfficiencyConversation(
					state.efficiency_plan.conversion_local_team
				) ||
				invalidEfficiencyConversation(
					state.efficiency_plan.conversion_long_team
				)
			),
		});
	},
	submit({ state, commit }) {
		commit('submitting', true);
		return axiosPromise(
			axios.post('/company/sales-team/update', {
				sales_plans: state.sales_plans,
				efficiency_plan: state.efficiency_plan,
			})
		)
			.then(({ success }) => {
				if (success) {
					window.App.Forms.showAlert('success', 'Changes are saved!');
				} else {
					throw new Error('Response is not successful!');
				}
			})
			.catch((error) => {
				window.App.Forms.simpleErrors(error);
			})
			.finally(() => {
				commit('submitting', false);
			});
	},
};

const mutations = {
	setPlans(state, { sales_plans, efficiency_plan }) {
		state.sales_plans = {
			local: sales_plans.local.map((record) => ({
				...record,
				local: record.local ?? record.prev_local,
			})),
			long: sales_plans.long.map((record) => ({
				...record,
				local: record.local ?? record.prev_local,
				intrestate: record.intrestate ?? record.prev_intrestate,
			})),
		};
		state.efficiency_plan = {
			...efficiency_plan,
			conversion_local_team:
				efficiency_plan.conversion_local_team ??
				efficiency_plan.prev_conversion_local_team,
			conversion_long_team:
				efficiency_plan.conversion_long_team ??
				efficiency_plan.prev_conversion_long_team,
		};
	},
	hasErrors(state, { sales, efficiency }) {
		state.hasSalesErrors = sales;
		state.hasEfficiencyErrors = efficiency;
	},
	submitting(state, value) {
		state.submitting = value;
	},
	updateSalesPlans(state, { team, index, field, value }) {
		state.sales_plans[team][index][field] = value;
	},
	updateEfficiencyPlan(state, { field, value }) {
		state.efficiency_plan[field] = value;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
};

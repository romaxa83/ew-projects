<template>
	<div>
		<div class="subheader">
			<h1 class="subheader-title d-flex flex-row">
				Teams Plans
				<span
					v-if="loading"
					role="status"
					aria-hidden="true"
					title="Refreshing..."
					class="spinner-border spinner-border-sm fs-nano mt-1 ml-2"
				/>
			</h1>
			<button
				type="button"
				class="btn"
				:class="submitButtonClassName"
				:disabled="submitButtonDisabled"
				:title="submitTooltip"
				@click="$store.dispatch('salesTeamsPlans/submit')"
			>
				<span
					v-if="submitting"
					role="status"
					aria-hidden="true"
					title="Refreshing..."
					class="spinner-border spinner-border-sm fs-nano opacity-50 mr-1"
				/>
				Save Changes
			</button>
		</div>
		<ui-row>
			<ui-coll :span="12">
				<ui-panel
					title="Efficiency Plans"
					title-slot-class-name="align-self-end"
				>
					<template v-slot:title>
						<ui-tabs-nav
							class="border-bottom-0"
							:tabs="tabs"
							:active="activeTab"
							@tab-click="setActiveTab"
						/>
					</template>
					<template v-if="showDatepicker" v-slot:toolbar>
						<ui-datepicker
							id="datepicker"
							format="months"
							@date-change="dateChange"
						/>
					</template>
					<div v-if="loading">Loading...</div>
					<div v-else>
						<component
							v-for="content in tabs"
							v-if="content.value === activeTab"
							:key="content.value"
							:is="content.component"
						/>
					</div>
				</ui-panel>
			</ui-coll>
		</ui-row>
	</div>
</template>

<script>
import UiDatepicker from '@ui/Date/Datepicker.vue';
import UiColl from '@ui/ModuleGrid/Coll.vue';
import UiRow from '@ui/ModuleGrid/Row.vue';
import UiPanel from '@ui/Panel/Panel.vue';
import UiTabsNav from '@ui/Tabs/TabsNav.vue';
import { mapGetters } from 'vuex';
import EfficiencyPlans from './EfficiencyPlans.vue';
import SalesPlans from './SalesPlans.vue';

export default {
	name: 'SettingsTeamsPlans',
	components: {
		UiDatepicker,
		UiTabsNav,
		UiColl,
		UiPanel,
		UiRow,
		EfficiencyPlans,
		SalesPlans,
	},
	data() {
		return {
			date: new Date(),
			activeTab: 'sales',
			loading: true,
		};
	},
	computed: {
		...mapGetters({
			hasSalesErrors: 'salesTeamsPlans/hasSalesErrors',
			hasEfficiencyErrors: 'salesTeamsPlans/hasEfficiencyErrors',
			submitting: 'salesTeamsPlans/submitting',
		}),
		tabs() {
			return [
				{
					value: 'sales',
					label: 'Sales Plans',
					component: 'SalesPlans',
					hasWarn: this.hasSalesErrors,
				},
				{
					value: 'efficiency',
					label: 'Efficiency Plans',
					component: 'EfficiencyPlans',
					hasWarn: this.hasEfficiencyErrors,
				},
			];
		},
		showDatepicker() {
			return typeof window.flatpickr === 'function';
		},
		submitButtonDisabled() {
			return (
				this.loading ||
				this.submitting ||
				this.hasSalesErrors ||
				this.hasEfficiencyErrors
			);
		},
		submitTooltip() {
			return this.hasSalesErrors || this.hasEfficiencyErrors
				? 'Please fill all required fields'
				: undefined;
		},
		submitButtonClassName() {
			return this.submitButtonDisabled
				? 'btn-outline-default'
				: 'btn-danger';
		},
	},
	methods: {
		setActiveTab(tab) {
			this.activeTab = tab;
		},
		dateChange(newDate) {
			this.date = newDate;
			this._fetchPlans();
		},
		async _fetchPlans() {
			this.loading = true;
			return this.$store
				.dispatch('salesTeamsPlans/fetchPlans', { date: this.date })
				.finally(() => {
					this.loading = false;
				});
		},
	},
	async mounted() {
		await this._fetchPlans();
	},
};
</script>

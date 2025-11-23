<template>
	<ui-row>
		<ui-coll :span="12" :span-xl="6">
			<ui-typography variant="h4">Local Team</ui-typography>
			<sales-table
				:records="salesPlansLocal"
				@update-record="
					$store.dispatch('salesTeamsPlans/updateSales', {
						...$event,
						team: 'local',
					})
				"
			/>
		</ui-coll>
		<ui-coll :span="12" :span-xl="6">
			<ui-typography variant="h4">Local/Long Team</ui-typography>
			<sales-table
				:records="salesPlansLong"
				:is-long="true"
				@update-record="
					$store.dispatch('salesTeamsPlans/updateSales', {
						...$event,
						team: 'long',
					})
				"
			/>
		</ui-coll>
	</ui-row>
</template>

<script>
import UiColl from '@ui/ModuleGrid/Coll.vue';
import UiRow from '@ui/ModuleGrid/Row.vue';
import UiTypography from '@ui/Typography/Typography.vue';
import { mapGetters } from 'vuex';
import SalesTable from './SalesTable.vue';

export default {
	name: 'SalesPlans',
	components: { UiColl, UiRow, UiTypography, SalesTable },
	filters: {
		name({ name = '', last_name = '' }) {
			return [name, last_name].filter(Boolean).join(' ').trim();
		},
	},
	computed: {
		...mapGetters({
			salesPlansLocal: 'salesTeamsPlans/salesPlansLocal',
			salesPlansLong: 'salesTeamsPlans/salesPlansLong',
		}),
	},
};
</script>

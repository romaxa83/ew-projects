<template>
	<ui-row>
		<ui-coll :span="12" :span-xl="6">
            <ui-typography variant="h4">Local Team</ui-typography>
			<efficiency-values
                id="efficiency-local-team"
				:value="local"
				@change-value="
					$store.dispatch('salesTeamsPlans/updateEfficiency', {
						team: 'local',
						value: $event.value,
					})
				"
			/>
		</ui-coll>
		<ui-coll :span="12" :span-xl="6">
            <ui-typography variant="h4">Local/Long Team</ui-typography>
			<efficiency-values
                id="efficiency-long-team"
				:value="long"
				@change-value="
					$store.dispatch('salesTeamsPlans/updateEfficiency', {
						team: 'long',
						value: $event.value,
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
import EfficiencyValues from './EfficiencyValues.vue';

export default {
	name: 'EfficiencyPlans',
	components: {UiTypography, UiColl, UiRow, EfficiencyValues },
	filters: {
		name({ name = '', last_name = '' }) {
			return [name, last_name].filter(Boolean).join(' ').trim();
		},
	},
	computed: {
		...mapGetters({
			local: 'salesTeamsPlans/efficiencyLocal',
			long: 'salesTeamsPlans/efficiencyLong',
		}),
	},
};
</script>

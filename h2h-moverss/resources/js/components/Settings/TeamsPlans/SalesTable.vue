<template>
	<table class="table table-bordered table-hover table-striped w-100">
		<thead>
			<tr>
				<th width="33.3%">Sales</th>
				<th>Plan, $ local</th>
				<th v-if="isLong" width="33.3%">Plan, $ Interstate</th>
			</tr>
		</thead>
		<tbody v-if="records.length === 0">
			<tr>
				<td :colspan="isLong ? '3' : '2'" class="text-center">
					No data
				</td>
			</tr>
		</tbody>
		<tbody v-else>
			<tr
				v-for="(record, index) in records"
				:key="`${index}::${record.employee_id}`"
			>
				<td>{{ record | name }}</td>
				<td class="cell-with-input">
					<input
						type="text"
						class="form-control"
						placeholder="0"
						:value="record.local"
						@input="
							$emit('update-record', {
								index,
								type: 'local',
								value: $event.target.value || null,
							})
						"
					/>
					<div
						v-if="hasErrorLocal(record)"
						class="help-block text-danger"
					>
						{{ errorMsg }}
					</div>
				</td>
				<td v-if="isLong" class="cell-with-input">
					<input
                        type="text"
						class="form-control"
						placeholder="0"
						:value="record.intrestate"
						@input="
							$emit('update-record', {
								index,
								type: 'interstate',
								value: $event.target.value || null,
							})
						"
					/>
					<div
						v-if="hasErrorInterstate(record)"
						class="help-block text-danger"
					>
						{{ errorMsg }}
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</template>

<script>
import { hasSalesErrorLocal, hasSalesErrorInterstate } from '@/services/sales_teams_plans'

export default {
	name: 'SalesTable',
	props: {
		records: {
			type: Array,
			default: [],
		},
		isLong: {
			type: Boolean,
			default: false,
		},
	},
    data() {
        return {
            errorMsg: 'Value must be a positive integer number'
        }
    },
	filters: {
		name({ name = '', last_name = '' }) {
			return [name, last_name].filter(Boolean).join(' ').trim();
		}
	},
	methods: {
		hasErrorLocal: hasSalesErrorLocal,
		hasErrorInterstate: hasSalesErrorInterstate,
	},
};
</script>

<template>
	<div class="form-group">
		<label class="form-label" :for="id">Conversion Plan, %</label>
		<div style="max-width: 250px">
            <input
                type="text"
                class="form-control"
                placeholder="0"
                :id="id"
                :value="value"
                @input="$emit('change-value', { value: $event.target.value })"
            />
        </div>
		<div v-if="invalid" class="help-block text-danger">
			{{ errorMsg }}
		</div>
	</div>
</template>

<script>
import { invalidEfficiencyConversation } from '@/services/sales_teams_plans';

export default {
	name: 'EfficiencyValues',
	props: {
		id: {
			type: String,
			required: true,
		},
		value: {
			type: [Number, String, null],
		},
	},
	data() {
		return { errorMsg: 'Value must be a integer number, in range 0 - 100' };
	},
	computed: {
		invalid() {
			return invalidEfficiencyConversation(this.value);
		},
	},
    methods: {
        invalid2(x) {
            console.log(x);
            return false;
        }
    }
};
</script>

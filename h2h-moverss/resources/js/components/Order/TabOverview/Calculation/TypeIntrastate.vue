<template>
	<div>
		<div class="row mb-2">
			<div class="col">
				<div class="form-group mb-0">
					<label for="intrastate_value" class="form-label"
						>Rate per 100 lbs</label
					>
					<div class="input-group">
						<div class="input-group-prepend">
							<label
								for="intrastate_value"
								class="input-group-text"
							>
								<i class="fas fa-dollar-sign"></i>
							</label>
						</div>
						<input
							v-if="canManage"
							id="intrastate_value"
							v-model="Rate"
							@change="inputChanged"
							type="text"
							:class="{ 'bg-transparent': !RateIsAuto }"
							class="onChanged form-control border-left-0"
							placeholder="Rate"
							:disabled="!!RateIsAuto || !!is_locked"
						/>
						<div v-else class="form-control">
							{{ Rate || '0' }}
						</div>
						<div v-if="canManage" class="input-group-append">
							<div class="input-group-text">
								<div
									class="custom-control d-flex custom-switch"
								>
									<input
										v-model="RateIsAuto"
										id="intrastate_auto"
										type="checkbox"
										class="custom-control-input"
										:disabled="!!is_locked"
									/>
									<label
										class="custom-control-label fw-500"
										for="intrastate_auto"
										>Auto</label
									>
								</div>
							</div>
						</div>
					</div>
					<span v-if="canManage" class="help-block"
						>Auto: {{ rate_auto | currencyFilter }}/100 lbs</span
					>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col">
				<div class="form-group mb-0">
					<label for="MovingDistance" class="form-label"
						>Route length, mi</label
					>
					<div
						class="input-group"
						:style="[
							is_locked ? { 'background-color': '#f3f3f3' } : '',
						]"
					>
						<div class="input-group-prepend">
							<label
								for="MovingDistance"
								class="input-group-text"
							>
								<i class="fas fa-road"></i>
							</label>
						</div>
						<input
							v-if="canManage"
							v-model="MovingDistance"
							id="MovingDistance"
							type="text"
							@change="inputChanged"
							:class="{ 'bg-transparent': !MovingDistanceIsAuto }"
							class="onChanged form-control border-left-0"
							placeholder="Rate"
							:disabled="!!MovingDistanceIsAuto || !!is_locked"
						/>
						<div v-else class="form-control">
							{{ MovingDistance || '0' }}
						</div>
						<div v-if="canManage" class="input-group-append">
							<div class="input-group-text">
								<div
									class="custom-control d-flex custom-switch"
								>
									<input
										v-model="MovingDistanceIsAuto"
										id="calculated_moving_distance_is_auto"
										type="checkbox"
										class="custom-control-input"
										:disabled="!!is_locked"
									/>
									<label
										class="custom-control-label fw-500"
										for="calculated_moving_distance_is_auto"
										>Auto</label
									>
								</div>
							</div>
						</div>
					</div>
					<span v-if="canManage" class="help-block"
						>Auto:
						{{
							this.moving_distance_auto
								? this.moving_distance_auto
								: 'n/a'
						}}
						miles</span
					>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';

export default {
	name: 'TypeIntrastate',
	filters: {
		currencyFilter,
	},
	props: {
		is_auto: {
			required: true,
		},
		is_locked: {
			type: [Number, Boolean],
		},
		moving_distance: {
			required: true,
		},
		moving_distance_auto: {
			required: true,
		},
		moving_distance_is_auto: {
			required: true,
		},
		rate: {
			required: true,
		},
		rate_auto: {
			required: true,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		MovingDistance: {
			get() {
				return this.moving_distance;
			},
			set(value) {
				this.$emit('update:moving_distance', value);
			},
		},
		MovingDistanceIsAuto: {
			get() {
				return this.moving_distance_is_auto;
			},
			set(value) {
				this.$emit('update:moving_distance_is_auto', value ? 1 : 0);
			},
		},
		Rate: {
			get() {
				console.log('Rate', this.rate);
				return this.rate;
			},
			set(value) {
				this.$emit('update:rate', value);
			},
		},
		RateIsAuto: {
			get() {
				return this.is_auto;
			},
			set(value) {
				this.$emit('update:is_auto', value ? 1 : 0);
			},
		},
	},
	mounted() {
		// Тригерим обновы если нет рейта
		this.$nextTick(() => {
			// input-group borders on focus
			initApp.appForms('.input-group', 'has-length', 'has-disabled');

			// $('#intrastate_value').find('input[name="qty"]').each(function () {
			Inputmask({
				alias: 'numeric',
				digits: 2,
				min: 0,
				digitsOptional: true,
				clearMaskOnLostFocus: false,
				placeholder: '0',
				rightAlign: false,
				allowMinus: false,
			}).mask($('#intrastate_value')[0]);

			// if(!this.rate_auto) {
			//     this.RateIsAuto = true
			// }
		});
	},
	methods: {
		inputChanged(e) {
			this.$emit('inputChanged', e);
		},
	},
};
</script>

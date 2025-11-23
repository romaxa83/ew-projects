<template>
	<div>
		<div class="row mb-3">
			<div class="col">
				<div class="form-group">
					<label for="interstate_value" class="form-label"
						>Rate per 1 cbFt</label
					>
					<div
						class="input-group"
						:style="[
							is_locked ? { 'background-color': '#f3f3f3' } : '',
						]"
					>
						<div class="input-group-prepend">
							<label
								for="interstate_value"
								class="input-group-text"
							>
								<i class="fas fa-dollar-sign"></i>
							</label>
						</div>
						<input
							v-if="canManage"
							id="interstate_value"
							v-model="Rate"
							@change="inputChanged"
							type="text"
							:class="{ 'bg-transparent': !RateIsAuto }"
							class="onChanged form-control border-left-0 numeric-inputmask"
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
										id="interstate_auto"
										type="checkbox"
										class="custom-control-input"
										:disabled="!!is_locked"
									/>
									<label
										class="custom-control-label fw-500"
										for="interstate_auto"
										>Auto</label
									>
								</div>
							</div>
						</div>
					</div>
					<span v-if="canManage" class="help-block"
						>Auto: {{ rate_auto | currencyFilter }}/per 1 cbFt</span
					>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="col">
				<label for="interstate_packing" class="form-label"
					>Packing</label
				>
				<div
					class="input-group"
					:style="[
						is_locked ||
						(estimate_rate === 'consolidated' && RateIsAuto)
							? { 'background-color': '#f3f3f3' }
							: '',
					]"
				>
					<div class="input-group-prepend">
						<label
							for="interstate_packing"
							class="input-group-text"
						>
							<i class="fas fa-dollar-sign"></i>
						</label>
					</div>
					<input
						v-if="canManage"
						id="interstate_packing"
						v-model="Packing"
						@change="inputChanged"
						type="text"
						class="onChanged form-control border-left-0 numeric-inputmask"
						placeholder="Packing"
						:disabled="
							!!is_locked ||
							(estimate_rate === 'consolidated' && !!RateIsAuto)
						"
					/>
					<div v-else class="form-control">
						{{ Packing || '0' }}
					</div>
				</div>
			</div>
			<div class="col">
				<label for="interstate_unpacking" class="form-label"
					>Unpacking</label
				>
				<div
					class="input-group"
					:style="[
						is_locked ||
						(estimate_rate === 'consolidated' && RateIsAuto)
							? { 'background-color': '#f3f3f3' }
							: '',
					]"
				>
					<div class="input-group-prepend">
						<label
							for="interstate_unpacking"
							class="input-group-text"
						>
							<i class="fas fa-dollar-sign"></i>
						</label>
					</div>
					<input
						v-if="canManage"
						id="interstate_unpacking"
						v-model="Unpacking"
						@change="inputChanged"
						type="text"
						class="onChanged form-control border-left-0 pl-0 numeric-inputmask"
						placeholder="Unpacking"
						:disabled="
							!!is_locked ||
							(estimate_rate === 'consolidated' && !!RateIsAuto)
						"
					/>
					<div v-else class="form-control">
						{{ Unpacking || '0' }}
					</div>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="col">
				<label class="form-label">Shuttle</label>
				<div
					v-if="canManage"
					class="custom-control d-flex custom-switch"
				>
					<input
						type="checkbox"
						class="onChanged custom-control-input"
						id="shuttle-pickup"
						v-model="ShuttlePickup"
						@change="inputChanged"
						:disabled="!!is_locked"
					/>
					<label
						class="custom-control-label fw-500"
						for="shuttle-pickup"
						>Pickup</label
					>
				</div>
				<div v-else>
					{{ `Pickup: ${ShuttlePickup ? 'Yes' : 'No'}` }}
				</div>
			</div>
			<div class="col">
				<label class="form-label">Shuttle</label>
				<div
					v-if="canManage"
					class="custom-control d-flex custom-switch"
				>
					<input
						type="checkbox"
						class="onChanged custom-control-input"
						id="shuttle-delivery"
						v-model="ShuttleDelivery"
						@change="inputChanged"
						:disabled="!!is_locked"
					/>
					<label
						class="custom-control-label fw-500"
						for="shuttle-delivery"
						>Delivery</label
					>
				</div>
				<div v-else>
					{{ `Delivery: ${ShuttleDelivery ? 'Yes' : 'No'}` }}
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label for="interstate_delivery_days" class="form-label"
					>Delivery days</label
				>
				<input
					v-if="canManage"
					id="interstate_delivery_days"
					v-model="DeliveryDays"
					type="text"
					:class="{ 'bg-transparent': !is_locked }"
					class="form-control"
					placeholder="1-10 business days"
					:disabled="!!is_locked"
				/>
				<div v-else class="form-control">
					{{ DeliveryDays || 'Not specified' }}
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';

export default {
	name: 'TypeInterstate',
	filters: {
		currencyFilter,
	},
	props: {
		delivery_days: {
			required: true,
		},
		estimate_rate: {
			required: true,
		},
		is_auto: {
			required: true,
		},
		is_locked: {
			type: [Number, Boolean],
		},
		packing: {
			required: true,
		},
		rate: {
			required: true,
		},
		rate_auto: {
			required: true,
		},
		shuttle_delivery: {
			required: true,
		},
		shuttle_pickup: {
			required: true,
		},
		unpacking: {
			required: true,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		DeliveryDays: {
			get() {
				return this.delivery_days;
			},
			set(value) {
				this.$emit('update:delivery_days', value);
			},
		},
		Packing: {
			get() {
				return this.packing;
			},
			set(value) {
				this.$emit('update:packing', value);
			},
		},
		Rate: {
			get() {
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
		ShuttleDelivery: {
			get() {
				return this.shuttle_delivery;
			},
			set(value) {
				this.$emit('update:shuttle_delivery', value ? 1 : 0);
			},
		},
		ShuttlePickup: {
			get() {
				return this.shuttle_pickup;
			},
			set(value) {
				this.$emit('update:shuttle_pickup', value ? 1 : 0);
			},
		},
		Unpacking: {
			get() {
				return this.unpacking;
			},
			set(value) {
				this.$emit('update:unpacking', value);
			},
		},
	},
	mounted() {
		this.$nextTick(() => {
			// input-group borders on focus
			initApp.appForms('.input-group', 'has-length', 'has-disabled');
			$('.numeric-inputmask').each(function () {
				Inputmask({
					alias: 'numeric',
					digits: 2,
					min: 0,
					digitsOptional: true,
					clearMaskOnLostFocus: false,
					placeholder: '0',
					rightAlign: false,
					allowMinus: false,
				}).mask(this);
			});
			// Inputmask({
			//     alias: 'numeric',
			//     digits: 2,
			//     min: 1,
			//     digitsOptional: true,
			//     clearMaskOnLostFocus: false,
			//     placeholder: '0',
			//     rightAlign: false,
			//     allowMinus: false
			// }).mask($('#interstate_value')[0]);
			// Тригерим обновы если нет рейта
			// if (!this.rate_auto) {
			//     this.RateIsAuto = true;
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

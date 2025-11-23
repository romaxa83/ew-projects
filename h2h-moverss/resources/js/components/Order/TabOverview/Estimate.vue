<template>
	<div class="panel block-estimate" id="estimate">
		<div class="panel-hdr">
			<h2>
				Estimate
				<span
					v-show="updating"
					class="spinner-border spinner-border-sm ml-1"
					role="status"
					aria-hidden="true"
				></span>
			</h2>
			<button
				v-if="canManage"
				@click="recalculate"
				class="btn btn-icon btn-sm btn-warning mr-1 shadow-0 waves-effect waves-themed"
			>
				<i class="fas fa-sync"></i>
			</button>
			<div class="panel-toolbar ml-2">
				<h5 class="m-0">
					<span
						v-if="calculatedTotal"
						:class="{
							'bg-danger-800': processing,
							'bg-success-500': !processing,
						}"
						class="badge badge-info fs-xl fw-600 l-h-n"
					>
						<span
							v-show="processing"
							class="spinner-border spinner-border-sm"
							role="status"
							aria-hidden="true"
						></span>
						{{ calculatedTotal.value }}
					</span>
				</h5>
			</div>
		</div>
		<div class="panel-container show">
			<div class="panel-content px-1">
				<div class="container">
					<div class="row">
						<div class="col">
							<div class="form-group mb-0">
								<label for="estimate.type" class="form-label"
									>Move type</label
								>
								<select
									v-if="canManage"
									v-model="type"
									class="form-control"
									id="estimate.type"
								>
									<option
										v-for="(v, k) in moveTypes"
										:key="k"
										v-bind:value="k"
									>
										{{ v.title }}
									</option>
								</select>
								<div v-else class="form-control">
									{{ currentMoveType }}
								</div>
							</div>
						</div>
						<div class="col">
							<div class="form-group mb-0">
								<label class="form-label" for="estimate_rate"
									>Rate type</label
								>
								<select
									v-if="canManage"
									v-model="EstimateRate"
									class="form-control"
									id="estimate_rate"
								>
									<option
										v-for="v in estimate_rate[type]"
										:key="v.key"
										v-bind:value="v.key"
									>
										{{ v.title }}
									</option>
								</select>
								<div v-else class="form-control">
									{{ currentRate }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';
import { mapGetters } from 'vuex';

let order_id = document.getElementById('order_id').textContent;

export default {
	name: 'OrderEstimate',
	filters: {
		currencyFilter,
	},
	props: {
		moveTypes: {
			type: Object,
			required: true,
		},
		processing: {
			type: Boolean,
			required: true,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			updating: false,
			estimate_rate: {
				local: [
					{
						key: 'hourly',
						title: 'Hourly',
					},
				],
				intrastate: [
					{
						key: 'by_weight',
						title: 'By weight',
					},
				],
				interstate: [
					{
						key: 'expedited',
						title: 'Expedited',
					},
					{
						key: 'consolidated',
						title: 'Consolidated',
					},
				],
			},
		};
	},
	computed: {
		EstimateRate: {
			get() {
				return this.getEstimateRate(this.estimate.type);
			},
			set(value) {
				this.sentUpdate({
					estimate_rate: value,
				});
			},
		},
		type: {
			get() {
				return this.estimate.type;
			},
			set(value) {
				this.sentUpdate({
					type: value,
					estimate_rate: this.getEstimateRate(value),
				});
			},
		},
		currentMoveType() {
			const move = this.moveTypes[this.type];
			return move?.title || 'None';
		},
		currentRate() {
			const rates = this.estimate_rate[this.type] || [];
			const rate = rates.find((r) => r.key === this.EstimateRate);
			return rate?.title || 'None';
		},
		...mapGetters({
			estimate: 'order/estimate',
			calculatedTotal: 'order/getCalculatedTotal',
		}),
	},
	methods: {
		recalculate() {
			this.$emit('recalculate');
		},
		// Костыль чтоб апдейт смог пройти
		getEstimateRate(type) {
			let res;
			if (type === 'local') res = 'hourly';
			else if (type === 'intrastate') res = 'by_weight';
			else if (type === 'interstate')
				res = this.estimate.interstate
					? this.estimate.interstate.estimate_rate
					: 'expedited';

			return res;
		},
		sentUpdate(data) {
			this.updating = true;
			data = {
				order_id,
				type: this.type,
				estimate_rate: this.EstimateRate,
				...data,
			};

			return axios
				.post('/orders/estimates/set-type', data)
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store
							.dispatch(
								'order/updateEstimate',
								resp.data.estimate
							)
							.then(() => {
								this.$parent.bindReInitEstimate();
								// this.recalculate();
							});
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				})
				.finally(() => (this.updating = false));
		},
	},
};
</script>

<template>
	<div class="card mb-3" :class="{ active: record.checked.checked }">
		<div class="card-body p-3">
			<div class="row mb-2">
				<div class="col-6 pl-2">
					<div
						v-if="canManage"
						class="custom-control custom-checkbox"
						:data-filter-tags="clearTitle"
					>
						<input
							type="checkbox"
							class="custom-control-input"
							:id="'mat-' + record.id"
							v-model="record.checked.checked"
						/>
						<label
							class="custom-control-label"
							:for="'mat-' + record.id"
							>{{ record.title }}</label
						>
					</div>
					<div v-else>{{ record.title }}</div>
					<span v-if="record.notes" class="help-block">{{
						record.notes
					}}</span>
				</div>
				<div class="col-3 px-1">
					<div v-if="canManage" class="input-group number-no-arrows">
						<div class="input-group-prepend">
							<button
								@click="itemDecrease"
								class="btn btn-xs px-2 border-top-right-radius-0 border-bottom-right-radius-0 extra-item-qty-sub btn-info waves-effect waves-themed fw-700 fs-sm"
								type="button"
							>
								-
							</button>
						</div>
						<input
							type="number"
							class="form-control input-xs inventory-change-control bg-transparent px-1 border-top-0 border-bottom-0 text-center"
							v-model.number="record.qty"
							@change="manualQty($event.target.value)"
							placeholder="1"
						/>
						<div class="input-group-append">
							<button
								@click="itemReduce"
								class="btn btn-xs px-2 border-top-left-radius-0 border-bottom-left-radius-0 extra-item-qty-add btn-info waves-effect waves-themed fw-700 fs-sm"
								type="button"
							>
								+
							</button>
						</div>
					</div>
					<div v-else>{{ record.qty }}</div>
				</div>
				<div class="col-3 text-right pr-2 fw-500">
					{{ record.price | currencyFilter }}
				</div>
			</div>
			<div v-if="record.need_packing" class="row mb-1">
				<div class="col-9 pl-2">
					<div
						v-if="canManage"
						class="custom-control custom-checkbox"
					>
						<input
							type="checkbox"
							class="custom-control-input"
							:id="'mat-packing-' + record.id"
							v-model="record.checked.packing_checked"
						/>
						<label
							class="custom-control-label"
							:for="'mat-packing-' + record.id"
						>
							Packing <i class="fas fa-inbox-in"></i>
						</label>
					</div>
					<div v-else-if="record.checked.packing_checked">
						Packing <i class="fas fa-inbox-in"></i>
					</div>
				</div>
				<div
					v-if="canManage || record.checked.packing_checked"
					class="col-3 text-right pr-2 fw-500"
				>
					{{ record.packing_price | currencyFilter }}
				</div>
			</div>
			<div v-if="record.need_unpacking" class="row mb-1">
				<div class="col-9 pl-2">
					<div
						v-if="canManage"
						class="custom-control custom-checkbox"
					>
						<input
							type="checkbox"
							class="custom-control-input"
							:id="'mat-unpacking-' + record.id"
							v-model="record.checked.unpacking_checked"
						/>
						<label
							class="custom-control-label"
							:for="'mat-unpacking-' + record.id"
						>
							Unpacking <i class="fas fa-inbox-out"></i>
						</label>
					</div>
					<div v-else-if="record.checked.unpacking_checked">
						Unpacking <i class="fas fa-inbox-out"></i>
					</div>
				</div>
				<div
					v-if="canManage || record.checked.unpacking_checked"
					class="col-3 text-right pr-2 fw-500"
				>
					{{ record.unpacking_price | currencyFilter }}
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';

export default {
	name: 'Item',
	filters: {
		currencyFilter,
	},
	props: {
		record: {
			type: Object,
			required: true,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		clearTitle() {
			console.log(this.record);
			return this.record.title.replace(/[\d+\.,]/g, '').toLowerCase();
		},
	},
	watch: {
		'record.checked.checked': function (val, oldVal) {
			// Анчек услуг
			if (
				!val &&
				oldVal &&
				(this.record.checked.packing_checked ||
					this.record.checked.unpacking_checked)
			) {
				this.record.checked.packing_checked = false;
				this.record.checked.unpacking_checked = false;
			}
		},
		'record.checked.packing_checked': function (val, oldVal) {
			// Включаем основную услугу
			if (val && !oldVal && !this.record.checked.checked) {
				this.record.checked.checked = true;
			}
		},
		'record.checked.unpacking_checked': function (val, oldVal) {
			// Включаем основную услугу
			if (val && !oldVal && !this.record.checked.checked) {
				this.record.checked.checked = true;
			}
		},
	},
	methods: {
		itemDecrease() {
			this.record.checked.checked = true;
			this.$emit('itemDecrease', this.record.id);
		},
		itemReduce() {
			this.record.checked.checked = true;
			this.$emit('itemReduce', this.record.id);
		},
		manualQty(value) {
			this.record.checked.checked = true;
			this.$emit('manualQty', this.record.id, value);
		},
	},
};
</script>

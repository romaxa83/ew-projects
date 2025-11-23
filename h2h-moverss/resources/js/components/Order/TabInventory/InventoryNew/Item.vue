<template>
	<div class="dd3-content pb-2">
		<div v-if="v.is_section" class="px-3 d-flex item-data">
			<div class="px-3 flex-grow-1">
				<vue-typeahead-bootstrap
					v-model="v.title"
					:disabled="!canManage"
					placeholder="Press enter to create a custom room name"
					:inputClass="`rounded-0 border-left-0 border-right-0 border-top-0 bg-transparent pl-0 pb-0 inventory-room el_${index}`"
					:data="v.autocompleteData"
					:serializer="(item) => item.title"
					:min-matching-chars="1"
					:max-matches="30"
					@hit="autocompleteOnSelect"
					@keyup.enter="autocompleteOnSelect($event, true)"
					@input="autocompleteQueryRoom"
					@focus="onFocus"
					@blur="onBlur"
				/>
			</div>
		</div>
		<div v-else class="px-3 d-flex item-data">
			<div style="margin-top: 7px" class="mr-3">
				<input-number
					v-model:value="qty"
					:min="1"
					:max="200"
					:controls="true"
					:disabled="!canManage"
					:class="`form-control text-right inventory-qty inventory-change-control bg-transparent pl-2 rounded-0 border-top-0 border-right-0 border-left-0 text-center el_${parentIndex}_${index}`"
					@pressEnter="goFocus"
					@focus="onFocus"
					@blur="onBlur"
				/>
			</div>
			<div class="flex-grow-1 mr-3">
				<vue-typeahead-bootstrap
					v-model="v.title"
					placeholder="Type to search"
					:disabled="!canManage"
					:inputClass="`rounded-0 border-left-0 border-right-0 border-top-0 bg-transparent pl-0 pb-0 el_title_${parentIndex}_${index}`"
					:data="v.autocompleteData"
					:serializer="(item) => item.title"
					:min-matching-chars="1"
					:max-matches="30"
					@hit="autocompleteOnSelect"
					@keyup.enter="autocompleteOnSelect($event, true)"
					@input="autocompleteQuery"
					@focus="onFocus"
					@blur="onBlur"
				/>
			</div>
			<div class="fix-height">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span
							class="input-group-text bg-transparent rounded-0 border-right-0 border-top-0 border-left-0 pl-1 pr-2 pb-0"
						>
							<i class="fas fa-cubes"></i>
						</span>
					</div>
					<input
						:disabled="!canManage"
						type="text"
						size="8"
						v-model="volume"
						@keyup.enter="addItem(false)"
						class="text-left form-control width-10 rounded-0 border-left-0 border-right-0 border-top-0 bg-transparent pl-0 pb-0"
						autocomplete="off"
						placeholder="0"
						@focus="onFocus"
						@blur="onBlur"
					/>
				</div>
				<div class="text-center help-block">
					Subtotal:
					<span class="subtotal-volume" v-text="totalVolume"></span>
					cuft
				</div>
			</div>
			<div class="fix-height">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span
							class="input-group-text bg-transparent rounded-0 border-right-0 border-top-0 border-left-0 pl-1 pr-2 pb-0"
						>
							<i class="fas fa-balance-scale"></i>
						</span>
					</div>
					<input
						:disabled="!canManage"
						type="text"
						size="8"
						v-model="weight"
						@keyup.enter="addItem(false)"
						class="text-left form-control inventory-change-control rounded-0 border-left-0 border-right-0 border-top-0 bg-transparent pl-0 pb-0"
						autocomplete="off"
						placeholder="0"
						@focus="onFocus"
						@blur="onBlur"
					/>
				</div>
				<div class="text-center help-block">
					Subtotal:
					<span class="subtotal-weight" v-text="totalWeight"></span>
					lb
				</div>
			</div>
			<div class="fix-height">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span
							class="input-group-text bg-transparent rounded-0 border-right-0 border-top-0 border-left-0 pl-1 pr-2 pb-0"
						>
							<i class="fas fa-dollar-sign"></i>
						</span>
					</div>
					<input
						:disabled="!canManage"
						type="text"
						size="6"
						v-model="price"
						@keyup.enter="addItem(false)"
						class="text-left form-control rounded-0 border-left-0 border-right-0 border-top-0 bg-transparent pl-0 pb-0"
						autocomplete="off"
						placeholder="0"
						@focus="onFocus"
						@blur="onBlur"
					/>
				</div>
				<div class="text-center help-block">
					Subtotal: $
					<span class="subtotal-price" v-text="totalPrice"></span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import formatNumber from '@/filters/formatNumber.filter';
import { InputNumber } from 'ant-design-vue';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';
import Debounce from 'lodash.debounce';

export default {
	name: 'InventoryNewItem',
	components: {
		VueTypeaheadBootstrap,
		InputNumber,
	},
	filters: {
		formatNumber,
	},
	props: {
		creatingOnEnter: {
			type: Boolean,
			required: true,
		},
		index: {
			type: Number,
			required: true,
		},
		parentIndex: {
			type: Number,
			required: true,
		},
		v: {
			type: Object,
			required: true,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
		activeOperations: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			goFocusFix: false,
			debouncedUpdate: null,
			shouldUpdate: false,
		};
	},
	created() {
		this.debouncedUpdate = Debounce(() => {
			this.createOrUpdateItem();
		}, 2000);
	},
	computed: {
		isChanged() {
			// Мониторим изменения для пуша в стор
			return (
				this.v.title +
				this.v.price +
				this.v.qty +
				this.v.volume +
				this.v.weight
			);
		},
		price: {
			get() {
				return this.v.price;
			},
			set(val) {
				val = val ? val.replace(/,/g, '.') : val;
				val = parseFloat(val);

				if (val < 1) val = 1;
				else if (val >= 9999) val = 9999;

				this.v.price = this.$options.filters.formatNumber(val);
			},
		},
		qty: {
			get() {
				return this.v.qty;
			},
			set(val) {
				if (val < 1) val = 1;
				else if (val >= 200) val = 200;

				this.v.qty = val;
			},
		},
		totalPrice() {
			return this.$options.filters.formatNumber(
				this.v.qty * this.v.price
			);
		},
		totalVolume() {
			return this.$options.filters.formatNumber(
				this.v.qty * this.v.volume
			);
		},
		totalWeight() {
			return this.$options.filters.formatNumber(
				this.v.qty * this.v.weight
			);
		},
		volume: {
			get() {
				return this.v.volume;
			},
			set(val) {
				val = val ? val.replace(/,/g, '.') : val;
				val = parseFloat(val);

				if (val < 1) val = 1;
				else if (val >= 99999) val = 99999;

				this.v.volume = this.$options.filters.formatNumber(val);
			},
		},
		weight: {
			get() {
				return this.v.weight;
			},
			set(val) {
				val = val ? val.replace(/,/g, '.') : val;
				val = parseFloat(val);

				if (val < 1) val = 1;
				else if (val >= 999999) val = 999999;

				this.v.weight = this.$options.filters.formatNumber(val);
			},
		},
	},
	watch: {
		isChanged(newVal, oldVal) {
			if (oldVal) this.shouldUpdate = true;
		},
	},
	methods: {
		updateStore(data) {
			if (!data) return;

			this.$store.commit('order/setInventoryRecords', data.inventories);
			this.$store.commit('order/setSizingVolume', data.sizing_volume);
			this.$store.commit('order/setSizingWeight', data.sizing_weight);
		},
		addItem(isFolder = false) {
			this.$emit('addItem', {
				parent_index: this.parentIndex,
				index: isFolder ? this.index : this.index + 1,
				isFolder,
				section_id: isFolder ? this.v.id : this.v.section_id,
			});
		},
		autocompleteOnSelect(e, is_enter = false) {
			if (e.hasOwnProperty('isTrusted')) {
				// prevent from number input hit
				if (this.goFocusFix) return;

				// Hit on Enter - recover value of input
				if (this.v.title_back) this.v.title = this.v.title_back;
			} else {
				this.v.title = e.title;
				this.v.title_back = e.title;
				this.v.item_id = e.id;

				if (!this.v.is_section) {
					this.v.weight = e.weight;
					this.v.volume = e.cuft;
					this.v.price = e.price;
				}
			}

			this.debouncedUpdate.flush();

			if (this.creatingOnEnter || this.v.is_section) {
				this.addItem(this.v.is_section);
			}
		},
		async autocompleteQuery(q) {
			this.goFocusFix = false;

			if (this.v.title && !q) q = this.v.title;
			await this.$emit('autocompleteQuery', {
				type: 'item',
				index: this.index,
				q,
			});
			// Save input value
			this.v.title = q;
		},
		async autocompleteQueryRoom(q) {
			await this.$emit('autocompleteQuery', {
				type: 'room',
				index: this.index,
				q,
			});

			// Save input value
			this.v.title = q;
		},
		goFocus() {
			this.goFocusFix = true;
			$(`.el_title_${this.parentIndex}_${this.index}`).focus();
            $(`.el_title_${this.parentIndex}_${this.index}`)[0]?.scrollIntoView({
                block: 'center',
                behavior: 'smooth',
            });
		},
		onFocus() {
			this.debouncedUpdate.cancel();
		},
		onBlur() {
			this.debouncedUpdate();
		},
		createOrUpdateItem() {
			if (!this.shouldUpdate) return;

			if (this.v.id === undefined) {
				this.$emit('createEntity', this.v);
				return;
			}

			const operationId = `update::${this.v.id}`;
			this.addActiveOperation(operationId);

			axios
				.post(
					`/orders/${this.v.order_id}/inventory/${this.v.id}`,
					this.v
				)
				.then((resp) => {
					if (resp.data?.success) {
						this.updateStore(resp.data.record);
					} else {
						throw {
							response: {
								data: resp.data,
							},
						};
					}
				})
				.catch(App.Forms.simpleErrors)
				.finally(() => {
					this.shouldUpdate = false;
					this.removeActiveOperation(operationId);
				});
		},
		addActiveOperation(operation) {
			const newItems = [...this.activeOperations, operation];
			this.$emit('updateActiveOperations', newItems);
		},
		removeActiveOperation(operation) {
			const newItems = this.activeOperations.filter(
				(el) => el !== operation
			);
			this.$emit('updateActiveOperations', newItems);
		},
	},
};
</script>

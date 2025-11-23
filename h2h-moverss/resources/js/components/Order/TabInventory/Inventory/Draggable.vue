<template>
	<draggable
		v-bind="dragOptions"
		class="dd-list"
		ghost-class="ghost"
		handle=".js-dd-handle"
		:list="records"
		:move="checkMove"
		@change="isAutosaveMode ? triggerChanged() : updateIsChanged(true)"
	>
		<div
			v-for="(v, i) in records"
			:key="v.randomRef"
			class="list-group-item dd-item dd3-item"
			:class="{ 'is-section': v.is_section }"
		>
			<div
				class="dd-handle dd3-handle fs-xl"
				:class="{
					'js-dd-handle': canManage,
					'dd-handle-disabled': !canManage,
				}"
			>
				<i
					class="fas"
					:class="{
						'fa-person-booth': v.is_section,
						'fa-grip-vertical': !v.is_section && canManage,
						'fa-archive': !v.is_section && !canManage,
					}"
				></i>
			</div>

			<InventoryItem
				:v="v"
				:parent-index="parentIndex"
				:index="i"
				:creating-on-enter="creatingOnEnter"
				:can-manage="canManage"
				@addItem="addItem"
				@autocompleteQuery="autocompleteQuery"
				@addItemInStart="addItemInStart"
				@updateIsChanged="updateIsChanged"
				@triggerChanged="triggerChanged"
			/>

			<div
				v-if="canManage"
				class="dd3-after fs-xl"
				@click="removeItem(i)"
			>
				<i class="color-danger-800 fas fa-times"></i>
			</div>

			<inventory-draggable
				:records="v.children"
				:parent-index="i"
				:creating-on-enter="creatingOnEnter"
				:can-manage="canManage"
				@addItem="addItem"
				@removeItem="removeItem"
				@updateIsChanged="updateIsChanged"
				@triggerChanged="triggerChanged"
				@change="triggerChanged"
			/>
		</div>
	</draggable>
</template>

<script>
import InventoryItem from '@components/Order/TabInventory/Inventory/Item';
import Debounce from 'lodash.debounce';
import draggable from 'vuedraggable';
import { mapGetters } from 'vuex';

export default {
	name: 'InventoryDraggable',
	components: {
		InventoryItem,
		draggable,
	},
	props: {
		creatingOnEnter: {
			type: Boolean,
			required: true,
		},
		parentIndex: {
			type: Number,
			default: -1,
		},
		records: {
			required: true,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		dragOptions() {
			return {
				animation: 0,
				group: 'description',
				disabled: false,
				ghostClass: 'ghost',
				emptyInsertThreshold: 15,
			};
		},
		...mapGetters({
			isAutosaveMode: 'order/inventoryIsAutosaveMode',
		}),
	},
	methods: {
		addItem: Debounce(function (obj) {
			this.$emit('addItem', obj);
		}, 100),
		addItemInStart: Debounce(function (index) {
			this.$emit('addItem', {
				parent_index: index,
				index: 0,
			});
		}, 100),
		autocompleteOnSelect(index) {
			this.addItemInStart(index);
		},
		autocompleteQuery: Debounce(async function ({ type, q, index }) {
			this.records[index].isLoading = true;

			let res = await axios.post('/orders/inventory/autocomplete', {
				division_id: $('#division_id').val(),
				type,
				q,
			});

			// Emulate new tags if not exists
			let data = res.data.data;
			let found = res.data.data
				.slice()
				.filter((item) => q.toLowerCase() == item.title.toLowerCase());

			if (!found.length) {
				data.unshift({
					id: null,
					title: q,
					price: 0,
					weight: 0,
					cuft: 0,
					children: [],
				});
			}

			this.$set(this.records[index], 'autocompleteData', data);
			this.records[index].isLoading = false;
		}, 300),
		checkMove(e) {
			// Move Item: to folder, between items
			if (
				!e.draggedContext.element.is_section &&
				(e.relatedContext.hasOwnProperty('element') ||
					$(e.to).parent().hasClass('is-section'))
			)
				return true;

			// Move folders: between root
			if (
				e.relatedContext.hasOwnProperty('element') &&
				e.draggedContext.element.is_section ==
					e.relatedContext.element.is_section
			)
				return true;

			return false;
		},
		removeItem(index) {
			let obj = {
				parent_index: this.parentIndex,
				index,
			};
			if (index instanceof Object) obj = index;

			this.$emit('removeItem', obj);
			if (this.isAutosaveMode) this.triggerChanged();
		},
		updateIsChanged(value) {
			this.$emit('updateIsChanged', value);
		},
		triggerChanged() {
			this.$emit('triggerChanged');
		},
	},
};
</script>

<style scoped>
.ghost {
	opacity: 0.5;
	background: #c8ebfb;
}

.dd-handle-disabled {
	pointer-events: none;
}
</style>

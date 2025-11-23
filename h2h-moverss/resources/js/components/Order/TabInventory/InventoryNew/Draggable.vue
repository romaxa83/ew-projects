<template>
	<draggable
		v-bind="dragOptions"
		class="dd-list"
		ghost-class="ghost"
		handle=".js-dd-handle"
		:list="records"
		:move="checkMove"
		@change="change"
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

			<InventoryNewItem
				:v="v"
				:parent-index="parentIndex"
				:index="i"
				:creating-on-enter="creatingOnEnter"
				:can-manage="canManage"
				:active-operations="activeOperations"
				@addItem="addItem"
				@autocompleteQuery="autocompleteQuery"
				@addItemInStart="addItemInStart"
				@updateActiveOperations="updateActiveOperations"
				@createEntity="createEntity"
			/>

			<div
				v-if="canManage"
				class="dd3-after fs-xl"
				@click="removeItem(i, v.id)"
			>
				<i class="color-danger-800 fas fa-times"></i>
			</div>

			<inventory-new-draggable
				:records="v.children"
				:parent-index="i"
				:creating-on-enter="creatingOnEnter"
				:can-manage="canManage"
				:active-operations="activeOperations"
				@addItem="addItem"
				@removeItem="removeItem"
				@updateActiveOperations="updateActiveOperations"
				@triggerChanged="change"
				@createEntity="createEntity"
			/>
		</div>
	</draggable>
</template>

<script>
import InventoryNewItem from './Item.vue';
import Debounce from 'lodash.debounce';
import draggable from 'vuedraggable';

export default {
	name: 'InventoryNewDraggable',
	components: {
		InventoryNewItem,
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
		activeOperations: {
			type: Array,
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
			) {
				return true;
			}

			// Move folders: between root
			if (
				e.relatedContext.hasOwnProperty('element') &&
				e.draggedContext.element.is_section ==
					e.relatedContext.element.is_section
			) {
				return true;
			}

			return false;
		},
		removeItem(index, item_id) {
			let obj = {
				parent_index: this.parentIndex,
				index,
				item_id,
			};
			if (index instanceof Object) obj = index;

			this.$emit('removeItem', obj);
		},
		updateActiveOperations(operations) {
			this.$emit('updateActiveOperations', operations);
		},
		change(event) {
			this.$emit('triggerChanged', event);
		},
		createEntity(...args) {
			this.$emit('createEntity', ...args);
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

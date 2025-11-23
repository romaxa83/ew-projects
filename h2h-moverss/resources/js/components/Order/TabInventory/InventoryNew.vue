<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>
				Inventory&nbsp;<i class="red">NEW</i>
				<span
					v-show="processing"
					class="spinner-border spinner-border-sm ml-1"
					role="status"
					aria-hidden="true"
				></span>
			</h2>
			<div
				v-if="canManage"
				class="custom-control custom-checkbox custom-control-inline"
			>
				<input
					type="checkbox"
					class="custom-control-input"
					id="creating_on_enter_new"
					v-model="creating_on_enter"
				/>
				<label class="custom-control-label" for="creating_on_enter_new">
					Add New Item on Enter
				</label>
			</div>
			<div v-if="canManage" class="panel-toolbar">
				<button
					class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
					@click="addRoom"
				>
					Add room
				</button>
			</div>
			<div v-if="canManage" class="panel-toolbar ml-1">
				<button
					class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
					@click="addItem"
				>
					Add item
				</button>
			</div>
		</div>
		<div class="panel-container collapse show">
			<div class="panel-content pt-2">
				<div class="dd nestable-inventory">
					<ol class="dd-list"></ol>
				</div>

				<div class="inventory-items">
					<inventory-new-draggable
						:records="records"
						:creating-on-enter="creating_on_enter"
						:can-manage="canManage"
						:active-operations="activeOperations"
						@addItem="addItem"
						@removeItem="removeItem"
						@updateActiveOperations="updateActiveOperations"
						@triggerChanged="triggerChanged"
						@createEntity="createEntity"
					/>
				</div>
				<div v-if="canManage" class="mt-2 ml-5">
					<button
						class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
						@click="addRoom"
					>
						Add room
					</button>
					<button
						class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
						@click="addItem"
					>
						Add item
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import 'ant-design-vue/lib/input-number/style/index.css';
import InventoryNewDraggable from './InventoryNew/Draggable';
import cloneDeep from 'lodash.clonedeep';
import { mapGetters } from 'vuex';

let order_id = document.getElementById('order_id').textContent;

export default {
	name: 'TabInventoryNew',
	components: {
		InventoryNewDraggable,
	},
	props: {
		is_changed: {
			type: Boolean,
			required: false,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			loading: false,
			creating_on_enter: true,
			enabled: true,
			records: [],
			activeOperations: [],
		};
	},
	mounted() {
		this.$store
			.dispatch('getSession')
			.then(({ order }) => {
				this.formatRecords(order.inventories);
			})
			.finally(() => {
				if (window.Echo) {
					window.Echo.channel(`order.${order_id}`).listen(
						'.order.client.edit.inventory',
						(e) => {
							const response = e[0];
							this.formatRecords(response.inventories);
							this.$store.dispatch(
								'order/updateInventory',
								response
							);
							this.$store.dispatch('order/refetchChangelog');
						}
					);
				}
			});
	},
	computed: {
		...mapGetters({
			processing: 'order/inventoriesProcessing',
		}),
	},
	watch: {
		activeOperations(newValue, oldValue) {
			this.$store.commit(
				'order/setInventoryProcessing',
				!!newValue.length
			);
			if (oldValue && !newValue.length) {
				this.refetchChangelog();
			}
		},
	},
	methods: {
		checkPermission() {
			if (!this.canManage) {
				App.Forms.showAlert(
					'error',
					'Forbidden',
					'You have no permission to manage inventory'
				);
				return false;
			}
			return true;
		},
		updateStore(data) {
			if (!data) return;

			this.$store.commit('order/setInventoryRecords', data.inventories);
			this.$store.commit('order/setSizingVolume', data.sizing_volume);
			this.$store.commit('order/setSizingWeight', data.sizing_weight);
		},
		checkEmptyRecords(is_section = false) {
			const lastItem = this.records
				.filter((record) => Boolean(record.is_section) === is_section)
				.at(-1);
			if (lastItem && lastItem.title === null) {
				const focus = is_section
					? `.inventory-room.el_${lastItem.index}`
					: `.el_title_${lastItem.parent_index}_${lastItem.index}`;
				this.$nextTick(() => {
					$(`${focus}`).focus().select();
					$(`${focus}`)[0]?.scrollIntoView({
						block: 'center',
						behavior: 'smooth',
					});
				});
				return true;
			}

			return false;
		},
		createEntity({ index, parent_index, ...entity }) {
			const operationId = `create::${entity.randomRef}`;
			this.activeOperations.push(operationId);
			return axios
				.post(`/orders/${order_id}/inventory`, entity)
				.then((resp) => {
					const {
						success = false,
						record = null,
						meta = null,
					} = resp.data;

					if (!success) {
						throw {
							response: {
								data: resp.data,
							},
						};
					}

					this.updateStore(record);

					if (!meta?.inventory) return;

					const parent = this.records[parent_index];
					const child = parent ? parent.children[index] : null;
					this.$set(
						child || this.records[index],
						'id',
						meta.inventory.id
					);

					if (entity.is_section) {
						this.records[index].children?.forEach((child) => {
							this.$set(child, 'section_id', meta.inventory.id);
						});
					}
				})
				.catch(App.Forms.simpleErrors)
				.finally(() => {
					this.activeOperations = this.activeOperations.filter(
						(operation) => operation !== operationId
					);
				});
		},
		addItem({
			parent_index = -1,
			index = -1,
			isFolder = false,
			section_id = null,
		}) {
			if (!this.checkPermission()) return;
			if (
				!isFolder &&
				parent_index === -1 &&
				this.checkEmptyRecords(false)
			) {
				return;
			}

			let new_item = {
				is_section: 0,
				section_id,
				item_id: 0,
				order_id,
				price: null,
				qty: 1,
				sort: this.records.length ? this.records.at(-1).sort + 1 : 1,
				title: null,
				volume: null,
				weight: null,
				autocompleteData: [],
				randomRef: App.Miscs.generateToken(),
				index,
				parent_index,
			};
			let focus = `.el_${parent_index}_${index}`;

			if (parent_index === -1 && index === -1) {
				new_item = { ...new_item, index: this.records.length };
				this.records.push(new_item);

				focus = `.el_${new_item.parent_index}_${new_item.index}`;
			} else {
				// detect position
				if (parent_index === -1 && !isFolder) {
					this.records.splice(index, 0, new_item);
				} else if (isFolder && !this.records[parent_index]) {
					new_item = {
						...new_item,
						index: this.records[index].children.length,
						parent_index: index,
					};
					this.records[index].children.push(new_item);
					focus = `.el_${index}_0`;
				} else {
					let empty_exists =
						this.records[parent_index].children.at(-1).title ===
						null;

					if (empty_exists) return;

					this.records[parent_index].children.splice(
						index,
						0,
						new_item
					);
				}
			}

			this.$nextTick(() => {
				$(`${focus} .ant-input-number-input`).focus().select();
				$(`${focus}`)[0]?.scrollIntoView({
					block: 'center',
					behavior: 'smooth',
				});
			});
		},
		addRoom() {
			if (!this.checkPermission()) return;
			if (this.checkEmptyRecords(true)) return;

			let new_item = {
				order_id,
				is_section: 1,
				title: null,
				sort: this.records.length ? this.records.at(-1).sort + 1 : 1,
				children: [],
				autocompleteData: [],
				randomRef: App.Miscs.generateToken(),
				index: this.records.length,
			};

			this.records.push(new_item);
			this.creating_on_enter = true;

			this.$nextTick(() => $(`.el_${new_item.index}`).focus());
		},
		removeItem({ parent_index = -1, index = -1, item_id }) {
			if (!this.checkPermission()) return;

			if (parent_index === -1) this.$delete(this.records, index);
			else this.$delete(this.records[parent_index].children, index);
			if (!item_id) return;

			const operationId = `delete::${item_id}`;
			this.activeOperations.push(operationId);

			axios
				.delete(`/orders/${order_id}/inventory/${item_id}`)
				.then((res) => this.handleResponseUpdate(res))
				.catch((error) => App.Forms.simpleErrors(error.response.data))
				.finally(() => {
					this.activeOperations = this.activeOperations.filter(
						(operation) => operation !== operationId
					);
				});
		},
		formatRecords(records) {
			this.records = cloneDeep(records)
				.sort((a, b) => a.sort - b.sort)
				.map((item) => {
					item.autocompleteData = [];
					item.title_back = item.title;
					item.isLoading = false;
					item.randomRef = App.Miscs.generateToken();

					item.children
						.sort((a, b) => a.sort - b.sort)
						.map((item) => {
							item.autocompleteData = [];
							item.title_back = item.title;
							item.isLoading = false;
							item.randomRef = App.Miscs.generateToken();

							return item;
						});

					return item;
				});
		},
		updateActiveOperations(operations) {
			this.activeOperations = operations;
		},
		triggerChanged(event) {
			let sort = 0;
			const items = this.records.flatMap((r) => {
				return [
					{
						id: r.id,
						sort: sort++,
						section_id: 0,
					},
				].concat(
					(r.children || []).map((c) => ({
						id: c.id,
						sort: sort++,
						section_id: r.id,
					}))
				);
			}).filter((item) => !!item.id);
			const operationId = `sort::items`;
			this.activeOperations.push(operationId);
			axios
				.post(`/orders/${order_id}/inventory/sort`, { items })
				.then((res) => this.handleResponseUpdate(res))
				.catch(App.Forms.simpleErrors)
				.finally(() => {
					this.activeOperations = this.activeOperations.filter(
						(operation) => operation !== operationId
					);
				});
		},
		handleResponseUpdate(res) {
			if (res.data.success) {
				this.updateStore(res.data.record);
			} else {
				throw {
					response: {
						data: res.data,
					},
				};
			}
		},
		refetchChangelog() {
			this.$store.dispatch('order/refetchChangelog', 'update');
		},
	},
};
</script>

<style>
.fix-height {
	margin-top: 5px;
}

.dd-list .list-group-item-action:focus {
	background-color: #c6c8ca;
}
</style>

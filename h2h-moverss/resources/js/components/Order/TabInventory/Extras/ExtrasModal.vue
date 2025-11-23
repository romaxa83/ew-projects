<template>
	<div
		id="extras-modal-right"
		class="modal fade"
		role="dialog"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-right">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title h4">Extra</h5>
					<button
						type="button"
						class="close"
						data-dismiss="modal"
						aria-label="Close"
					>
						<span aria-hidden="true">
							<i class="fal fa-times"></i>
						</span>
					</button>
				</div>
				<div class="modal-body">
					<div
						v-if="loading"
						class="frame-wrap position-absolute w-100 h-100 opacity-50"
					>
						<div
							class="w-100 d-flex justify-content-center align-items-center"
						>
							<div
								class="spinner-border text-info position-absolute"
								style="top: 50%"
								role="status"
							>
								<span class="sr-only">Loading...</span>
							</div>
						</div>
					</div>

					<!-- В компонент не выйдет т.к. надо чтоб без изменения стейта было,
                        если поменяем стейт - после переоткрытия модалки оно получит не сохзраненную инфу а из стейта -->
					<div v-if="canManage || customRecords.length" class="panel">
						<div class="panel-hdr">
							<h2>Additional Services</h2>
						</div>
						<div class="panel-container">
							<div class="panel-content">
								<div
									v-if="canManage"
									class="input-group input-group-multi-transition mb-3 number-no-arrows"
								>
									<input
										type="text"
										class="form-control"
										aria-label="Service title"
										placeholder="Service title"
										v-model="newCustomRecord.title"
									/>
									<input
										type="number"
										class="form-control"
										aria-label="Amount"
										placeholder="Amount"
										v-model="newCustomRecord.price"
									/>
									<div class="input-group-append">
										<button
											@click="customAddRecord"
											class="btn btn-outline-default btn-primary"
											type="button"
										>
											<i class="fas fa-plus"></i>
										</button>
									</div>
								</div>

								<template v-if="customRecords.length">
									<hr v-if="canManage" />
									<div
										class="input-group input-group-multi-transition mb-3 number-no-arrows"
										v-for="(v, i) in customRecords"
										:key="v.id"
									>
										<input
											v-if="canManage"
											type="text"
											class="form-control"
											aria-label="Service title"
											placeholder="Service title"
											v-model="v.title"
										/>
										<div v-else class="form-control">
											{{ v.title }}
										</div>
										<input
											v-if="canManage"
											type="number"
											class="form-control"
											aria-label="Amount"
											placeholder="Amount"
											v-model.number="v.price"
										/>
										<div v-else class="form-control">
											{{ v.price | currencyFilter }}
										</div>
										<div
											v-if="canManage"
											class="input-group-append"
										>
											<button
												@click="customRemoveRecord(i)"
												class="btn btn-outline-default btn-danger"
												type="button"
											>
												<i class="fas fa-times"></i>
											</button>
										</div>
									</div>
								</template>
							</div>
						</div>
					</div>

					<div v-if="items.length">
						<h2 class="fs-lg fw-500 mb-3 text-dark">
							Materials & Services
						</h2>

						<div class="card mb-3" v-if="canManage">
							<div class="card-body p-3">
								<div class="row">
									<div class="col">
										<div class="input-group mb-1">
											<input
												v-model="searchValue"
												type="text"
												id="extras-modal-filter"
												class="form-control rounded-0 border-top-0 border-left-0 border-right-0 bg-transparent pl-0 pb-0"
												placeholder="Filter"
											/>
											<div class="input-group-append">
												<span
													class="input-group-text bg-transparent rounded-0 border-right-0 border-top-0 border-left-0 pr-1 pl-2 pb-0"
												>
													<i
														class="fas fa-search"
													></i>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div id="extras-modal-container">
							<Item
								v-if="items.length > 0"
								v-for="(v, i) in filteredItems"
								:key="v.id"
								:record="v"
								:index="i"
								:id="'sizes_' + v.id"
								:can-manage="canManage"
								@manualQty="manualQty"
								@itemReduce="itemReduce"
								@itemDecrease="itemDecrease"
							/>
						</div>
					</div>
				</div>
				<div class="modal-footer sticky-bottom bg-white shadow-5-top">
					<div class="d-flex w-100">
						<div class="mr-auto">
							<div v-for="total in totals" class="mb-1">
								<b>{{ total.title }}:</b>
								{{ total.value | currencyFilter }}
							</div>
						</div>
						<div>
							<button
								type="button"
								class="btn btn-secondary"
								data-dismiss="modal"
							>
								Close
							</button>
							<button
								v-if="canManage"
								@click="submit()"
								type="button"
								class="btn btn-primary"
							>
								<span
									v-show="updating"
									class="spinner-border spinner-border-sm"
									role="status"
									aria-hidden="true"
								></span>
								{{ updating ? 'Saving' : 'Save' }} changes
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';
import { AxiosHelper } from '@/helpers/axiosHelper';
import cloneDeep from 'lodash.clonedeep';
import { mapGetters } from 'vuex';

import Item from './Item';

export default {
	name: 'ExtrasModal',
	components: {
		Item,
	},
	filters: {
		currencyFilter,
	},
	props: {
		openId: {
			type: [Number],
			required: false,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			records: [],
			newCustomRecord: this.customEmptyRecord(),
			customRecords: [],
			all_materials: [],
			loading: false,
			updating: false,
			searchValueData: '',
		};
	},
	computed: {
		totals() {
			return [
				{
					title: 'Materials',
					value: this.totalMaterials,
				},
				{
					title: 'Services',
					value: this.totalServices,
				},
				{
					title: 'Additional Services',
					value: this.totalCustomServices,
				},
			];
		},
		totalMaterials() {
			return this.records.reduce(function (sum, record) {
				return (
					sum +
					(record.checked.checked
						? parseFloat(record.price) * record.qty
						: 0)
				);
			}, 0);
		},
		totalServices() {
			return this.records.reduce(function (sum, record) {
				let plus = 0;
				if (
					record.checked.checked &&
					(record.checked.packing_checked ||
						record.checked.unpacking_checked)
				) {
					if (record.checked.packing_checked)
						plus += record.packing_price * record.qty;
					if (record.checked.unpacking_checked)
						plus += record.unpacking_price * record.qty;
				}
				return sum + plus;
			}, 0);
		},
		searchValue: {
			get() {
				return this.searchValueData;
			},
			set(value) {
				this.searchValueData = value;
			},
		},
		items() {
			if (this.canManage) {
				return this.records;
			}
			return this.records.filter((v) => v.checked.checked);
		},
		filteredItems() {
			if (!this.searchValue) {
				return this.items;
			}
			return this.items.filter((v) => {
				return v.title
					.toLowerCase()
					.includes(this.searchValue.toLowerCase());
			});
		},
		...mapGetters({
			materials: 'order/materials',
			customsExtras: 'order/customsExtras',
			totalCustomServices: 'order/totalCustomExtras',
			orderID: 'getOrderId',
		}),
	},
	async mounted() {
		let el = $('#extras-modal-right');
		if (!el.data('is_moved')) {
			$('#js-page-content').append($('#extras-modal-right'));
			el.data('is_moved', true);
		}

		el.on('shown.bs.modal', () => {
			this.customRecords = cloneDeep(this.customsExtras);

			if (this.all_materials.length) this.initRecords();
		});

		this.$nextTick(() => el.modal('show'));

		this.loading = true;
		await AxiosHelper({
			url: '/orders/materials',
			data: {
				division_id: $('#division_id').val(),
			},
		})
			.then(({ records }) => {
				this.all_materials = records;
				this.initRecords();

				// window.initApp.listFilter(
				// 	$('#extras-modal-container'),
				// 	$('#extras-modal-filter')
				// );
			})
			.finally(() => (this.loading = false));
	},
	methods: {
		initRecords() {
			// Собираем инфу о том что в заказе
			let exists = {};
			if (this.materials.length)
				for (let key in this.materials) {
					let v = this.materials[key];

					exists[v.material_id] = {
						packing_checked: !!v.need_packing,
						unpacking_checked: !!v.need_unpacking,
						qty: v.qty,
						price: v.price,
						packing_price: v.packing_price,
						unpacking_price: v.unpacking_price,
					};
				}

			let all_materials = cloneDeep(this.all_materials);

			// Наливаем чекед данные
			this.records = all_materials
				.map(function (v) {
					let ex = exists.hasOwnProperty(v.id),
						obj = ex ? exists[v.id] : null;

					v.checked = {
						checked: ex,
						packing_checked: ex ? obj.packing_checked : false,
						unpacking_checked: ex ? obj.unpacking_checked : false,
					};
					if (ex) {
						v.price = obj.price;
						v.packing_price = obj.packing_price;
						v.unpacking_price = obj.unpacking_price;
					}
					v.qty = ex ? obj.qty : 1;

					return v;
				})
				.sort(function (a, b) {
					// true values first
					return a.checked.checked === b.checked.checked
						? 0
						: a.checked.checked
						? -1
						: 1;
				});

			if (this.openId) {
				this.$nextTick(() => {
					// Scroll to
					let hiddenElement = document.getElementById(
						'sizes_' + this.openId
					);
					if (hiddenElement) {
						hiddenElement.scrollIntoView({
							block: 'center',
							behavior: 'smooth',
						});
					}
				});
			}
		},
		customAddRecord() {
			if (!this.newCustomRecord.title) {
				App.Forms.showAlert(
					'warning',
					'Error',
					'Service title is required'
				);
				return;
			}
			let price = parseFloat(this.newCustomRecord.price);
			if (!price || price < 0) {
				this.newCustomRecord.price = 0;
			}

			this.customRecords.push(this.newCustomRecord);
			this.newCustomRecord = this.customEmptyRecord();
		},
		customEmptyRecord() {
			return {
				id: null,
				title: null,
				price: null,
			};
		},
		customRemoveRecord(index) {
			this.$delete(this.customRecords, index);
		},
		/**
		 * @param id
		 * @returns {Object|null}
		 */
		findRecordById(id) {
			const record = this.records.find((record) => record.id === id);
			if (record) return record;
			console.log('Record not found by id: ', id);
			return null;
		},
		itemDecrease(id) {
			const record = this.findRecordById(id);
			if (record && record.qty > 1) {
				record.qty -= 1;
			}
		},
		itemReduce(id) {
			const record = this.findRecordById(id);
			if (record && record.qty < 100) {
				record.qty += 1;
			}
		},
		manualQty(id, value) {
			const record = this.findRecordById(id);
			if (record) {
				const qty = parseInt(value);
				if (qty > 100) record.qty = 100;
				else if (qty < 1) record.qty = 1;
				else record.qty = qty;
			}
		},
		async submit() {
			this.updating = true;
			await this.$store.dispatch('order/saveMaterials', {
				records: this.records,
				custom_records: this.customRecords,
			});

			$('#extras-modal-right').modal('hide');
			this.$root.$refs.overview.recalculate();

			this.updating = false;
		},
	},
};
</script>

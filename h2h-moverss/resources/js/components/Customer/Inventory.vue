<template>
	<div class="card-body">
		<div v-if="loading" class="d-flex justify-content-center">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>

		<div class="panel-content mt-2" v-if="canManage">
			<div class="row">
				<div class="col-lg-3 mb-4">
					<div class="form-group">
						<label class="form-label" for="new_room">Room</label>
						<select
							id="new_room"
							class="form-control"
							v-model.number="newRecord.room"
						>
							<option
								v-for="(title, k) in rooms"
								:key="k"
								v-bind:value="k"
							>
								{{ title }}
							</option>
						</select>
					</div>
				</div>
				<div class="col-lg-6 mb-4">
					<div class="form-group">
						<label for="new_name" class="form-label">Name</label>
						<input
							v-model="newRecord.title"
							type="search"
							autocomplete="off"
							id="new_name"
							class="form-control"
							placeholder="Eg. cupboard, A vacuum cleaner"
						/>
					</div>
				</div>
				<div class="col-lg-auto d-flex flex-nowrap align-items-end mb-4">
					<div class="form-group mr-4 mb-0">
						<label for="new_qty" class="form-label">Qty</label>
                        <br />
						<input
							type="text"
							size="4"
							v-model.number="newRecord.qty"
							id="new_qty"
							class="form-control inventory-change-control bg-transparent pl-2 rounded-0 border-top-0 border-right-0 border-left-0 text-center"
							autocomplete="off"
							placeholder="Item qty"
						/>
					</div>
					<button @click="add()" class="btn btn-primary mt-4 mr-2">
						Add
					</button>
					<button
						type="button"
						:disabled="!is_changed"
						@click="save()"
						:class="{
							'btn-danger': is_changed,
							'btn-primary': !is_changed,
						}"
						class="mt-4 btn waves-effect waves-themed"
					>
						<span
							v-show="updating"
							class="spinner-border spinner-border-sm mr-1"
							role="status"
							aria-hidden="true"
						></span>
						Save
					</button>
				</div>
			</div>
			<hr />
		</div>

		<div class="inventory-table">
			<table v-if="!loading" class="table m-0 counter-table">
				<thead>
					<tr>
						<th>#</th>
						<th>Room</th>
						<th class="w-50">Name</th>
						<th>Qty</th>
						<th>Weight</th>
						<th>Volume</th>
						<th v-if="canManage"></th>
					</tr>
				</thead>
				<tbody>
					<template v-for="(room, i) in inventories">
						<template v-if="room.is_section">
							<tr
								v-for="(v, k) in room.children"
								:key="`${room.id}_${k}`"
							>
								<td></td>
								<td v-text="room.title"></td>
								<td v-text="v.title"></td>
								<td>
									<input
										v-if="canManage"
										type="text"
										size="4"
										v-model="v.qty"
										class="form-control inventory-change-control bg-transparent pl-2 rounded-0 border-top-0 border-right-0 border-left-0 text-center"
										autocomplete="off"
										:placeholder="
											v.qty ? v.qty : 'Item qty'
										"
									/>
									<span v-else v-text="v.qty"></span>
								</td>
								<td>{{ v.weight ? v.weight : '-' }} lb</td>
								<td>{{ v.volume ? v.volume : '-' }} Cuft</td>
								<td v-if="canManage">
									<button
										@click="removeItem(room.children, k)"
										class="btn btn-danger btn-xs btn-icon waves-effect waves-themed"
									>
										<i class="fal fa-times"></i>
									</button>
								</td>
							</tr>
						</template>
						<template v-else>
							<tr>
								<td></td>
								<td>-</td>
								<td v-text="room.title"></td>
								<td>
									<input
										v-if="canManage"
										type="text"
										size="4"
										v-model="room.qty"
										class="form-control inventory-change-control bg-transparent pl-2 rounded-0 border-top-0 border-right-0 border-left-0 text-center"
										autocomplete="off"
										:placeholder="
											room.qty ? room.qty : 'Item qty'
										"
									/>
									<span v-else v-text="room.qty"></span>
								</td>
								<td>
									{{ room.weight ? room.weight : '-' }} lb
								</td>
								<td>
									{{ room.volume ? room.volume : '-' }} Cuft
								</td>
								<td v-if="canManage">
									<button
										@click="removeItem(inventories, i)"
										class="btn btn-danger btn-xs btn-icon waves-effect waves-themed"
									>
										<i class="fal fa-times"></i>
									</button>
								</td>
							</tr>
						</template>
					</template>
				</tbody>
			</table>
		</div>
	</div>
</template>

<style>
.inventory-table {
	overflow-x: auto;
}

.ispin-wrapper input {
    border: 1px solid #e5e5e5 !important;
    border-radius: 4px !important;
    padding-right: 34px !important;
    padding-left: 4px !important;
    width: 72px !important;
}

.ispin-wrapper button {
    border-left: 1px solid #e5e5e5 !important;
}

</style>

<script>
import ISpin from 'ispin';

require('inputmask');

export default {
	name: 'CustomerInventory',
	props: {
		divisionId: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			order_id: null,
			inventories: [],
			is_changed: false,
			updating: false,
			canManage: false,
			rooms: {},
			newRecord: this.emptyRecord(),
		};
	},
	watch: {
		inventories: {
			handler: function (val, oldVal) {
				if (!this.is_changed && Object.keys(oldVal).length) {
					this.is_changed = true;
				}
			},
			deep: true,
		},
	},
	mounted() {
		this.loadData();

		if (document.getElementById('toDocument'))
			document
				.getElementById('toDocument')
				.addEventListener('click', function (e) {
					e.preventDefault();

					document
						.querySelector('#doc')
						.scrollIntoView({ behavior: 'smooth' });
				});
	},
	methods: {
		add() {
			if (!this.newRecord.title || this.newRecord.title.length <= 2) {
				Swal.fire(
					'Too short',
					'Minimum item name is 2 symbols',
					'warning'
				);
				return;
			}

			let record = {
				children: [],
				id: null,
				is_section: 0,
				order_id: this.order_id,
				section_id: this.newRecord.room,
				sort: this.inventories.length + 1,
				...this.newRecord,
			};

			if (!this.newRecord.room) {
				// root
				this.inventories.push(record);
			} else {
				// Пытаемся найти комнату, если нет, создаем ее
				let groupIndex = this.inventories.find(
					(item) => item.id === this.newRecord.room
				);

				if (groupIndex === undefined) {
					// Создаем комнату из тех что были в списке ранее
					let newGroup = {
						id: this.newRecord.room,
						section_id: 0,
						is_section: 1,
						item_id: null,
						title: this.rooms[this.newRecord.room],
						price: null,
						qty: null,
						weight: null,
						volume: null,
						sort: this.inventories.length + 1,
						type: 'room',
						children: [],
					};
					this.inventories.push(newGroup);
				}

				this.inventories.forEach((item) => {
					if (item.id === this.newRecord.room) {
						record['sort'] = item.children.length + 1;
						item.children.push(record);
					}
				});
			}

			setTimeout(() => {
				this.qtyFilter();
			});
			this.newRecord = this.emptyRecord();
		},
		emptyRecord() {
			return {
				room: 0,
				title: null,
				qty: 1,
				volume: 0,
				weight: 0,
				price: 0,
				itemId: 0,
				type: 'item',
			};
		},
		initTypeahead() {
			let el = $('#new_name'),
				vm = this;

			el.data('bound', true);
			el.blur();

			function makeOption(item, title = item.title) {
				return `<div role="option"><span class="title">${title}</span> <span class="text-muted">${item.cuft} cuft, ${item.weight} lb</span></div>`;
			}

			el.typeahead({
				matcher: function () {
					return true;
				},
				source: function (q, process) {
					$.ajax({
						dataType: 'json',
						method: 'post',
						url: window.location.href + '/inventories/autocomplete',
						data: {
							type: 'item',
							division_id: vm.divisionId,
							q,
						},
					}).done(function (data) {
						$.each(data.data, function () {
							this.name = this.title;
						});

						process(data.data);
					});
				},
				highlighter: function (displayText, item) {
					if (this.query.length < 2) return displayText;
					let query = this.query.replace(
						/[\-\[\]{}()*+?.,\\\^$|#\s]/g,
						'\\$&'
					);

					const title = item.title.replace(
						new RegExp('(' + query + ')', 'ig'),
						function ($1, match) {
							return (
								'<mark class="px-0" style="background: #f6f6319f">' +
								match +
								'</mark>'
							);
						}
					);
					return makeOption(item, title);
				},
				displayText: function (item) {
					return makeOption(item);
				},
				minLength: 1,
				afterSelect: function (item) {
					vm.newRecord.title = item.title;
					vm.newRecord.itemId = item.id;
					vm.newRecord.volume = item.cuft;
					vm.newRecord.weight = item.weight;
					vm.newRecord.price = item.price;
				},
				items: 15,
				autoSelect: false,
				fitToElement: true,
			});
		},
		loadData() {
			axios
				.get(window.location.href + '/inventories')
				.then((resp) => {
					if (resp.data.success === true) {
						this.order_id = resp.data.order.id;
						this.canManage = resp.data.order.can_manage;
						this.mapRecords(resp.data.order.inventories);
						this.rooms = resp.data.types.rooms;

						setTimeout(() => {
							this.qtyFilter();
							this.initTypeahead();
						});
					} else {
						throw {
							response: {
								data: resp.data,
							},
						};
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data); // FIXME похоже нет доступа
				})
				.finally(() => (this.loading = false));
		},
		mapRecords(records) {
			records.forEach((item) => {
				item.orig_id = item.id;
				item.type = item.is_section ? 'room' : 'item';
				item.children.forEach((child) => {
					child.orig_id = child.id;
					child.type = 'item';
				});
			});

			this.inventories = records;
		},
		qtyFilter() {
			$('.inventory-change-control:not(.bound)').each(function () {
				$(this).addClass('bound');

				new ISpin(this, {
					wrapperClass: 'ispin-wrapper',
					buttonsClass: 'ispin-button',
					step: 1,
					max: 999,
					min: 1,
					pageStep: 10,
					disabled: false,
					repeatInterval: 200,
					// wrapOverflow: false,
					parse: Number,
					format: String,
					onChange: () => {
						$(this)[0].dispatchEvent(
							new Event('input', { bubbles: true })
						);
					},
				});

				Inputmask({
					alias: 'numeric',
					digits: 0,
					min: 1,
					max: 9999,
					digitsOptional: true,
					clearMaskOnLostFocus: false,
					// placeholder: '1',
					allowMinus: false,
				}).mask(this);

				// Отрубаем tabindex
				$(this)
					.closest('.ispin-wrapper')
					.find('.ispin-button')
					.attr('tabindex', -1);
			});
		},
		removeItem(obj, index) {
			Swal.fire({
				title: 'Are you sure?',
				text: 'Remove this item',
				icon: 'warning',
				showCancelButton: true,
				reverseButtons: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, do it!',
			}).then((result) => {
                if (!result.value) return;

                if (!this.is_changed && obj[index]?.orig_id) {
                    this.is_changed = true;
                }
                this.$delete(obj, index);
			});
		},
		save() {
			this.updating = true;
			axios
				.post(window.location.href + '/inventories/save', {
					order_id: this.order_id,
					records: this.inventories,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.mapRecords(resp.data.record.inventories);
						setTimeout(() => (this.is_changed = false));
					} else {
						throw {
							response: {
								data: resp.data,
							},
						};
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data); // FIXME похоже нет доступа
				})
				.finally(() => (this.updating = false));
		},
	},
};
</script>

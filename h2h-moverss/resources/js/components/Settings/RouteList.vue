<template>
	<div>
		<div class="d-flex">
			<div class="flex-grow-1">
				<ul role="tablist" class="nav nav-tabs nav-tabs-clean">
					<li class="nav-item">
						<a
							data-toggle="tab"
							href="#tab-information"
							role="tab"
							aria-selected="true"
							class="nav-link active"
							>Information</a
						>
					</li>
					<!--                    <li class="nav-item">-->
					<!--                        <a data-toggle="tab" href="#tab-calendar" role="tab" aria-selected="false" class="nav-link">-->
					<!--                            Truck Busy-->
					<!--                        </a>-->
					<!--                    </li>-->
				</ul>
			</div>
		</div>
		<div class="tab-content mt-md-3 mt-6">
			<div v-if="loading" class="d-flex justify-content-center">
				<div class="spinner-border" role="status">
					<span class="sr-only">Loading...</span>
				</div>
			</div>
			<div
				v-else
				role="tabpanel"
				id="tab-information"
				aria-labelledby="tab-information"
				class="tab-pane fade active show"
			>
				<table class="table m-0">
					<thead>
						<tr>
							<th>#</th>
							<th>Uri</th>
							<th>Route Name</th>
							<th>Title</th>
							<th>Allowed in groupss</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(record, i) in records" :key="record.id">
							<th scope="row" v-text="record.id"></th>
							<td v-text="record.uri"></td>
							<td v-text="record.name"></td>
							<td>
								<input
									type="text"
									v-model="record.title"
									@change="record.changed = true"
								/>
							</td>
							<td>
								<div
									v-for="role in types.roles"
									:key="role.id"
									class="custom-control custom-checkbox mb-2"
								>
									<input
										type="checkbox"
										class="custom-control-input"
										:value="role.id"
										:id="'type_' + record.id + role.id"
										v-model="record.groups_checked"
										@change="setChanged(i)"
									/>
									<label
										class="custom-control-label cursor-pointer"
										:for="'type_' + record.id + role.id"
									>
										{{ role.title }}
									</label>
								</div>
								<a @click.prevent="toggleAll(i)" href="#"
									>Toggle all</a
								>
							</td>
							<td>
								<button
									@click="saveRecord(i)"
									type="button"
									class="text-nowrap btn waves-effect waves-themed"
									:class="{
										'btn-danger': record.changed,
										'btn-default': !record.changed,
									}"
									:disabled="!record.changed"
								>
									<span
										v-show="record.updating"
										class="spinner-border spinner-border-sm"
										role="status"
										aria-hidden="true"
									></span>
									<i class="fal fa-download mr-1"></i>
									{{
										record.updating
											? 'Saving changes'
											: 'Save changes'
									}}
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
import lodashDifference from 'lodash.difference';

export default {
	name: 'SettingsRouteList',
	data() {
		return {
			loading: true,
			records: [],
			types: {
				roles: {},
			},
		};
	},
	mounted() {
		axios
			.post(window.location.href)
			.then((resp) => {
				if (resp.data.success === true) {
					this.records = resp.data.records.map((item) =>
						this.mapItem(item)
					);

					this.types.roles = resp.data.types.roles;
					this.types.roles_total = Object.keys(
						resp.data.types.roles
					).length;

					let roles_all_ids = [];
					Object.values(this.types.roles).forEach((item) =>
						roles_all_ids.push(item.id)
					);

					this.types.roles_all_ids = roles_all_ids;
				} else {
					App.Forms.simpleErrors(resp.data);
				}
			})
			.catch((error) => {
				App.Forms.simpleErrors(error.response.data);
			})
			.finally(() => (this.loading = false));
	},
	methods: {
		mapItem(item) {
			item.updating = false;
			item.changed = false;
			let groups_checked = [];

			item.groups.forEach((v) => groups_checked.push(v.id));

			item.groups_checked = groups_checked;
			item.groups_checked_orig = groups_checked;

			return item;
		},
		saveRecord(index) {
			let record = this.records[index];

			record.updating = true;
			axios
				.post(window.location.href + '/save', record)
				.then((resp) => {
					if (resp.data.success === true) {
						this.$set(
							this.records,
							index,
							this.mapItem(resp.data.record)
						);
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				})
				.finally(() => (record.updating = false));
		},
		setChanged(index) {
			let record = this.records[index];

			record.changed = !!(
				lodashDifference(
					record.groups_checked,
					record.groups_checked_orig
				).length ||
				lodashDifference(
					record.groups_checked_orig,
					record.groups_checked
				).length
			);
		},
		toggleAll(index) {
			let record = this.records[index];

			if (record.groups_checked.length === this.types.roles_total) {
				// deSelect
				record.groups_checked = [];
			} else {
				// selectALl
				record.groups_checked = this.types.roles_all_ids;
			}
			record.changed = true;
		},
	},
};
</script>

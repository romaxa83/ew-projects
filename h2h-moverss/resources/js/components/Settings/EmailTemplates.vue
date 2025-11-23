<template>
	<div class="panel-content">
		<div v-if="loading" class="d-flex justify-content-center">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<div v-else>
			<table class="table table-hover m-0">
				<thead>
					<tr>
						<th>#</th>
						<th>Active</th>
						<th>Order</th>
						<th>Title</th>
						<th>Mailjet Tpl ID</th>
						<th>Updated at</th>
					</tr>
				</thead>
				<tbody>
					<tr
						class="cursor-pointer"
						@click="editRecord(i)"
						v-for="(v, i) in records"
						:key="v.id"
					>
						<th scope="row" v-text="v.id"></th>
						<td>{{ v.active ? 'Yes' : 'No' }}</td>
						<td v-text="v.sort"></td>
						<td v-text="v.title"></td>
						<td v-text="v.mailjet_tpl_id"></td>
						<td>
							{{ v.updated_at | formatDate('ll, [at] h:mm a') }}
						</td>
					</tr>
				</tbody>
			</table>

			<div
				class="modal fade"
				id="edit-record-modal"
				role="dialog"
				aria-hidden="true"
			>
				<div
					class="modal-dialog modal-lg modal-dialog-centered"
					role="document"
				>
					<div class="modal-content">
						<div class="modal-header bg-fusion-100">
							<h5 class="modal-title">Edit</h5>
							<button
								type="button"
								class="close"
								data-dismiss="modal"
								aria-label="Close"
							>
								<span aria-hidden="true"
									><i class="fal fa-times"></i
								></span>
							</button>
						</div>
						<div class="modal-body">
							<div class="custom-control custom-checkbox mb-2">
								<input
									type="checkbox"
									class="custom-control-input"
									id="record_active"
									v-model="record.active"
								/>
								<label
									class="custom-control-label"
									for="record_active"
									>Active</label
								>
							</div>
							<div class="form-group mb-2">
								<label class="form-label"
									>Title<sup>*</sup></label
								>
								<div class="input-group">
									<input
										v-model="record.title"
										class="form-control"
										type="text"
									/>
								</div>
							</div>
							<div class="form-group mb-2">
								<label class="form-label">Order</label>
								<div class="input-group">
									<input
										v-model.number="record.sort"
										class="form-control"
										type="text"
									/>
								</div>
							</div>
							<div class="form-group mb-2">
								<label class="form-label"
									>Mailjet Tpl ID<sup>*</sup></label
								>
								<div class="input-group">
									<input
										v-model.number="record.mailjet_tpl_id"
										class="form-control"
										type="text"
									/>
								</div>
							</div>
							<hr />
							<h4>
								You can use variables in email template like
								this:
							</h4>
							<h5>Order:</h5>
							<pre v-pre>
                                Order:
                                {{ var:order.id }} - ID
                                {{ var:order.estimate.type }} - Move type
                                {{ var:order.estimate.calculated_moving_distance


								}} - Move distance
                                {{ var:order.estimate.(local|interstate|intrastate)


								}} - (array) Move details
                                {{ var:order.waypoints }} - (array) Waypoints
                                {{ var:order.first_waypoint


								}} - (array) First Waypoint
                                {{ var:order.works }} - (array) Works
                                {{ var:order.first_work }} - (array) First Work
                                {{ var:order.first_work.friendly_date:"n/a"


								}} - Date-time start of work
                                {{ var:order.sizing_volume


								}} - Inventory size: CuFT
                                {{ var:order.sizing_weight


								}} - Inventory size: Lb

                                Order Waypoints (array):
                                {% for waypoint in var:order.waypoints %}
                                Variables...
                                address: "651 E Ohio St, Chicago, IL 60611, USA"
                                ap: "12312"
                                building_type_id: 2
                                city: "Chicago"
                                created_at: "-000001-11-30T00:00:00.000000Z"
                                deleted_at: null
                                flights_id: 2
                                has_elevator: 1
                                id: 2
                                lat: 41.89267949
                                lng: -87.61474221
                                notes: [{id: 11, waypoint_id: 2, user_id: 6, value: "blah", created_at: "2020-09-15T19:38:02.000000Z",…},…]
                                order_id: 1
                                parking_type: {id: 1, title: "No parking"}
                                parking_type_id: 1
                                sort: 1
                                state: "IL"
                                type: "pickup"
                                updated_at: "2021-02-19T16:22:32.000000Z"
                                zip: "60611"
                                {% endfor %}

                                Client:
                                {{ var:client.id }} - ID
                                {{ var:client.name }} - Name
                                {{ var:client.lname }} - Last Name

                                Manager:
                                {{ var:manager.name:"" }} - Name
                                {{ var:manager.signature:"" }} - Signature

                                Client Phones (array):
                                {% for phone in var:client.phones %}
                                Phone: {{ phone.value }}
                                {% endfor %}

                                Client Emails (array):
                                {% for email in var:client.emails %}
                                Email: {{ email.value }}
                                {% endfor %}

                                More details about Mailjet Template syntax
                                https://dev.mailjet.com/email/template-language/reference/
                            </pre>
						</div>
						<div class="modal-footer">
							<div class="flex-grow-1">
								<button
									type="button"
									class="btn btn-secondary"
									data-dismiss="modal"
								>
									Close
								</button>
							</div>
							<div>
								<button
									@click="submit()"
									type="button"
									class="text-nowrap btn waves-effect waves-themed"
									:class="{
										'btn-danger': is_changed,
										'btn-default': !is_changed,
									}"
									:disabled="!is_changed"
								>
									<span
										v-show="updating"
										class="spinner-border spinner-border-sm"
										role="status"
										aria-hidden="true"
									></span>
									{{
										record.id
											? updating
												? 'Saving changes'
												: 'Save changes'
											: 'Create new template'
									}}
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';

import cloneDeep from 'lodash.clonedeep';

export default {
	name: 'SettingsEmailTemplates',
	filters: {
		formatDate,
	},
	data() {
		return {
			loading: true,
			updating: false,
			is_changed: false,
			records: [],
			record: {},
		};
	},
	watch: {
		record: {
			handler: function (val, oldVal) {
				if (!this.is_changed && Object.keys(oldVal).length) {
					this.is_changed = true;
				}
			},
			deep: true,
		},
	},
	mounted() {
		axios
			.post(window.location.href)
			.then((resp) => {
				if (resp.data.success === true) {
					this.records = resp.data.records;
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
		addRecord() {
			this.is_changed = 0;
			this.record = {
				id: null,
				title: null,
				mailjet_tpl_id: null,
				active: 0,
				sort: null,
			};
			$('#edit-record-modal').modal('show');
		},
		editRecord(index) {
			this.record = cloneDeep(this.records[index]);
			$('#edit-record-modal').modal('show');
		},
		submit() {
			this.updating = true;
			axios
				.post(window.location.href + '/save', this.record)
				.then((resp) => {
					if (resp.data.success === true) {
						this.records = resp.data.records;
						this.is_changed = false;
						$('#edit-record-modal').modal('hide');
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

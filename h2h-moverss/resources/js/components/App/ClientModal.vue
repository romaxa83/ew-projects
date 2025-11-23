<template>
	<div class="modal fade" id="client-modal" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-left">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title h4">
						Edit profile #<span
							v-if="record"
							v-text="record.id"
						></span>
					</h5>
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
					<div
						v-if="loading || updating"
						class="d-flex justify-content-center"
					>
						<div class="spinner-border" role="status">
							<span class="sr-only"
								>{{ loading ? 'Loading' : 'Updating' }}...</span
							>
						</div>
					</div>
					<div v-if="record" class="container">
						<div class="form-group">
							<label class="form-label" for="name-f"
								>Client</label
							>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text text-success">
										<i class="ni ni-user fs-xl"></i>
									</span>
								</div>
								<input
									type="text"
									aria-label="First name"
									class="form-control"
									placeholder="First name"
									id="name-f"
									v-model="record.name"
								/>
								<input
									type="text"
									aria-label="Last name"
									class="form-control"
									placeholder="Last name"
									v-model="record.lname"
								/>
							</div>
						</div>

						<modal-phones
							v-if="record"
							:records="record.phones"
							:types="types.phones"
							@setPrimary="setPrimary"
							@setType="setType"
							@deleteRecord="deleteRecord"
							@addRecord="addRecord"
						></modal-phones>
						<hr class="mb-2 mt-2" />

						<modal-emails
							v-if="record"
							:records="record.emails"
							@setPrimary="setPrimary"
							@deleteRecord="deleteRecord"
							@addRecord="addRecord"
						></modal-emails>
						<hr class="mb-2 mt-2" />

						<modal-messengers
							:records="record.messengers"
							:types="types.messengers"
							@setType="setType"
							@deleteRecord="deleteRecord"
							@addRecord="addRecord"
						></modal-messengers>
						<hr class="mb-2 mt-2" />

						<modal-notes
							ref="notes"
							:records="record.notes"
							:has-edit="true"
							@addRecord="addNoteRecord"
							@deleteRecord="deleteNoteRecord"
							@closeDrop="closeDrop"
						></modal-notes>
						<hr class="mb-2 mt-2" />

						<h4>Tags:</h4>
						<multiselect
							v-model="selectedTags"
							track-by="key"
							label="value"
							placeholder="Type to search"
							tagPosition="bottom"
							:multiple="true"
							:show-no-results="false"
							:show-no-options="false"
							:options="clientTags"
							@select="autocompleteOnSelect"
							@remove="toggleUnSelectMarket"
						>
							<template slot="option" slot-scope="props">
								<i
									:class="
										'fas mr-1 fa-' +
										(props.option.icon
											? props.option.icon
											: 'tag')
									"
									:style="{ color: props.option.color }"
								></i>
								<span class="option__title">{{
									props.option.title
								}}</span>
							</template>
						</multiselect>
					</div>
				</div>
				<div class="modal-footer">
					<button
						@click="closeModal"
						type="button"
						class="btn btn-secondary"
					>
						Close
					</button>
					<button
						@click="submit()"
						:disabled="!is_changed"
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
</template>

<script>
import { fixBsModal } from '@/fix-bs-modal';
import ModalEmails from '@components/Order/TabOverview/ClientModal/Emails.vue';
import ModalMessengers from '@components/Order/TabOverview/ClientModal/Messengers.vue';
import ModalNotes from '@components/Order/TabOverview/ClientModal/Notes.vue';
import ModalPhones from '@components/Order/TabOverview/ClientModal/Phones.vue';
import cloneDeep from 'lodash.clonedeep';
import Multiselect from 'vue-multiselect';
import { mapGetters } from 'vuex';

export default {
	name: 'ClientModal',
	components: {
		ModalPhones,
		ModalEmails,
		ModalMessengers,
		ModalNotes,
		Multiselect,
	},
	props: {
		openOnload: {
			type: Boolean,
			default: false,
			required: false,
		},
		whenUpdated: {
			type: String,
			required: false,
		},
	},
	data() {
		return {
			record: null,
			is_changed: false,
			order_id: null,
			selectedTags: [],
		};
	},
	computed: {
		clientTags() {
			return Object.values(this.types.tags)
				.slice()
				.sort((a, b) => a.sort - b.sort)
				.map((item) => {
					return {
						key: item.id,
						value: item.title,
						...item,
					};
				});
		},
		...mapGetters({
			clientID: 'clients/clientId',
			clientRecord: 'clients/record',
			loading: 'clients/loading',
			updating: 'clients/updating',
			types: 'clients/types',
		}),
	},
	watch: {
		record: {
			handler() {
				this.is_changed = true;
			},
			deep: true,
		},
	},
	mounted() {
		if (this.openOnload) $('#client-modal').modal('show');

		const order_id = document.getElementById('order_id');
		if (order_id) {
			this.order_id = order_id.textContent;
		}

		$('#client-modal')
			.on('shown.bs.modal', () => {
				if (this.clientID && this.openOnload) {
					this.setClientData(this.clientRecord);
				}
				fixBsModal();
			})

			.on('hide.bs.modal', (e) => {
				// Save if data changed - cant close modal
				if (this.is_changed) {
					e.preventDefault();
				}
			});
	},
	methods: {
		addNoteRecord(payload) {
			this.record.notes.push(payload);
		},
		addRecord(type, obj) {
			let record = {
				is_new: true,
				type_id: 1,
				is_primary: 0,
				value: null,
			};
			if (type === 'phone') {
				record = {
					type: this.types.messengers[1],
					...record,
				};
			}

			obj.push(record);
		},
		autocompleteOnSelect() {
			this.is_changed = true;
		},
		closeDrop() {
			$('#client-modal .modal-header').trigger('click');
		},
		closeModal() {
			this.is_changed = false;
			$('#client-modal').modal('hide');
		},
		deleteNoteRecord(index) {
			this.$delete(this.record.notes, index);
		},
		deleteRecord(obj, index) {
			this.$delete(obj, index);
			this.closeDrop();
		},
		editClient(id) {
			// Used in Clients Listing
			$('#client-modal').modal('show');
			this.setClientData(this.$store.dispatch('clients/fetchClient', id));
		},
		async setClientData(record) {
			this.record = cloneDeep(await record);

			this.updateSelectedTags();
			this.$nextTick(() => (this.is_changed = false));
		},
		setPrimary(obj, index) {
			obj = obj.map((v, i) => {
				// Проставляем primary
				v.is_primary = i === index ? 1 : 0;
			});
			this.closeDrop();
		},
		setType(record, id) {
			record.type_id = id;
			this.closeDrop();
		},
		async submit() {
			try {
				const payload = {
					...this.record,
					selectedTags: this.selectedTags,
				};

				if (this.order_id) {
					payload.order_id = this.order_id;
				}

				let { record, msg } = await this.$store.dispatch(
					'clients/updateClient',
					payload
				);
				await this.setClientData(record);

				App.Forms.showAlert('success', msg);
				this.$refs.notes.resetEdit();
				this.closeModal();

				if (this.whenUpdated) {
					App.Forms.executeFunction(this.whenUpdated);
				}

				return Promise.resolve(msg);
			} catch (e) {
				return Promise.reject(false);
			}
		},
		toggleUnSelectMarket(e) {
			this.is_changed = true;
		},
		updateSelectedTags() {
			this.selectedTags = cloneDeep(this.record.tags).map((v) => {
				return {
					key: v.id,
					value: v.title,
				};
			});
		},
	},
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>
.multiselect__tags {
	min-height: 20px;
	font-size: 0.75rem;
	border: none;
}

.multiselect__single,
.multiselect__option,
.multiselect__input {
	font-size: 0.75rem;
}
</style>

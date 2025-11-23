<template>
	<div
		class="modal fade"
		id="modal-waypoint"
		role="dialog"
		aria-hidden="true"
	>
		<div
			class="modal-dialog modal-lg modal-dialog-centered"
			role="document"
		>
			<div class="modal-content">
				<div
					v-show="loading"
					class="frame-wrap position-absolute w-100 h-100 opacity-50"
					style="z-index: 10"
				>
					<div
						class="w-100 d-flex justify-content-center align-items-center"
					>
						<div
							class="spinner-border text-info position-absolute"
							style="top: 30%"
							role="status"
						>
							<span class="sr-only">Loading...</span>
						</div>
					</div>
				</div>
				<div class="modal-header bg-fusion-200">
					<h5 class="modal-title">Waypoint</h5>
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
				<form
					@submit.prevent="submit('save')"
					class="modal-body"
					autocomplete="off"
				>
					<div class="form-group mb-3">
						<label class="form-label" for="wp_address"
							>Address</label
						>
						<div class="input-group">
							<div class="input-group-prepend">
								<button
									class="btn btn-outline-default dropdown-toggle"
									type="button"
									data-toggle="dropdown"
									aria-haspopup="true"
									aria-expanded="false"
								>
									{{
										record.type === 'pickup'
											? 'Pickup'
											: 'Destination'
									}}
								</button>
								<div class="dropdown-menu">
									<a
										class="dropdown-item"
										href="#"
										@click.prevent="changeType('pickup')"
										>Pickup</a
									>
									<a
										class="dropdown-item"
										href="#"
										@click.prevent="
											changeType('destination')
										"
										>Destination</a
									>
								</div>
							</div>
							<vue-google-autocomplete
								id="wp_address"
								ref="googleAutocompleteInput"
								classname="form-control"
								placeholder="Start typing..."
								:country="['us']"
								types="geocode"
								@change="addressChanged"
								v-model="record.address"
								v-on:placechanged="getAddressData"
								:disabled="record.miscs.usedAutocomplete"
							>
							</vue-google-autocomplete>
							<div
								v-if="record.miscs.usedAutocomplete"
								class="input-group-append"
							>
								<button
									type="button"
									name="reset"
									@click="unlockAddress"
									class="btn btn-info waves-effect waves-themed"
								>
									Unlock
								</button>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col">
							<div class="form-group">
								<label for="wp_state" class="form-label"
									>State<sup>*</sup></label
								>
								<select
									id="wp_state"
									v-model="record.state"
									class="form-control"
									:disabled="record.miscs.usedAutocomplete"
								>
									<option
										v-for="(title, state) in usa_states"
										:key="state"
										v-bind:value="state"
									>
										{{ title }}
									</option>
								</select>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label for="wp_city" class="form-label"
									>City</label
								>
								<input
									id="wp_city"
									autocomplete="chrome-off"
									v-model="record.city"
									type="text"
									class="form-control"
									placeholder="City"
									:disabled="record.miscs.usedAutocomplete"
								/>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label for="wp_zip" class="form-label"
									>Zip code<sup>*</sup></label
								>
								<input
									v-model="record.zip"
									autocomplete="chrome-off"
									id="wp_zip"
									type="text"
									class="form-control"
									@change="onZipChanged"
									placeholder="Zip code"
									:disabled="record.miscs.usedAutocomplete"
								/>
							</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col">
							<div class="form-group">
								<label
									for="wp_building_type_id"
									class="form-label"
									>Property type *</label
								>
								<select
									id="wp_building_type_id"
									v-model="record.building_type_id"
									class="form-control"
								>
									<option
										v-for="v in types_building"
										:key="v.id"
										v-bind:value="v.id"
									>
										{{ v.title }}
									</option>
								</select>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label for="wp_ap" class="form-label"
									>Unit #</label
								>
								<input
									v-model="record.ap"
									id="wp_ap"
									type="text"
									class="form-control"
									placeholder="Unit #"
								/>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label for="flights_id" class="form-label"
									>Stairs</label
								>
								<select
									id="flights_id"
									v-model="record.flights_id"
									class="form-control"
								>
									<option value="0">None</option>
									<option
										v-for="(v, k) of sortedFlights"
										:key="k"
										:value="v.id"
									>
										{{ v.title }}
									</option>
								</select>
							</div>
						</div>
					</div>
					<div class="d-flex mb-3">
						<div class="flex-grow-1">
							<div class="frame-wrap">
								<div
									class="custom-control custom-radio custom-control-inline"
									v-for="v in types_parkings"
									:key="v.id"
									v-bind:value="v.id"
								>
									<input
										type="radio"
										class="custom-control-input"
										:id="'parking_' + v.id"
										v-model="record.parking_type_id"
										:value="v.id"
									/>
									<label
										class="custom-control-label"
										:for="'parking_' + v.id"
										>{{ v.title }}</label
									>
								</div>
							</div>
						</div>
						<div
							class="custom-control custom-checkbox custom-control-inline"
						>
							<input
								v-model="record.has_elevator"
								type="checkbox"
								class="custom-control-input"
								id="wp_elevator"
								value="1"
							/>
							<label
								class="custom-control-label"
								for="wp_elevator"
								>Elevator</label
							>
						</div>
					</div>

					<modal-notes
						:records="record.notes"
						:ignore-empty="true"
						@addRecord="addNote"
						@deleteRecord="deleteNote"
					></modal-notes>
				</form>
				<div class="modal-footer">
					<button
						type="button"
						class="btn btn-secondary mr-auto"
						data-dismiss="modal"
					>
						Close
					</button>
					<button
						v-if="record.lat"
						@click="showMap()"
						type="button"
						class="btn btn-secondary waves-effect waves-themed"
					>
						Show on Map
					</button>
					<button
						@click="submit('create')"
						type="button"
						class="btn btn-success waves-effect waves-themed"
					>
						{{ saveAndCreateNew }}
					</button>
					<button
						@click="submit('save')"
						type="button"
						class="btn btn-primary"
						id="wp_go"
					>
						{{
							record.id
								? loading === 'save'
									? 'Saving changes'
									: 'Save changes'
								: 'Create waypoint'
						}}
					</button>
				</div>
				<div
					v-show="show_map"
					class="modal-map height-xl border-top"
				></div>
			</div>
		</div>
	</div>
</template>

<script>
import VueGoogleAutocomplete from 'vue-google-autocomplete';
import ModalNotes from '../ClientModal/Notes';
import { apiWaypoints } from '@/api/crm';
import { fixBsModal } from '@/fix-bs-modal';

const componentForm = {
	street_number: 'short_name',
	route: 'long_name',
	locality: 'long_name',
	administrative_area_level_1: 'short_name',
	country: 'long_name',
	postal_code: 'short_name',
};

export default {
	name: 'WaypointsModal',
	components: {
		ModalNotes,
		VueGoogleAutocomplete,
	},
	props: {
		record: {
			type: Object,
			required: true,
		},
		types_building: {
			type: Object,
			required: false,
		},
		types_flights: {
			type: Object,
			required: false,
		},
		types_parkings: {
			type: Object,
			required: false,
		},
	},
	data() {
		return {
			loading: false,
			show_map: false,
			usa_states: null,
			prevent_submit: false,
		};
	},
	computed: {
		sortedFlights() {
			return Object.values(this.types_flights).sort((a, b) => {
				return a.sort - b.sort;
			});
		},
		saveAndCreateNew() {
			return this.record.id
				? this.loading === 'create'
					? 'Saving changes & Add new'
					: 'Save & Add new'
				: 'Create & Add new';
		},
	},
	mounted() {
		let modal = $('#modal-waypoint');
		modal.modal('show');
		modal
			.on('shown.bs.modal', () => {
				this.$refs.googleAutocompleteInput.update(this.record.address);

				if (!this.record.id) {
					$('#wp_address').focus();
				}

                fixBsModal();
			})
			.on('hide.bs.modal', () => {
				$('#modal-waypoint .modal-map .gm-style').remove();
				this.show_map = false;
			});
		this.loadStates();
	},
	methods: {
		addNote(payload) {
			this.record.notes.push(payload);
		},
		addressChanged(value) {
			this.record.address = value;
			this.$refs.googleAutocompleteInput.update(value);
		},
		changeType(value) {
			this.record.type = value;
			this.closeDrop();
		},
		closeDrop() {
			$('#modal-waypoint .modal-title').trigger('click');
		},
		deleteNote(index) {
			this.$delete(this.record.notes, index);
			this.closeDrop();
		},
		geocoderAddress(GeocoderRequest) {
			this.prevent_submit = true;
			const geocoder = new google.maps.Geocoder();

			geocoder.geocode(GeocoderRequest, (results, status) => {
				if (status === 'OK') {
					if (results[0]) {
						this.addressChanged(results[0].formatted_address);
						this.updateAddress(results[0].address_components);

						if (GeocoderRequest['address']) {
							this.record.lat = parseFloat(
								results[0].geometry.location
									.lat()
									.toString()
									.substring(0, 11)
							);
							this.record.lng = parseFloat(
								results[0].geometry.location
									.lng()
									.toString()
									.substring(0, 12)
							);
							this.record.miscs.usedAutocomplete = true;

							// this.showMap();
							$('#wp_go').focus();
						}
					} else {
						App.Forms.showAlert(
							'error',
							'Error',
							'No results found'
						);
					}
				} else {
					App.Forms.showAlert(
						'error',
						'Error',
						'Geocoder failed due to: ' + status
					);
				}
				this.prevent_submit = false;
			});
		},
		// Получили инфу с API, обновляем поля
		getAddressData(addressData) {
			this.record.miscs.usedAutocomplete = true;

			this.record.state = addressData.administrative_area_level_1;
			this.record.city = addressData.locality;
			this.record.zip = addressData.postal_code;
			this.record.lat = parseFloat(
				addressData.latitude.toString().substring(0, 11)
			);
			this.record.lng = parseFloat(
				addressData.longitude.toString().substring(0, 12)
			);

			// this.showMap();
		},
		loadStates() {
			apiWaypoints('states', {}, 'get').then(
				(resp) => (this.usa_states = resp.records)
			);
		},
		onZipChanged() {
			this.geocoderAddress({ address: this.record.zip });
		},
		showMap() {
			this.show_map = !this.show_map;

			let mapCanvas = $('#modal-waypoint').find('.modal-map');
			let cairo = { lat: this.record.lat, lng: this.record.lng };
			const map = new google.maps.Map(mapCanvas[0], {
				scaleControl: true,
				center: cairo,
				zoom: 15,
			});

			// Пикер
			const infoWindow = new google.maps.InfoWindow();
			infoWindow.setContent(this.record.address);
			let marker = new google.maps.Marker({
				map,
				position: cairo,
				draggable: true,
			});

			// Показ подсказки адреса
			marker.addListener('click', () => {
				infoWindow.open(map, marker);
			});

			// Перемещение маркера
			google.maps.event.addListener(marker, 'dragend', (ev) => {
				this.record.lat = ev.latLng.lat();
				this.record.lng = ev.latLng.lng();

				this.geocoderAddress({ location: ev.latLng });
			});
		},
		submit(type) {
			if (this.prevent_submit) return;

			this.loading = type;
			new Promise((resolve, reject) =>
				this.$emit('saveRecord', this.record, { resolve, reject })
			)
				.then(
					() => {
						let modal = $('#modal-waypoint');
						if (type === 'create') {
							// Открываем модалку на создание
							this.$emit('clickModal', -1);
							this.show_map = false;
							this.addressChanged(null);
						} else {
							modal.modal('hide');
						}
					},
					() => {}
				)
				.finally(() => (this.loading = false));
		},
		unlockAddress() {
			this.record.miscs.usedAutocomplete = false;
			this.show_map = false;
			this.$nextTick(() => this.$refs.googleAutocompleteInput.focus());
		},
		updateAddress(data) {
			let addressData = {};
			for (const component of data) {
				const addressType = component.types[0];

				if (componentForm[addressType]) {
					const val = component[componentForm[addressType]];
					addressData[addressType] = val;
				}
			}

			this.record.state = addressData.administrative_area_level_1;
			this.record.city = addressData.locality;
			this.record.zip = addressData.postal_code;
		},
	},
};
</script>

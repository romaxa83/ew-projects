<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>
				Waypoints
				<span
					v-show="loading"
					class="spinner-border spinner-border-sm ml-1"
					role="status"
					aria-hidden="true"
				></span>
			</h2>
			<div v-if="canManageWaypoints" class="panel-toolbar">
				<button
					@click="clickModal(-1)"
					class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed"
				>
					<i class="fal fa-plus"></i> Add waypoint
				</button>
			</div>
		</div>
		<div v-if="!loading" class="panel-container collapse show">
			<div class="panel-content">
				<div class="dd waypoints-block">
					<ol class="dd-list">
						<WaypointItem
							v-for="(v, index) in waypoints.records"
							:key="v.id"
							:i="index"
							:record="v"
							:types_flights="types?.flights || {}"
							:types_building="types?.building || {}"
							:can-manage="canManageWaypoints"
							@clickModal="clickModal"
							@removeRecord="removeRecord"
							@saveRecord="saveRecord"
						/>
					</ol>
				</div>
			</div>
			<div
				class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex"
			>
				<div class="mr-auto">
					<button
						@click="showRoute()"
						class="btn btn-primary btn-sm waves-effect waves-themed"
						:disabled="
							!this.estimate.calculated_moving_distance_auto
						"
					>
						Show route
					</button>
				</div>
				<div
					class="mt-2"
					v-show="this.estimate.calculated_moving_distance_auto"
				>
					<span class="badge badge-secondary fw-400 fs-md l-h-n">
						Auto Route:
						{{ this.estimate.calculated_moving_distance_auto }}
						miles
					</span>
				</div>
			</div>
		</div>

		<order-waypoints-modal
			v-if="openModal"
			:record.sync="recordCloned"
			:types_flights="types.flights"
			:types_building="types.building"
			:types_parkings="types.parking_types"
			@saveRecord="saveRecord"
			@clickModal="clickModal"
		></order-waypoints-modal>
		<route-map-modal v-if="openRouteModal"></route-map-modal>
	</div>
</template>

<script>
import { apiWaypoints } from '@/api/crm';

import cloneDeep from 'lodash.clonedeep';
import { mapGetters } from 'vuex';
import WaypointItem from './WaypointsModal/Item';

let order_id = document.getElementById('order_id').textContent;

const OrderWaypointsModal = () =>
	import(
		/* webpackChunkName: "OrderWaypointsModal" */ './WaypointsModal/Modal'
	);
const RouteMapModal = () =>
	import(
		/* webpackChunkName: "OrderWaypointsModalRoute" */ './WaypointsModal/RouteMapModal'
	);

export default {
	name: 'OrderWaypoints',
	components: {
		WaypointItem,
		OrderWaypointsModal,
		RouteMapModal,
	},
	props: {
		canManageWaypoints: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			errors: null,
			openModal: false,
			openRouteModal: false,
			recordCloned: null,
			types: null,
		};
	},
	computed: {
		...mapGetters({
			waypoints: 'order/waypoints',
			estimate: 'order/estimate',
		}),
	},
	mounted() {
		this.loading = true;
		this.errors = null;

		this.$store
			.dispatch('getSession')
			.then(({ types }) => {
				this.types = {
					flights: types.waypoints.flights,
					building: types.waypoints.building_types,
					parking_types: types.waypoints.parking_types,
				};
			})
			.finally(() => {
				this.loading = false;
				this.$nextTick(() => this.initNestable());
			});
	},
	methods: {
		clickModal(index) {
			if (index >= 0) {
				this.recordCloned = cloneDeep(this.waypoints.records[index]);
				if (this.recordCloned.miscs === null) {
					this.recordCloned.miscs = {
						usedAutocomplete: false,
					};
				}
			} else {
				this.recordCloned = this.emptyRecord();
			}

			if (!this.openModal) this.openModal = true;
			else $('#modal-waypoint').modal('show');
		},
		emptyRecord() {
			return {
				id: null,
				order_id,
				address: null,
				ap: null,
				building_type_id: 1,
				city: null,
				flights_id: 0,
				has_elevator: false,
				parking_type_id: 1,
				lat: null,
				lng: null,
				notes: [],
				sort: null,
				state: null,
				type: 'pickup',
				zip: null,
				miscs: {
					usedAutocomplete: false,
				},
			};
		},
		initNestable() {
			$('.waypoints-block').nestable({
				itemClass: 'waypoint-item',
				contentNodeName: 'div',
				itemRenderer: function (
					item_attrs,
					content,
					children,
					options,
					item
				) {
					let contentClass = options.contentClass;
					let item_attrs_string = $.map(
						item_attrs,
						function (value, key) {
							return ' ' + key + '="' + value + '"';
						}
					).join(' ');

					let html =
						'<' + options.itemNodeName + item_attrs_string + '>';
					html +=
						'<' +
						options.handleNodeName +
						' class="' +
						options.handleClass +
						' waypoints-timeline-icon">' +
						'<i class="fas fa-2x fa-map-marker-alt"></i></' +
						options.handleNodeName +
						'>';
					html +=
						'<' +
						options.contentNodeName +
						' class="' +
						contentClass +
						'">';
					html += content;
					html += '</' + options.contentNodeName + '>';
					html += children;
					html += '</' + options.itemNodeName + '>';

					return html;
				},
				contentCallback: function (item) {
					return this.itemContentHTML(item);
				},
				callback: () => {
					this.saveSort($('.waypoints-block').nestable('serialize'));
				},
				includeContent: false,
				maxDepth: 1,
			});
		},
		removeRecord(id) {
			apiWaypoints('remove', {
				order_id,
				id,
			}).then((resp) => {
				this.$store.dispatch('order/updateWaypoints', resp.records);
				this.$store.dispatch('order/updateEstimateTimes', {
					calculated_moving_distance: resp.calculated_moving_distance,
					calculated_moving_distance_is_auto:
						resp.calculated_moving_distance_is_auto,
					calculated_moving_distance_auto:
						resp.calculated_moving_distance_auto,
					calculated_moving_time: resp.calculated_moving_time,
				});

				this.$emit('recalculate');
			});
		},
		saveRecord(data, promise = null) {
			apiWaypoints('save', data)
				.then((resp) => {
					this.$store.dispatch('order/updateWaypoints', resp.records);
					this.$store.dispatch('order/updateEstimateTimes', {
						calculated_moving_distance:
							resp.calculated_moving_distance,
						calculated_moving_distance_is_auto:
							resp.calculated_moving_distance_is_auto,
						calculated_moving_distance_auto:
							resp.calculated_moving_distance_auto,
						calculated_moving_time: resp.calculated_moving_time,
					});

					this.$emit('recalculate');
					if (promise) promise.resolve();
				})
				.catch((error) => {
					if (promise) promise.reject(error.data);
				});
		},
		saveSort(records) {
			apiWaypoints('save-sort', {
				order_id,
				records,
			}).then(() => {
				this.$store.dispatch('order/refetchChangelog', 'update');
			});
		},
		showRoute() {
			if (!this.openRouteModal) this.openRouteModal = true;
			else $('#modal-route').modal('show');
		},
	},
};
</script>

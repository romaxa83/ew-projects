<template>
	<div class="modal fade" id="modal-route" role="dialog" aria-hidden="true">
		<div
			class="modal-dialog modal-lg modal-dialog-centered"
			role="document"
		>
			<div class="modal-content">
				<div class="modal-header bg-fusion-200">
					<h5 class="modal-title">Waypoints Map</h5>
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
					<div id="routeMap" style="min-height: 450px"></div>
				</div>
				<div class="modal-footer d-flex">
					<div class="mr-auto">
						<b>Route:</b>
						{{ this.estimate.calculated_moving_distance_auto }}
						miles
					</div>
					<div>
						<button
							type="button"
							class="btn btn-secondary"
							data-dismiss="modal"
						>
							Close
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
	name: 'RouteMapModal',
	mounted() {
		let modal = $('#modal-route');
		modal.modal('show');
		modal.on('shown.bs.modal', () => {
			this.initMap();
		});
	},

	computed: {
		...mapGetters({
			waypoints: 'order/waypoints',
			estimate: 'order/estimate',
		}),
	},
	methods: {
		initMap() {
			const directionsRenderer = new google.maps.DirectionsRenderer();
			const directionsService = new google.maps.DirectionsService();
			const map = new google.maps.Map(
				document.getElementById('routeMap'),
				{
					zoom: 14,
					center: { lat: 37.77, lng: -122.447 },
				}
			);
			directionsRenderer.setMap(map);

			this.calculateAndDisplayRoute(
				directionsService,
				directionsRenderer
			);
		},

		calculateAndDisplayRoute(directionsService, directionsRenderer) {
			let origin = this.waypoints.records[0];
			let destination =
				this.waypoints.records[this.waypoints.records.length - 1];
			let waypoints = [];

			this.waypoints.records.forEach((item) => {
				if (item.id !== origin.id && item.id !== destination.id) {
					waypoints.push({
						location: `${item.lat},${item.lng}`,
						stopover: true,
					});
				}
			});

			directionsService.route(
				{
					origin,
					destination,
					waypoints,
					travelMode: google.maps.TravelMode.DRIVING,
				},
				(response, status) => {
					if (status === 'OK') {
						directionsRenderer.setDirections(response);
					} else {
						App.Forms.showAlert(
							'error',
							'Error',
							'Directions request failed due to ' + status
						);
					}
				}
			);
		},
	},
};
</script>

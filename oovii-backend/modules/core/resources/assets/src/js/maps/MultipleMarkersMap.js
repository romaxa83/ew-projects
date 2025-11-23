export default class MultipleMarkersMap {
	constructor(el) {
		this.$container = $(el);

		this.selectors = {
			mapSelector: '.js-map-container'
		}

		this.map = null;
		this.markers = [];

		this.defaultCoordinates = {lat: 46.9648674, lng: 31.973737};
		this.defaultZoom = 16;

		this.$input = this.$container.find('.js-input');
	}

	set inputValue (value) {
		if (value) {
			if (value.hasOwnProperty('markers')) {
				value.markers = value.markers.map(coordinates => this.encodeCoordinates(coordinates));
			}

			if (value.hasOwnProperty('center')) {
				value.center = this.encodeCoordinates(value.center);
			}
		}

		this.$input.val(JSON.stringify(value));
	}

	get inputValue () {
		const data = JSON.parse(this.$input.val());

		if (data === null) {
			return {};
		}

		if (data.hasOwnProperty('markers')) {
			data.markers = data.markers.map(coordinates => this.decodeCoordinates(coordinates));
		}

		if (data.hasOwnProperty('center')) {
			data.center = this.decodeCoordinates(data.center);
		}

		return data;
	}

	main() {
		const $mapContainer = this.$container.find(this.selectors.mapSelector);

		// Transform container center coordinates
		let containerConfig = $mapContainer.data('config');
		if (containerConfig && containerConfig.hasOwnProperty('center')) {
			containerConfig.center = this.decodeCoordinates(containerConfig.center);
		}

		const mapConfig = $.extend(
			{
				zoom: this.defaultZoom,
				center: this.decodeCoordinates(this.defaultCoordinates)
			},
			containerConfig,
			this.inputValue
		);

		// Create map
		this.map = this.createMap($mapContainer[0], mapConfig);

		// Register map event listeners
		this.registerMapEventListeners();

		// Restore old makers
		this.restoreMarkers();
	}

	createMap(container, mapConfig) {
		throw new Error('Method "createMap" does not implemented');
	}

	createMarker(coordinates) {
		throw new Error('Method "createMarker" does not implemented');
	}

	createAndAddMarkerToMap(coordinates) {
		const marker = this.createMarker(coordinates);

		this.markers.push(marker);

		this.addMarkerToMap(marker);

		this.registerMarkerEventListeners(marker);

		this.updateHiddenInputValue();
	}

	restoreMarkers() {
		let markers = this.inputValue && this.inputValue.markers ? this.inputValue.markers : [];

		markers.forEach(coordinates => this.createAndAddMarkerToMap(coordinates));
	}

	addMarkerToMap(marker) {
		throw new Error('Method "addMarkerToMap" does not implemented');
	}

	deleteMarkerFromMap(marker) {
		throw new Error('Method "deleteMarkerFromMap" does not implemented');
	}

	updateHiddenInputValue() {
		throw new Error('Method "updateHiddenInputValue" does not implemented');
	}

	deleteMarker(marker) {
		this.markers.forEach((m, i) => {
			if (m === marker) {
				delete this.markers[i];
				return false;
			}
		});

		this.markers = this.markers.filter(coordinates => coordinates);

		this.deleteMarkerFromMap(marker);

		this.updateHiddenInputValue();
	}

	decodeCoordinates(coordinates) {
		return coordinates;
	}

	encodeCoordinates(coordinates) {
		return coordinates;
	}

	registerMapEventListeners() {}

	registerMarkerEventListeners(marker) {}
}

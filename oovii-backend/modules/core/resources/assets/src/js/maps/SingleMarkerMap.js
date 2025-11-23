export default class SingleMarkerMap {
	constructor (el) {
		this.$container = $(el);

		this.selectors = {
			liveSearchInput: '.js-search-input',
			mapSelector: '.js-map-container'
		};

		this.map = null;
		this.marker = null;
		this.geocoder = null;

		this.defaultCoordinates = {lat: 46.9648674, lng: 31.973737};
		this.defaultZoom = 16;

		this.$input = this.$container.find('.js-input');
	}

	set inputValue (value) {
		if (value) {
			if (value.hasOwnProperty('marker')) {
				value.marker = this.encodeCoordinates(value.marker);
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

		if (data.hasOwnProperty('marker')) {
			data.marker = this.decodeCoordinates(data.marker);
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

		this.marker = this.createMarker(this.getInitMarkerCoordinates());

		this.addMarkerToMap(this.marker);

		// Register event listeners
		this.registerEventListeners();

		// Init live search
		this.initLiveSearch();
	}

	createMap(container, mapConfig) {
		throw new Error('Method "createMap" does not implemented');
	}

	createMarker(coordinates) {
		throw new Error('Method "createMarker" does not implemented');
	}

	getInitMarkerCoordinates() {
		return this.inputValue.marker ? this.inputValue.marker : this.decodeCoordinates(this.defaultCoordinates);
	}

	addMarkerToMap(marker) {
		throw new Error('Method "addMarkerToMap" does not implemented');
	}

	decodeCoordinates(coordinates) {
		return coordinates;
	}

	encodeCoordinates(coordinates) {
		return coordinates;
	}

	registerEventListeners() {}

	initLiveSearch() {
		const liveSearchInput = this.$container.find(this.selectors.liveSearchInput);
		if (liveSearchInput.length) {
			liveSearchInput.autocomplete({
				source: this.liveSearchSource.bind(this),
				select: this.liveSearchSelect.bind(this)
			});
		}
	}

	liveSearchSource(request, response) {
		response([]);
	}

	liveSearchSelect(event, ui) {}
}

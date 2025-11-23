import MultipleMarkersMap from "../MultipleMarkersMap";

export default class YandexMultipleMarkersMap extends MultipleMarkersMap {
    main() {
        window.ymaps.ready().then(() => {
            super.main();
        }).catch(e => console.error(e));
    }

    createMap(container, mapConfig) {
        mapConfig.controls = [
            'fullscreenControl',
            'typeSelector',
            'zoomControl',
        ];

        return new window.ymaps.Map(container, mapConfig);
    }

    createMarker(coordinates) {
        return new window.ymaps.Placemark(coordinates, {}, {draggable: true});
    }

    addMarkerToMap(marker) {
        this.map.geoObjects.add(marker);
    }

    deleteMarkerFromMap(marker) {
        marker.setParent(null);
    }

    registerMapEventListeners() {
        // Add marker
        this.map.events.add('click', e => this.createAndAddMarkerToMap(e.get('coords')));

        this.map.events.add('boundschange', this.updateHiddenInputValue.bind(this));
    }

    registerMarkerEventListeners(marker) {
        marker.events.add('dragend', this.updateHiddenInputValue.bind(this));

        marker.events.add('click', () => this.deleteMarker(marker));
    }

    updateHiddenInputValue() {
        this.inputValue = {
            markers: this.markers.map(marker => marker.geometry.getCoordinates()),
            zoom: this.map.getZoom(),
            center: this.map.getCenter()
        };
    }

    decodeCoordinates(coordinates) {
        return [coordinates.lat, coordinates.lng];
    }

    encodeCoordinates(coordinates) {
        return {lat: coordinates[0], lng: coordinates[1]};
    }
}

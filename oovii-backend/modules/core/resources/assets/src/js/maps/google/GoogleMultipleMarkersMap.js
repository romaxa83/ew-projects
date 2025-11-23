import MultipleMarkersMap from "../MultipleMarkersMap";

export default class GoogleMultipleMarkersMap extends MultipleMarkersMap {
    createMap(container, mapConfig) {
        return new window.google.maps.Map(container, mapConfig);
    }

    createMarker(coordinates) {
        return new window.google.maps.Marker({position: coordinates, draggable: true});
    }

    addMarkerToMap(marker) {
        marker.setMap(this.map);
    }

    deleteMarkerFromMap(marker) {
        marker.setMap(null);
    }

    updateHiddenInputValue() {
        this.inputValue = {
            markers: this.markers.map(marker => marker.getPosition().toJSON()),
            zoom: this.map.getZoom(),
            center: this.map.getCenter().toJSON()
        };
    }

    registerMapEventListeners() {
        // Add marker
        window.google.maps.event.addListener(this.map, 'click', e => this.createAndAddMarkerToMap(e.latLng));

        window.google.maps.event.addListener(this.map, 'zoom_changed', this.updateHiddenInputValue.bind(this));
        window.google.maps.event.addListener(this.map, 'dragend', this.updateHiddenInputValue.bind(this));
    }

    registerMarkerEventListeners(marker) {
        window.google.maps.event.addListener(marker, 'dragend', this.updateHiddenInputValue.bind(this));

        window.google.maps.event.addListener(marker, 'click', () => this.deleteMarker(marker));
    }
}

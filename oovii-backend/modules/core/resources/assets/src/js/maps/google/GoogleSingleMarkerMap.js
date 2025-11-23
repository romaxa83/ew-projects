import SingleMarkerMap from "../SingleMarkerMap";

export default class GoogleSingleMarkerMap extends SingleMarkerMap {
    constructor(el) {
        super(el);

        this.geocoder = new window.google.maps.Geocoder();
    }

    createMap(container, mapConfig) {
        return new window.google.maps.Map(container, mapConfig);
    }

    createMarker(coordinates) {
        return new window.google.maps.Marker({position: coordinates, draggable: true});
    }

    addMarkerToMap(marker) {
        marker.setMap(this.map);
    }

    registerEventListeners() {
        // marker events
        window.google.maps.event.addListener(this.marker, 'dragend', this.updateHiddenInputValue.bind(this));

        // map events
        window.google.maps.event.addListener(this.map, 'click', e => {
            this.marker.setPosition(e.latLng);
            this.updateHiddenInputValue.bind(this)();
        });

        window.google.maps.event.addListener(this.map, 'zoom_changed', this.updateHiddenInputValue.bind(this));
        window.google.maps.event.addListener(this.map, 'dragend', this.updateHiddenInputValue.bind(this));
    }

    updateHiddenInputValue() {
        this.inputValue = {
            marker: this.marker.getPosition() ? this.marker.getPosition().toJSON() : {},
            zoom: this.map.getZoom(),
            center: this.map.getCenter().toJSON(),
        };
    }

    liveSearchSource(request, response) {
        this.geocoder.geocode({'address': request.term}, (results, status) => {
            response($.map(results, item => {
                return {
                    label: item.formatted_address,
                    value: item.formatted_address,
                    latitude: item.geometry.location.lat(),
                    longitude: item.geometry.location.lng()
                }
            }));
        })
    }

    liveSearchSelect(event, ui) {
        let position = {lat: ui.item.latitude, lng: ui.item.longitude};

        this.marker.setPosition(position);
        this.map.setCenter(position);

        this.updateHiddenInputValue();
    }
}

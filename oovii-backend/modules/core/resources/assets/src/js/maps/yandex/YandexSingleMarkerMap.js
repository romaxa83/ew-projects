import SingleMarkerMap from "../SingleMarkerMap";

export default class YandexSingleMarkerMap extends SingleMarkerMap {
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

    registerEventListeners() {
        this.marker.events.add('dragend', this.updateHiddenInputValue.bind(this));

        this.map.events.add('boundschange', this.updateHiddenInputValue.bind(this));
        this.map.events.add('click', e => {
            this.marker.geometry.setCoordinates(e.get('coords'));

            this.updateHiddenInputValue();
        });
    }

    updateHiddenInputValue() {
        this.inputValue = {
            marker: this.marker.geometry.getCoordinates(),
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

    liveSearchSource(request, response) {
        window.ymaps.geocode(request.term).then((res) => {
            let result = [];

            res.geoObjects.each((item) => {
                result.push({
                    label: item.properties.get('text'),
                    value: item.properties.get('text'),
                    coordinates: item.geometry.getCoordinates()
                });
            });

            response(result);
        });
    }

    liveSearchSelect(event, ui) {
        this.map.setCenter(ui.item.coordinates);
        this.marker.geometry.setCoordinates(ui.item.coordinates);
        this.updateHiddenInputValue();
    }
}

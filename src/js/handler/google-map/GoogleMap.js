const URUGUAY_DEPARTMENTS = [
    { name: "Artigas", lat: -30.4000, lng: -56.4667 },
    { name: "Canelones", lat: -34.5228, lng: -56.2781 },
    { name: "Cerro Largo", lat: -32.3700, lng: -54.1756 },
    { name: "Colonia", lat: -34.4626, lng: -57.8400 },
    { name: "Durazno", lat: -33.3800, lng: -56.5200 },
    { name: "Flores", lat: -33.5000, lng: -56.8600 },
    { name: "Florida", lat: -34.0961, lng: -56.2144 },
    { name: "Lavalleja", lat: -34.3667, lng: -55.2333 },
    { name: "Maldonado", lat: -34.9000, lng: -54.9500 },
    { name: "Paysandú", lat: -32.3214, lng: -58.0756 },
    { name: "Río Negro", lat: -33.1200, lng: -58.3000 },
    { name: "Rivera", lat: -30.9050, lng: -55.5506 },
    { name: "Rocha", lat: -34.4833, lng: -54.2167 },
    { name: "Salto", lat: -31.3833, lng: -57.9667 },
    { name: "San José", lat: -34.3372, lng: -56.7133 },
    { name: "Soriano", lat: -33.4500, lng: -58.0300 },
    { name: "Tacuarembó", lat: -31.7111, lng: -55.9833 },
    { name: "Treinta y Tres", lat: -33.2333, lng: -54.3833 },
];

class GoogleMap {
    constructor(payload) {
        var { el } = payload;
        this.DOM = {
            element: el,
        };
        this.lat = parseFloat(el.getAttribute("data-lat")) || -34.9011;
        this.lng = parseFloat(el.getAttribute("data-lng")) || -56.1645;
        this.zoom = parseInt(el.getAttribute("data-zoom")) || 12;
        this.init();
    }

    async init() {
        await this.loadGoogleMaps();

        this.map = new google.maps.Map(this.DOM.element, {
            center: { lat: this.lat, lng: this.lng },
            zoom: this.zoom,
            disableDefaultUI: true,
            zoomControl: true,
            styles: [
                { featureType: "all", elementType: "labels.text", stylers: [{ color: "#878787" }] },
                { featureType: "all", elementType: "labels.text.stroke", stylers: [{ visibility: "off" }] },
                { featureType: "landscape", elementType: "all", stylers: [{ color: "#f9f5ed" }] },
                { featureType: "road.highway", elementType: "all", stylers: [{ color: "#f5f5f5" }] },
                { featureType: "road.highway", elementType: "geometry.stroke", stylers: [{ color: "#c9c9c9" }] },
                { featureType: "water", elementType: "all", stylers: [{ color: "#aee0f4" }] },
            ],
        });

        const pinSvg = (color) => ({
            url: `data:image/svg+xml,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="30" height="45" viewBox="0 0 30 45"><path d="M15 0C6.7 0 0 6.7 0 15c0 11.2 15 30 15 30s15-18.8 15-30C30 6.7 23.3 0 15 0z" fill="${color}" stroke="#fff" stroke-width="1.5"/><circle cx="15" cy="15" r="5" fill="#fff"/></svg>`)}`,
            scaledSize: new google.maps.Size(30, 45),
            anchor: new google.maps.Point(15, 45),
        });

        new google.maps.Marker({
            position: { lat: -34.8833, lng: -56.1667 },
            map: this.map,
            title: "Hidral - Coruña 3021, Montevideo",
            icon: pinSvg("#004996"),
        });

        URUGUAY_DEPARTMENTS.forEach((dept) => {
            const marker = new google.maps.Marker({
                position: { lat: dept.lat, lng: dept.lng },
                map: this.map,
                title: dept.name,
                icon: pinSvg("#A7D2FF"),
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `<strong>${dept.name}</strong>`,
            });

            marker.addListener("click", () => {
                infoWindow.open(this.map, marker);
            });
        });
    }

    loadGoogleMaps() {
        return new Promise((resolve) => {
            if (window.google && window.google.maps) {
                resolve();
                return;
            }

            const script = document.createElement("script");
            script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyDDt51USx2punEQ4y6TU-9tcPN2DDZv44k`;
            script.async = true;
            script.defer = true;
            script.onload = resolve;
            document.head.appendChild(script);
        });
    }

    destroy() {
        this.map = null;
    }
}

export default GoogleMap;

import L from "leaflet";
import "leaflet/dist/leaflet.css";

import iconUrl from "leaflet/dist/images/marker-icon.png";
import iconRetinaUrl from "leaflet/dist/images/marker-icon-2x.png";
import shadowUrl from "leaflet/dist/images/marker-shadow.png";

L.Icon.Default.mergeOptions({
    iconUrl,
    iconRetinaUrl,
    shadowUrl,
});

const elementoMapa = document.getElementById("mapa");

if (elementoMapa) {
    const mapa = L.map(elementoMapa).setView([-26.1775, -58.1781], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(mapa);

    // Marcador inicial para verificar que los íconos de Leaflet cargan bien.
    L.marker([-26.1775, -58.1781]).addTo(mapa).bindPopup("Formosa Capital");
}

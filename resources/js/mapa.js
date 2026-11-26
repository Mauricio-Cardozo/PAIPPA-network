import L from "leaflet";
import "leaflet/dist/leaflet.css";

import iconUrl from "leaflet/dist/images/marker-icon.png";
import iconRetinaUrl from "leaflet/dist/images/marker-icon-2x.png";
import shadowUrl from "leaflet/dist/images/marker-shadow.png";

delete L.Icon.Default.prototype._getIconUrl;

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

    const crearPopup = (punto) => {
        const contenedor = document.createElement("div");

        const titulo = document.createElement("strong");
        titulo.textContent = punto.nombre;
        contenedor.appendChild(titulo);

        const direccion = document.createElement("p");
        direccion.textContent = `Dirección: ${punto.direccion}`;
        contenedor.appendChild(direccion);

        const horario = document.createElement("p");
        horario.textContent =
            `Horario: ${punto.horario ?? "Sin horario informado"}`;
        contenedor.appendChild(horario);

        const feria = document.createElement("p");
        feria.textContent = `Feria: ${punto.feria.nombre}`;
        contenedor.appendChild(feria);

        const ofertas = document.createElement("p");
        ofertas.textContent =
            `Ofertas publicadas: ${punto.ofertas_publicadas}`;
        contenedor.appendChild(ofertas);

        const enlace = document.createElement("a");
        enlace.href = punto.url_catalogo;
        enlace.textContent = "Ver ofertas de esta feria";
        enlace.className = "font-semibold text-paippa-green";
        contenedor.appendChild(enlace);

        return contenedor;
    };

    fetch("/mapa/puntos")
        .then((respuesta) => {
            if (!respuesta.ok) {
                throw new Error(
                    "No se pudieron cargar los puntos de retiro."
                );
            }

            return respuesta.json();
        })
        .then((respuesta) => {
            const puntos = respuesta.data ?? [];
            const coordenadas = [];

            puntos.forEach((punto) => {
                const latitud = Number(punto.latitud);
                const longitud = Number(punto.longitud);

                if (
                    !Number.isFinite(latitud) ||
                    !Number.isFinite(longitud)
                ) {
                    return;
                }

                L.marker([latitud, longitud])
                    .addTo(mapa)
                    .bindPopup(crearPopup(punto));

                coordenadas.push([latitud, longitud]);
            });

            if (coordenadas.length > 0) {
                mapa.fitBounds(coordenadas, {
                    padding: [30, 30],
                    maxZoom: 15,
                });
            }
        })
        .catch((error) => {
            console.error(error);

            const aviso = document.createElement("p");

            aviso.textContent =
                "No se pudieron cargar los puntos de retiro en este momento.";

            aviso.className =
                "mt-3 text-center text-sm text-red-600";

            elementoMapa.parentElement?.appendChild(aviso);
        });
}
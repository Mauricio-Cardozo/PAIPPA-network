/*
 * Mapa de puntos de retiro con Leaflet y OpenStreetMap.
 * Los datos vienen de GET /mapa/puntos (JSON).
 * Los popups se arman con nodos del DOM y textContent, nunca con HTML
 * concatenado, para que un dato malicioso no pueda inyectar código (XSS).
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Con Vite, Leaflet no encuentra las imágenes del marcador: se le indican a mano.
L.Icon.Default.mergeOptions({
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

const FORMOSA = [-26.1775, -58.1781];

function crearElemento(etiqueta, texto, clase) {
    const elemento = document.createElement(etiqueta);
    if (texto) elemento.textContent = texto;
    if (clase) elemento.className = clase;
    return elemento;
}

function armarPopup(punto) {
    const contenedor = crearElemento('div', null, 'space-y-1 text-sm');
    contenedor.append(
        crearElemento('p', punto.nombre, 'font-semibold text-base'),
        crearElemento('p', punto.feria.nombre, 'text-verde-700'),
        crearElemento('p', '📍 ' + punto.direccion),
        crearElemento('p', '🕒 ' + punto.horario),
        crearElemento('p', punto.ofertas_publicadas + ' ofertas publicadas'),
    );

    const enlace = crearElemento('a', 'Ver ofertas de esta feria →', 'font-semibold');
    enlace.href = punto.url_catalogo;

    const comoLlegar = crearElemento('a', 'Cómo llegar', 'block');
    comoLlegar.href = `https://www.openstreetmap.org/directions?to=${punto.latitud},${punto.longitud}`;
    comoLlegar.target = '_blank';
    comoLlegar.rel = 'noopener';

    contenedor.append(enlace, comoLlegar);
    return contenedor;
}

async function iniciarMapa() {
    const div = document.getElementById('mapa');
    if (!div) return;

    const mapa = L.map(div).setView(FORMOSA, 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(mapa);

    const lista = document.getElementById('lista-puntos');

    try {
        const respuesta = await fetch(div.dataset.url, { headers: { Accept: 'application/json' } });
        const { data: puntos } = await respuesta.json();

        const marcadores = puntos.map((punto) => {
            const marcador = L.marker([punto.latitud, punto.longitud]).addTo(mapa);
            marcador.bindPopup(armarPopup(punto));

            // Lista accesible debajo del mapa (sirve también sin ver el mapa).
            if (lista) {
                const item = crearElemento('li', null, 'rounded-md bg-white p-3 shadow-sm ring-1 ring-gray-200');
                const boton = crearElemento('button', punto.nombre + ' · ' + punto.feria.nombre, 'text-left font-medium text-verde-800 hover:underline');
                boton.type = 'button';
                boton.addEventListener('click', () => {
                    mapa.setView([punto.latitud, punto.longitud], 16);
                    marcador.openPopup();
                    div.scrollIntoView({ behavior: 'smooth' });
                });
                item.append(boton, crearElemento('p', punto.direccion + ' · ' + punto.horario, 'text-sm text-gray-600'));
                lista.append(item);
            }

            return marcador;
        });

        if (marcadores.length > 0) {
            mapa.fitBounds(L.featureGroup(marcadores).getBounds().pad(0.2));
        }
    } catch (error) {
        div.insertAdjacentElement('afterend', crearElemento('p', 'No se pudieron cargar los puntos de retiro. Probá recargar la página.', 'mt-2 text-sm text-red-600'));
    }
}

iniciarMapa();

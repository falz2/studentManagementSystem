import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapBounds = L.latLngBounds([[-2.2, 28.0], [5.0, 36.3]]);

function createMap(element) {
    const map = L.map(element, {
        center: [1.3733, 32.2903],
        zoom: 6,
        minZoom: 5,
        maxZoom: 19,
        scrollWheelZoom: false,
        zoomControl: true,
    });
    const tileStatus = document.createElement('p');
    let tileErrors = 0;
    tileStatus.className = 'map-tile-status';
    tileStatus.setAttribute('role', 'status');
    tileStatus.setAttribute('aria-live', 'polite');
    tileStatus.textContent = 'Loading detailed street map…';
    element.insertAdjacentElement('afterend', tileStatus);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    })
        .on('tileload', () => {
            if (tileErrors === 0) tileStatus.textContent = 'Detailed street map loaded · © OpenStreetMap contributors';
        })
        .on('tileerror', () => {
            tileErrors += 1;
            tileStatus.textContent = 'Some map tiles could not load. Check your internet connection and retry.';
        })
        .addTo(map);
    L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);
    map.fitBounds(mapBounds, { padding: [12, 12] });

    return map;
}

function popupContent(title, subtitle) {
    const content = document.createElement('div');
    const heading = document.createElement('strong');
    const detail = document.createElement('p');

    heading.textContent = title;
    detail.className = 'mt-1';
    detail.textContent = subtitle;
    content.append(heading, detail);

    return content;
}

function updateMarker(map, marker, location, color = '#4A7C59') {
    if (!location || !Number.isFinite(Number(location.latitude)) || !Number.isFinite(Number(location.longitude))) {
        return marker;
    }

    const coordinates = [Number(location.latitude), Number(location.longitude)];
    if (marker) {
        marker.setLatLng(coordinates);
        return marker;
    }

    const nextMarker = L.circleMarker(coordinates, {
        radius: 9,
        color: '#ffffff',
        weight: 3,
        fillColor: color,
        fillOpacity: 1,
    }).addTo(map);

    return nextMarker;
}

function initializeFarmMaps() {
    document.querySelectorAll('[data-farm-map]').forEach((element) => {
        const map = createMap(element);
        const dataElement = document.getElementById(element.dataset.locationsId);
        const locations = dataElement ? JSON.parse(dataElement.textContent) : [];
        const bounds = [];

        locations.forEach((location) => {
            const coordinates = [Number(location.latitude), Number(location.longitude)];
            const marker = L.circleMarker(coordinates, {
                radius: 8,
                color: '#ffffff',
                weight: 3,
                fillColor: location.type === 'Feed supplier' ? '#C96F4A' : '#4A7C59',
                fillOpacity: 1,
            }).addTo(map);
            marker.bindPopup(popupContent(location.name, location.type));
            bounds.push(coordinates);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [24, 24], maxZoom: 12 });
        }
    });
}

function initializeOrderMaps() {
    document.querySelectorAll('[data-order-delivery-map]').forEach((element) => {
        const map = createMap(element);
        const status = document.querySelector(element.dataset.statusTarget);
        let marker = null;
        let firstLocation = true;

        const loadLocation = async () => {
            try {
                const response = await fetch(element.dataset.locationUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });
                if (!response.ok) {
                    throw new Error('Could not refresh delivery location.');
                }

                const result = await response.json();
                if (!result.active) {
                    if (status) status.textContent = 'Live location sharing has ended.';
                    return;
                }
                if (!result.location) {
                    if (status) status.textContent = 'Waiting for the rider to share a GPS location.';
                    return;
                }

                marker = updateMarker(map, marker, result.location, '#C96F4A');
                marker.bindPopup(popupContent('Delivery rider', `Updated ${new Date(result.location.updated_at).toLocaleTimeString()}`));
                if (firstLocation) {
                    map.setView([result.location.latitude, result.location.longitude], 9);
                    firstLocation = false;
                }
                if (status) {
                    status.textContent = `Last GPS update: ${new Date(result.location.updated_at).toLocaleString()} · accuracy ±${Math.round(result.location.accuracy_meters ?? 0)} m`;
                }
            } catch (error) {
                if (status) status.textContent = 'Could not refresh the map. Check your connection and try again.';
            }
        };

        loadLocation();
        const interval = window.setInterval(loadLocation, 10000);
        window.addEventListener('pagehide', () => window.clearInterval(interval), { once: true });
    });
}

function initializeSellerLocationForm() {
    const form = document.querySelector('[data-seller-location-form]');
    if (!form) return;

    const button = form.querySelector('[data-capture-location]');
    const status = form.querySelector('[data-location-capture-status]');
    button?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            if (status) status.textContent = 'This browser does not support location sharing.';
            return;
        }

        if (status) status.textContent = 'Waiting for location permission…';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                form.querySelector('[name="latitude"]').value = position.coords.latitude.toFixed(7);
                form.querySelector('[name="longitude"]').value = position.coords.longitude.toFixed(7);
                if (status) status.textContent = `Location captured (accuracy ±${Math.round(position.coords.accuracy)} m). Save to continue.`;
            },
            () => {
                if (status) status.textContent = 'Could not access location. Allow location permission and try again.';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 },
        );
    });
}

function initializeRiderSharing() {
    const element = document.querySelector('[data-rider-share-map]');
    if (!element) return;

    const map = createMap(element);
    const status = document.querySelector('[data-rider-share-status]');
    const startButton = document.querySelector('[data-start-location-sharing]');
    const stopButton = document.querySelector('[data-stop-location-sharing]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const token = window.location.hash.slice(1);
    let marker = null;
    let watchId = null;
    let pollInterval = null;
    let lastSentAt = 0;
    let terminal = false;

    const setStatus = (message) => {
        if (status) status.textContent = message;
    };

    const shareRequest = async (coordinates = null) => {
        const response = await fetch(element.dataset.locationUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            credentials: 'same-origin',
            cache: 'no-store',
            body: JSON.stringify({
                token,
                ...(coordinates ?? {}),
            }),
        });

        if (response.status === 404 || response.status === 410) {
            terminal = true;
            throw new Error('This tracking link is invalid, expired, or delivery has ended.');
        }
        if (!response.ok) {
            throw new Error('Could not update the map. Check your connection and try again.');
        }

        return response.json();
    };

    const showLocation = (location) => {
        if (!location) {
            setStatus('Waiting for the rider to share a GPS location.');
            return;
        }

        marker = updateMarker(map, marker, location, '#C96F4A');
        marker.bindPopup(popupContent('Delivery location', `Updated ${new Date(location.updated_at).toLocaleTimeString()}`));
        map.setView([location.latitude, location.longitude], 9);
        setStatus(`Last update: ${new Date(location.updated_at).toLocaleString()} · accuracy ±${Math.round(location.accuracy_meters ?? 0)} m`);
    };

    const pollLocation = async () => {
        if (!/^[a-f0-9]{64}$/i.test(token)) {
            terminal = true;
            setStatus('This private link is invalid. Ask the seller for a new tracking link.');
            return;
        }

        try {
            const result = await shareRequest();
            showLocation(result.location);
        } catch (error) {
            terminal = true;
            setStatus(error.message);
            if (startButton) startButton.disabled = true;
            if (stopButton) stopButton.hidden = true;
        }
    };

    const stopSharing = () => {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        if (startButton) startButton.hidden = false;
        if (stopButton) stopButton.hidden = true;
        setStatus('Location sharing paused on this device. The buyer and seller can still see the last update.');
    };

    startButton?.addEventListener('click', () => {
        if (terminal || !/^[a-f0-9]{64}$/i.test(token)) {
            setStatus('This private link is invalid or has expired. Ask the seller for a new one.');
            return;
        }
        if (!navigator.geolocation) {
            setStatus('This browser does not support GPS location sharing.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            () => {
                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                watchId = navigator.geolocation.watchPosition(async (position) => {
                    const now = Date.now();
                    if (now - lastSentAt < 10000) return;
                    lastSentAt = now;
                    setStatus('Sending your location securely to the PoultryHub order…');

                    try {
                        const result = await shareRequest({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy_meters: position.coords.accuracy,
                        });
                        showLocation(result.location);
                    } catch (error) {
                        terminal = true;
                        setStatus(error.message);
                        stopSharing();
                    }
                }, () => {
                    setStatus('GPS access was denied or unavailable. Check location permission and try again.');
                    stopSharing();
                }, { enableHighAccuracy: true, maximumAge: 5000, timeout: 20000 });
                if (startButton) startButton.hidden = true;
                if (stopButton) stopButton.hidden = false;
                setStatus('GPS sharing is active while this page stays open.');
            },
            () => setStatus('Allow location permission in your browser to start sharing.'),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 },
        );
    });
    stopButton?.addEventListener('click', stopSharing);

    pollLocation();
    pollInterval = window.setInterval(pollLocation, 10000);
    window.addEventListener('pagehide', () => {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        if (pollInterval !== null) window.clearInterval(pollInterval);
    }, { once: true });
}

function initializeCopyButtons() {
    document.querySelectorAll('[data-copy-value]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copyValue;
            try {
                await navigator.clipboard.writeText(value);
                button.textContent = 'Copied';
            } catch (error) {
                const input = document.querySelector('[data-copy-fallback]');
                input?.select();
                setTimeout(() => input?.focus(), 0);
                button.textContent = 'Select and copy the link';
            }
        });
    });
}

initializeFarmMaps();
initializeOrderMaps();
initializeSellerLocationForm();
initializeRiderSharing();
initializeCopyButtons();

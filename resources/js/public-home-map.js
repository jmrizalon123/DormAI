(function () {
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function isDarkTheme() {
        return document.documentElement.getAttribute('data-da-theme') === 'dark';
    }

    function attachBasemap(map, mapEl) {
        var light = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        });

        var dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap &copy; CARTO',
        });

        var active = isDarkTheme() ? dark : light;
        active.addTo(map);

        mapEl._daBasemap = { light: light, dark: dark, active: active };

        window.addEventListener('dormai-theme-change', function (event) {
            var state = mapEl._daBasemap;
            if (!state || !map) {
                return;
            }

            var next = event.detail && event.detail.theme === 'dark' ? state.dark : state.light;
            if (state.active === next) {
                return;
            }

            map.removeLayer(state.active);
            next.addTo(map);
            state.active = next;
        });
    }

    function initHomeMap() {
        if (typeof L === 'undefined') {
            return;
        }

        var mapEl = document.getElementById('publicHomeMap');
        if (!mapEl || mapEl.dataset.daMapInitialized === '1') {
            return;
        }

        mapEl.dataset.daMapInitialized = '1';

        var dataEl = document.getElementById('publicHomeMapData');
        var markers = [];
        if (dataEl && dataEl.textContent) {
            try {
                markers = JSON.parse(dataEl.textContent);
            } catch (error) {
                markers = [];
            }
        }

        var defaultLat = parseFloat(mapEl.dataset.defaultLat || '11.7753');
        var defaultLng = parseFloat(mapEl.dataset.defaultLng || '124.8861');
        var defaultZoom = parseInt(mapEl.dataset.defaultZoom || '13', 10);

        var map = L.map(mapEl, { scrollWheelZoom: false }).setView([defaultLat, defaultLng], defaultZoom);

        attachBasemap(map, mapEl);

        var bounds = [];

        markers.forEach(function (item) {
            if (!Number.isFinite(item.lat) || !Number.isFinite(item.lng)) {
                return;
            }

            var marker = L.marker([item.lat, item.lng]).addTo(map);

            var popupHtml =
                '<div class="da-map-popup">' +
                '<div class="fw-semibold">' +
                escapeHtml(item.name) +
                '</div>';

            if (item.location) {
                popupHtml += '<div class="small text-muted mt-1">' + escapeHtml(item.location) + '</div>';
            }

            if (item.rate) {
                popupHtml += '<div class="small fw-semibold text-primary mt-1">' + escapeHtml(item.rate) + '</div>';
            }

            popupHtml +=
                '<a href="' +
                escapeHtml(item.url) +
                '" class="btn btn-sm btn-primary mt-2 w-100">View listing</a>' +
                '</div>';

            marker.bindPopup(popupHtml);
            bounds.push([item.lat, item.lng]);
        });

        if (bounds.length === 1) {
            map.setView(bounds[0], Math.max(defaultZoom, 14));
        } else if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [24, 24], maxZoom: 15 });
        }

        setTimeout(function () {
            map.invalidateSize();
        }, 200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHomeMap);
    } else {
        initHomeMap();
    }
})();

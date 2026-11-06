(function () {
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function initPortfolioMap() {
        if (typeof L === 'undefined') {
            return;
        }

        var mapEl = document.getElementById('organizationPropertiesMap');
        if (!mapEl || mapEl.dataset.daMapInitialized === '1') {
            return;
        }

        mapEl.dataset.daMapInitialized = '1';

        var dataEl = document.getElementById('organizationPropertiesMapData');
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

        var map = L.map(mapEl, { scrollWheelZoom: true }).setView([defaultLat, defaultLng], defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

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

            if (item.code) {
                popupHtml += '<div class="text-muted small">' + escapeHtml(item.code) + '</div>';
            }

            if (item.location) {
                popupHtml += '<div class="small mt-1">' + escapeHtml(item.location) + '</div>';
            }

            if (item.status) {
                popupHtml += '<div class="small text-muted">' + escapeHtml(item.status) + '</div>';
            }

            popupHtml +=
                '<a href="' +
                escapeHtml(item.url) +
                '" class="btn btn-sm btn-outline-primary mt-2 w-100">View property</a>' +
                '</div>';

            marker.bindPopup(popupHtml);
            bounds.push([item.lat, item.lng]);
        });

        if (bounds.length === 1) {
            map.setView(bounds[0], Math.max(defaultZoom, 14));
        } else if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [48, 48], maxZoom: 15 });
        }

        if (window.DormaiMapFullscreen) {
            window.DormaiMapFullscreen.attach(map, mapEl);
        }

        window.setTimeout(function () {
            map.invalidateSize();
        }, 200);

        window.addEventListener('resize', function () {
            map.invalidateSize();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPortfolioMap);
    } else {
        initPortfolioMap();
    }
})();

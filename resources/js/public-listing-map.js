(function () {
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

    function initListingMap() {
        if (typeof L === 'undefined') {
            return;
        }

        var mapEl = document.getElementById('publicListingMap');
        if (!mapEl || mapEl.dataset.daMapInitialized === '1') {
            return;
        }

        mapEl.dataset.daMapInitialized = '1';

        var lat = parseFloat(mapEl.dataset.lat);
        var lng = parseFloat(mapEl.dataset.lng);
        var zoom = parseInt(mapEl.dataset.zoom || '15', 10);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }

        var map = L.map(mapEl, { scrollWheelZoom: false }).setView([lat, lng], zoom);

        attachBasemap(map, mapEl);

        L.marker([lat, lng]).addTo(map);

        setTimeout(function () {
            map.invalidateSize();
        }, 200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initListingMap);
    } else {
        initListingMap();
    }
})();

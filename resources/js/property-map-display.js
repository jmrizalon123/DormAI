(function () {
    function initDisplayMaps() {
        if (typeof L === 'undefined') {
            return;
        }

        document.querySelectorAll('.da-property-map-display').forEach(function (el) {
            if (el.dataset.daMapInitialized === '1') {
                return;
            }

            el.dataset.daMapInitialized = '1';

            var lat = parseFloat(el.dataset.lat);
            var lng = parseFloat(el.dataset.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }

            var map = L.map(el, { scrollWheelZoom: true }).setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(map);

            L.marker([lat, lng]).addTo(map).bindPopup(el.dataset.title || 'Property');

            if (window.DormaiMapFullscreen) {
                window.DormaiMapFullscreen.attach(map, el);
            }

            window.setTimeout(function () {
                map.invalidateSize();
            }, 150);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDisplayMaps);
    } else {
        initDisplayMaps();
    }
})();

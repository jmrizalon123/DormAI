(function () {
    function parseCoord(value) {
        var n = parseFloat(value);
        return Number.isFinite(n) ? n : null;
    }

    function mapToast(type, message) {
        if (window.DormaiToast && typeof window.DormaiToast[type] === 'function') {
            window.DormaiToast[type](message);
            return;
        }

        window.setTimeout(function () {
            if (window.DormaiToast && typeof window.DormaiToast[type] === 'function') {
                window.DormaiToast[type](message);
            }
        }, 100);
    }

    function initMapPicker(mapEl) {
        if (typeof L === 'undefined') {
            return;
        }

        var latInput = document.getElementById(mapEl.dataset.latInput);
        var lngInput = document.getElementById(mapEl.dataset.lngInput);
        var coordsLabel = mapEl.dataset.coordsLabel
            ? document.getElementById(mapEl.dataset.coordsLabel)
            : null;
        var geocodeBtn = mapEl.dataset.geocodeButton
            ? document.getElementById(mapEl.dataset.geocodeButton)
            : null;
        var clearBtn = mapEl.dataset.clearButton
            ? document.getElementById(mapEl.dataset.clearButton)
            : null;
        var form = mapEl.closest('form');

        if (!latInput || !lngInput) {
            return;
        }

        var defaults = {
            lat: parseCoord(mapEl.dataset.defaultLat) || 11.7753,
            lng: parseCoord(mapEl.dataset.defaultLng) || 124.8861,
            zoom: parseInt(mapEl.dataset.defaultZoom || '13', 10),
        };

        var initialLat = parseCoord(latInput.value);
        var initialLng = parseCoord(lngInput.value);

        var map = L.map(mapEl, { scrollWheelZoom: true }).setView(
            initialLat !== null && initialLng !== null ? [initialLat, initialLng] : [defaults.lat, defaults.lng],
            initialLat !== null && initialLng !== null ? Math.max(defaults.zoom, 14) : defaults.zoom,
        );

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        var marker = null;
        var reverseTimer = null;
        var reverseRequestId = 0;

        function setField(name, value) {
            if (!form || value === null || value === undefined || value === '') {
                return;
            }
            var field = form.querySelector('[name="' + name + '"]');
            if (field) {
                field.value = value;
            }
        }

        function fillAddressFromReverse(body) {
            setField('address_line1', body.address_line1);
            setField('address_line2', body.address_line2);
            setField('city', body.city);
            setField('province', body.province);
            setField('postal_code', body.postal_code);
        }

        function reverseGeocode(lat, lng) {
            if (!mapEl.dataset.reverseGeocodeUrl || !form) {
                mapToast('success', 'Location pinned successfully.');
                return;
            }

            clearTimeout(reverseTimer);
            reverseTimer = window.setTimeout(function () {
                var requestId = ++reverseRequestId;
                var params = new URLSearchParams({
                    latitude: String(lat),
                    longitude: String(lng),
                });

                fetch(mapEl.dataset.reverseGeocodeUrl + '?' + params.toString(), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then(function (res) {
                        return res.json().then(function (body) {
                            return { ok: res.ok, body: body };
                        });
                    })
                    .then(function (result) {
                        if (requestId !== reverseRequestId) {
                            return;
                        }
                        if (!result.ok) {
                            mapToast(
                                'warning',
                                'Location pinned. We could not auto-fill the address for this spot.',
                            );
                            return;
                        }
                        fillAddressFromReverse(result.body);
                        mapToast('success', 'Location pinned successfully. Address fields were updated.');
                    })
                    .catch(function () {
                        if (requestId !== reverseRequestId) {
                            return;
                        }
                        mapToast('warning', 'Location pinned. Address lookup is temporarily unavailable.');
                    });
            }, 450);
        }

        function writeCoords(lat, lng, options) {
            options = options || {};
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            if (coordsLabel) {
                coordsLabel.textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
                coordsLabel.classList.add('is-set');
            }
            latInput.dispatchEvent(new Event('change', { bubbles: true }));

            if (options.reverse) {
                reverseGeocode(lat, lng);
            }
        }

        function setMarker(lat, lng, pan, options) {
            options = options || {};
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    var pos = marker.getLatLng();
                    writeCoords(pos.lat, pos.lng, { reverse: true });
                });
            }
            if (pan) {
                map.setView([lat, lng], Math.max(map.getZoom(), 14));
            }
            writeCoords(lat, lng, { reverse: options.reverse !== false });
        }

        function clearMarker() {
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            reverseRequestId++;
            clearTimeout(reverseTimer);
            latInput.value = '';
            lngInput.value = '';
            if (coordsLabel) {
                coordsLabel.textContent = mapEl.dataset.emptyLabel || 'No pin set';
                coordsLabel.classList.remove('is-set');
            }
        }

        map.on('click', function (e) {
            setMarker(e.latlng.lat, e.latlng.lng, false, { reverse: true });
        });

        if (initialLat !== null && initialLng !== null) {
            setMarker(initialLat, initialLng, false, { reverse: false });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', clearMarker);
        }

        if (geocodeBtn && mapEl.dataset.geocodeUrl) {
            geocodeBtn.addEventListener('click', function () {
                geocodeBtn.disabled = true;
                var params = new URLSearchParams({
                    address_line1: form?.querySelector('[name="address_line1"]')?.value || '',
                    address_line2: form?.querySelector('[name="address_line2"]')?.value || '',
                    city: form?.querySelector('[name="city"]')?.value || '',
                    province: form?.querySelector('[name="province"]')?.value || '',
                    postal_code: form?.querySelector('[name="postal_code"]')?.value || '',
                });

                fetch(mapEl.dataset.geocodeUrl + '?' + params.toString(), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then(function (res) {
                        return res.json().then(function (body) {
                            return { ok: res.ok, body: body };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            mapToast('warning', result.body.message || 'Could not find that address.');
                            return;
                        }
                        setMarker(result.body.latitude, result.body.longitude, true, { reverse: false });
                        mapToast('success', 'Map updated from your address.');
                    })
                    .catch(function () {
                        mapToast('warning', 'Geocoding failed. Click the map to set the pin manually.');
                    })
                    .finally(function () {
                        geocodeBtn.disabled = false;
                    });
            });
        }

        var tabTarget = mapEl.dataset.tabTarget;
        if (tabTarget) {
            document.querySelectorAll('[data-bs-target="' + tabTarget + '"]').forEach(function (tabBtn) {
                tabBtn.addEventListener('shown.bs.tab', function () {
                    window.setTimeout(function () {
                        map.invalidateSize();
                    }, 80);
                });
            });
        }

        window.setTimeout(function () {
            map.invalidateSize();
        }, 200);

        if (window.DormaiMapFullscreen) {
            window.DormaiMapFullscreen.attach(map, mapEl);
        }

        mapEl._daLeafletMap = map;
    }

    function initPropertyMapPickers() {
        document.querySelectorAll('[data-da-map-picker]').forEach(initMapPicker);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPropertyMapPickers);
    } else {
        initPropertyMapPickers();
    }
})();

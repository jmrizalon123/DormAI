(function () {
    function ensureShell(mapEl) {
        var shell = mapEl.closest('.da-map-shell');
        if (shell) {
            return shell;
        }

        shell = document.createElement('div');
        shell.className = 'da-map-shell position-relative';
        mapEl.parentNode.insertBefore(shell, mapEl);
        shell.appendChild(mapEl);

        return shell;
    }

    function setButtonState(button, isFullscreen) {
        if (!button) {
            return;
        }

        button.innerHTML = isFullscreen
            ? '<i class="bi bi-fullscreen-exit" aria-hidden="true"></i>'
            : '<i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>';
        button.title = isFullscreen ? 'Exit full screen' : 'Full screen';
        button.setAttribute('aria-label', button.title);
    }

    function resizeMap(map) {
        if (!map) {
            return;
        }

        if (typeof map.invalidateSize === 'function') {
            map.invalidateSize();
        } else if (typeof map.resize === 'function') {
            map.resize();
        }
    }

    function attachMapFullscreen(map, mapEl) {
        if (!map || !mapEl || mapEl.dataset.daFullscreenAttached === '1') {
            return;
        }

        if (typeof L === 'undefined') {
            return;
        }

        mapEl.dataset.daFullscreenAttached = '1';
        var shell = ensureShell(mapEl);

        var control = L.control({ position: 'topright' });
        control.onAdd = function () {
            var wrap = L.DomUtil.create('div', 'leaflet-bar da-map-fs-control');
            var button = L.DomUtil.create('button', 'da-map-fs-btn', wrap);
            button.type = 'button';
            setButtonState(button, false);

            L.DomEvent.disableClickPropagation(wrap);
            L.DomEvent.on(button, 'click', function (event) {
                L.DomEvent.preventDefault(event);
                L.DomEvent.stopPropagation(event);

                if (document.fullscreenElement === shell) {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                    return;
                }

                var request = shell.requestFullscreen || shell.webkitRequestFullscreen;
                if (request) {
                    request.call(shell);
                }
            });

            shell._daFullscreenButton = button;
            return wrap;
        };
        control.addTo(map);

        var onFullscreenChange = function () {
            var isActive = document.fullscreenElement === shell;
            setButtonState(shell._daFullscreenButton, isActive);
            window.setTimeout(function () {
                resizeMap(map);
            }, 100);
        };

        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);
    }

    window.DormaiMapFullscreen = {
        attach: attachMapFullscreen,
        resizeMap: resizeMap,
    };
})();

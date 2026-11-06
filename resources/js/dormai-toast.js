(function () {
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function ensureHost() {
        var host = document.getElementById('daToastHost');
        if (host) {
            return host;
        }

        host = document.createElement('div');
        host.id = 'daToastHost';
        host.className = 'da-toast-host';
        host.setAttribute('aria-live', 'polite');
        host.setAttribute('aria-atomic', 'true');
        document.body.appendChild(host);

        return host;
    }

    function variantMeta(variant) {
        if (variant === 'success') {
            return { icon: 'bi-check-circle-fill', mod: 'da-toast-item--success' };
        }
        if (variant === 'warning') {
            return { icon: 'bi-exclamation-triangle-fill', mod: 'da-toast-item--warning' };
        }
        if (variant === 'danger') {
            return { icon: 'bi-x-circle-fill', mod: 'da-toast-item--danger' };
        }

        return { icon: 'bi-info-circle-fill', mod: 'da-toast-item--info' };
    }

    function show(message, variant) {
        var run = function () {
            if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
                return false;
            }

            var meta = variantMeta(variant);
            var host = ensureHost();
            var toastEl = document.createElement('div');
            toastEl.className = 'toast da-toast-item border-0 shadow ' + meta.mod;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML =
                '<div class="d-flex align-items-start gap-2">' +
                '<i class="bi ' + meta.icon + ' da-toast-item__icon flex-shrink-0" aria-hidden="true"></i>' +
                '<div class="toast-body flex-grow-1 p-0">' + escapeHtml(message) + '</div>' +
                '<button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>';

            host.appendChild(toastEl);

            var toast = bootstrap.Toast.getOrCreateInstance(toastEl, {
                delay: variant === 'danger' ? 6500 : 4500,
                autohide: true,
            });

            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });

            toast.show();

            return true;
        };

        if (run()) {
            return;
        }

        window.setTimeout(function () {
            run();
        }, 50);
    }

    window.DormaiToast = {
        show: show,
        success: function (message) {
            show(message, 'success');
        },
        info: function (message) {
            show(message, 'info');
        },
        warning: function (message) {
            show(message, 'warning');
        },
        danger: function (message) {
            show(message, 'danger');
        },
    };
})();

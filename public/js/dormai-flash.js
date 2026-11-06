(function () {
    function showFlash(type, message) {
        if (!window.DormaiToast || !message) {
            return;
        }

        if (type === 'success') {
            DormaiToast.success(message);
            return;
        }

        if (type === 'warning') {
            DormaiToast.warning(message);
            return;
        }

        if (type === 'info') {
            DormaiToast.info(message);
            return;
        }

        DormaiToast.danger(message);
    }

    function run() {
        var payloadEl = document.getElementById('daFlashMessages');
        if (!payloadEl) {
            return;
        }

        var messages = [];

        try {
            messages = JSON.parse(payloadEl.textContent || '[]');
        } catch (e) {
            return;
        }

        if (!Array.isArray(messages)) {
            return;
        }

        messages.forEach(function (entry) {
            if (!entry || !entry.message) {
                return;
            }

            showFlash(entry.type || 'info', entry.message);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();

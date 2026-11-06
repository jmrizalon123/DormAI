/**
 * Thin top loading bar for same-origin navigation (sidebar / topbar links, forms).
 */
(function () {
    const bar = document.getElementById('da-topbar-progress');
    if (!bar) {
        return;
    }

    const fill = bar.querySelector('.da-topbar-progress-fill');
    if (!fill) {
        return;
    }

    let trickleTimer = null;
    let progress = 0;
    let active = false;

    function setWidth(value) {
        fill.style.width = `${Math.min(100, Math.max(0, value))}%`;
    }

    function start() {
        if (active) {
            return;
        }
        active = true;
        progress = 8;
        bar.classList.add('is-active');
        bar.setAttribute('aria-hidden', 'false');
        setWidth(progress);
        trickle();
    }

    function trickle() {
        clearInterval(trickleTimer);
        trickleTimer = window.setInterval(() => {
            if (progress < 92) {
                progress += Math.random() * 12;
                if (progress > 92) {
                    progress = 92;
                }
                setWidth(progress);
            }
        }, 180);
    }

    function finish() {
        clearInterval(trickleTimer);
        trickleTimer = null;
        if (!active) {
            return;
        }
        progress = 100;
        setWidth(100);
        window.setTimeout(() => {
            bar.classList.remove('is-active');
            bar.setAttribute('aria-hidden', 'true');
            setWidth(0);
            progress = 0;
            active = false;
        }, 280);
    }

    function shouldTrackAnchor(anchor) {
        if (anchor.hasAttribute('data-no-progress')) {
            return false;
        }
        if (anchor.target === '_blank' || anchor.hasAttribute('download')) {
            return false;
        }
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
            return false;
        }
        if (anchor.dataset.bsToggle !== undefined || anchor.getAttribute('data-bs-toggle')) {
            return false;
        }
        try {
            const url = new URL(anchor.href, window.location.href);

            return url.origin === window.location.origin;
        } catch {
            return false;
        }
    }

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a');
        if (anchor && shouldTrackAnchor(anchor)) {
            start();
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.target === '_blank') {
            return;
        }
        if (form.hasAttribute('data-no-progress')) {
            return;
        }
        start();
    });

    window.addEventListener('load', finish);
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            finish();
        }
    });

    if (document.readyState === 'complete') {
        finish();
    }
})();

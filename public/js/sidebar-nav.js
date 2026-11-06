/**
 * Sidebar nav groups — custom toggle (Bootstrap Collapse breaks on nested UL height).
 */
(function () {
    const storageKey = 'dormai.sidebar.navGroups.v2';

    function readState() {
        try {
            return JSON.parse(localStorage.getItem(storageKey) || '{}');
        } catch {
            return {};
        }
    }

    function writeState(state) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(state));
        } catch {
            /* ignore */
        }
    }

    function setOpen(toggle, panel, open) {
        panel.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function init() {
        const savedAll = readState();

        document.querySelectorAll('[data-nav-group-panel]').forEach((panel) => {
            const id = panel.getAttribute('data-nav-group-panel');
            const toggle = document.querySelector(`[data-nav-group-toggle="${id}"]`);
            if (!toggle || !id) {
                return;
            }

            const routeOpen = panel.classList.contains('is-open');
            const saved = savedAll[id];

            if (routeOpen) {
                setOpen(toggle, panel, true);
            } else if (saved === true) {
                setOpen(toggle, panel, true);
            } else if (saved === false) {
                setOpen(toggle, panel, false);
            }

            toggle.addEventListener('click', (event) => {
                const collapsedRail =
                    document.body.classList.contains('sidebar-collapsed') &&
                    window.matchMedia('(min-width: 992px)').matches;

                if (collapsedRail) {
                    event.preventDefault();
                    return;
                }

                const willOpen = !panel.classList.contains('is-open');
                setOpen(toggle, panel, willOpen);
                const state = readState();
                state[id] = willOpen;
                writeState(state);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

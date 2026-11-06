/**
 * Desktop: collapse/expand sidebar. Mobile: Bootstrap offcanvas (existing markup).
 */
(function () {
    const storageKey = 'dormai.sidebar.collapsed.v1';
    const body = document.body;
    const toggle = document.getElementById('daSidebarToggle');
    const sidebar = document.getElementById('appSidebar');

    if (!toggle || !sidebar) {
        return;
    }

    const desktopQuery = window.matchMedia('(min-width: 992px)');

    function isDesktop() {
        return desktopQuery.matches;
    }

    function syncBrandLogoViewBox(collapsed) {
        const useMark = collapsed && isDesktop();

        document.querySelectorAll('.da-sidebar-brand-block .da-logo[data-da-logo-view-full]').forEach((logo) => {
            const full = logo.getAttribute('data-da-logo-view-full');
            const mark = logo.getAttribute('data-da-logo-view-mark') || '0 0 36 36';
            logo.setAttribute('viewBox', useMark ? mark : full);
        });
    }

    function setCollapsed(collapsed) {
        body.classList.toggle('sidebar-collapsed', collapsed);
        syncBrandLogoViewBox(collapsed);
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar menu' : 'Collapse sidebar menu');
        toggle.setAttribute('title', collapsed ? 'Expand menu' : 'Collapse menu');

        document.querySelectorAll('[data-sidebar-tooltip]').forEach((el) => {
            el.removeAttribute('title');
        });

        document.querySelectorAll('[data-nav-group-panel].is-open').forEach((panel) => {
            if (!collapsed) {
                return;
            }
            const id = panel.getAttribute('data-nav-group-panel');
            const groupToggle = document.querySelector(`[data-nav-group-toggle="${id}"]`);
            panel.classList.remove('is-open');
            groupToggle?.setAttribute('aria-expanded', 'false');
        });
    }

    function readCollapsedPreference() {
        try {
            return localStorage.getItem(storageKey) === '1';
        } catch {
            return false;
        }
    }

    function saveCollapsedPreference(collapsed) {
        try {
            localStorage.setItem(storageKey, collapsed ? '1' : '0');
        } catch {
            /* ignore */
        }
    }

    function syncMobileToggleLabels() {
        if (isDesktop()) {
            return;
        }

        const open = sidebar.classList.contains('show');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        toggle.setAttribute('title', open ? 'Close menu' : 'Menu');
    }

    function applyDesktopState() {
        if (!isDesktop()) {
            body.classList.remove('sidebar-collapsed');
            syncBrandLogoViewBox(false);
            syncMobileToggleLabels();
            return;
        }

        setCollapsed(readCollapsedPreference());
    }

    toggle.addEventListener('click', (event) => {
        event.preventDefault();

        if (isDesktop()) {
            const willCollapse = !body.classList.contains('sidebar-collapsed');
            setCollapsed(willCollapse);
            saveCollapsedPreference(willCollapse);
            return;
        }

        const offcanvas = window.bootstrap?.Offcanvas?.getOrCreateInstance(sidebar);
        offcanvas?.toggle();
    });

    desktopQuery.addEventListener('change', applyDesktopState);

    sidebar.addEventListener('shown.bs.offcanvas', syncMobileToggleLabels);
    sidebar.addEventListener('hidden.bs.offcanvas', syncMobileToggleLabels);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyDesktopState);
    } else {
        applyDesktopState();
    }
})();

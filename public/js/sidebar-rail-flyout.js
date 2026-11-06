/**
 * Collapsed sidebar: fixed-position hover flyouts (avoids overflow clipping).
 */
(function () {
    const desktopQuery = window.matchMedia('(min-width: 992px)');
    let closeTimer = null;
    let openPanel = null;
    let openTrigger = null;

    function isCollapsedRail() {
        return document.body.classList.contains('sidebar-collapsed') && desktopQuery.matches;
    }

    function flyoutGap() {
        return 6;
    }

    function placePanel(trigger, panel) {
        const rect = trigger.getBoundingClientRect();
        const gap = flyoutGap();

        panel.classList.add('da-rail-flyout-visible');
        panel.style.position = 'fixed';
        panel.style.visibility = 'hidden';
        panel.style.left = '-9999px';
        panel.style.top = '0';
        panel.style.zIndex = '1100';
        panel.style.transform = 'none';

        const panelHeight = panel.offsetHeight;
        let top = rect.top;

        if (panel.classList.contains('da-sidebar-flyout-panel')) {
            top = rect.top + rect.height / 2 - panelHeight / 2;
        }

        const maxTop = window.innerHeight - panelHeight - 8;
        top = Math.max(8, Math.min(top, maxTop));

        panel.style.left = `${Math.round(rect.right + gap)}px`;
        panel.style.top = `${Math.round(top)}px`;
        panel.style.visibility = '';
    }

    function closeFlyouts() {
        document.querySelectorAll('.da-rail-flyout-visible').forEach((panel) => {
            panel.classList.remove('da-rail-flyout-visible');
            panel.style.position = '';
            panel.style.left = '';
            panel.style.top = '';
            panel.style.transform = '';
            panel.style.zIndex = '';
        });
        openPanel = null;
        openTrigger = null;
    }

    function scheduleClose() {
        clearTimeout(closeTimer);
        closeTimer = window.setTimeout(closeFlyouts, 100);
    }

    function cancelClose() {
        clearTimeout(closeTimer);
    }

    function openFlyout(trigger, panel) {
        if (!isCollapsedRail()) {
            return;
        }

        cancelClose();

        if (openPanel && openPanel !== panel) {
            closeFlyouts();
        }

        openPanel = panel;
        openTrigger = trigger;
        placePanel(trigger, panel);
    }

    function repositionOpen() {
        if (openPanel && openTrigger && openPanel.classList.contains('da-rail-flyout-visible')) {
            placePanel(openTrigger, openPanel);
        }
    }

    function bindGroup(group) {
        const toggle = group.querySelector('[data-nav-group-toggle]');
        const panel = group.querySelector('[data-nav-group-panel]');
        if (!toggle || !panel) {
            return;
        }

        group.addEventListener('mouseenter', () => openFlyout(toggle, panel));
        group.addEventListener('mouseleave', scheduleClose);
        panel.addEventListener('mouseenter', cancelClose);
        panel.addEventListener('mouseleave', scheduleClose);
    }

    function bindFooterAnchor(anchor) {
        const panel = anchor.querySelector('.da-sidebar-flyout-panel');
        const trigger =
            anchor.querySelector('.da-sidebar-user-card') ||
            anchor.querySelector('.da-sidebar-footer-link');

        if (!panel || !trigger) {
            return;
        }

        anchor.addEventListener('mouseenter', () => openFlyout(trigger, panel));
        anchor.addEventListener('mouseleave', scheduleClose);
        panel.addEventListener('mouseenter', cancelClose);
        panel.addEventListener('mouseleave', scheduleClose);
    }

    function init() {
        document.querySelectorAll('.da-nav-group').forEach(bindGroup);
        document.querySelectorAll('.da-sidebar-flyout-anchor').forEach(bindFooterAnchor);

        window.addEventListener('scroll', repositionOpen, true);
        window.addEventListener('resize', repositionOpen);
        desktopQuery.addEventListener('change', closeFlyouts);

        document.getElementById('daSidebarToggle')?.addEventListener('click', () => {
            window.setTimeout(closeFlyouts, 280);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

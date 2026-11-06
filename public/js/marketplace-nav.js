/**
 * Marketplace header: scroll state over hero; mobile menu open class and a11y.
 */
(function () {
    var nav = document.getElementById('daMarketplaceNav');
    var collapseEl = document.getElementById('daMarketplaceNavCollapse');

    if (!nav) {
        return;
    }

    var scrollOffset = 20;
    var ticking = false;

    function updateNav() {
        if (nav.classList.contains('da-marketplace-nav--over-hero')) {
            nav.classList.toggle('da-marketplace-nav--scrolled', window.scrollY > scrollOffset);
        }
        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(updateNav);
        }
    }

    if (nav.classList.contains('da-marketplace-nav--over-hero')) {
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', updateNav);
        updateNav();
    }

    if (!collapseEl) {
        return;
    }

    var toggleBtn = document.querySelector('[data-bs-target="#daMarketplaceNavCollapse"]');

    function syncToggleFromCollapse() {
        var open = collapseEl.classList.contains('show');
        nav.classList.toggle('da-marketplace-nav--open', open);
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggleBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        }
    }

    collapseEl.addEventListener('shown.bs.collapse', syncToggleFromCollapse);
    collapseEl.addEventListener('hidden.bs.collapse', syncToggleFromCollapse);
    syncToggleFromCollapse();

    collapseEl.querySelectorAll('a.da-marketplace-nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (typeof bootstrap === 'undefined' || !bootstrap.Collapse) {
                return;
            }

            var href = link.getAttribute('href') || '';
            var hashOnly = href.charAt(0) === '#' && href.length > 1;

            if (!hashOnly) {
                return;
            }

            window.requestAnimationFrame(function () {
                var inst = bootstrap.Collapse.getInstance(collapseEl);
                if (inst && collapseEl.classList.contains('show')) {
                    inst.hide();
                }
            });
        });
    });
})();

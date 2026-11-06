(function () {
    var modalEl = document.getElementById('daMarketplaceSearchModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }

    function openSearchModal() {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    if (window.location.hash === '#hero-search') {
        if (window.matchMedia('(max-width: 991.98px)').matches) {
            openSearchModal();
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        var first = modalEl.querySelector('#search-q-modal');
        if (first) {
            first.focus();
        }
    });
})();

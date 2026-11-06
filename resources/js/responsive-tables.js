/**
 * Wrap data tables that are not already inside .table-responsive for horizontal scroll on small screens.
 */
(function () {
    function wrapTables() {
        var root = document.querySelector('.da-app main');
        if (!root) {
            return;
        }

        root.querySelectorAll('table.table').forEach(function (table) {
            if (table.closest('.table-responsive')) {
                return;
            }

            var parent = table.parentElement;
            if (!parent || parent.classList.contains('da-table-wrap')) {
                return;
            }

            var wrap = document.createElement('div');
            wrap.className = 'table-responsive da-table-wrap';
            parent.insertBefore(wrap, table);
            wrap.appendChild(table);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wrapTables);
    } else {
        wrapTables();
    }
})();

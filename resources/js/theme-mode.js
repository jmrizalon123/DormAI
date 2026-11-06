/**
 * App shell & marketplace light / dark mode.
 */
(function () {
    const storageKey = 'dormai.theme.v1';
    const root = document.documentElement;

    function readTheme() {
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved === 'dark' || saved === 'light') {
                return saved;
            }
        } catch {
            /* ignore */
        }
        return 'light';
    }

    function saveTheme(theme) {
        try {
            localStorage.setItem(storageKey, theme);
        } catch {
            /* ignore */
        }
    }

    function updateToggleControl(toggle, theme) {
        if (!toggle) {
            return;
        }

        toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');

        const icon = toggle.querySelector('[data-theme-icon]') || toggle.querySelector('.da-sidebar-footer-icon');
        const labels = toggle.querySelectorAll('[data-theme-label]');

        const labelText = theme === 'dark' ? 'Light mode' : 'Dark mode';
        const shortLabel = theme === 'dark' ? 'Light' : 'Dark';

        labels.forEach(function (label) {
            label.textContent = label.classList.contains('da-marketplace-theme-label') ? shortLabel : labelText;
        });

        if (icon) {
            icon.classList.remove('bi-brightness-high', 'bi-moon-stars', 'bi-sun');

            if (theme === 'dark') {
                icon.classList.add('bi-sun');
            } else if (toggle.id === 'daThemeToggle') {
                icon.classList.add('bi-brightness-high');
            } else {
                icon.classList.add('bi-moon-stars');
            }
        }
    }

    function applyTheme(theme) {
        root.setAttribute('data-da-theme', theme);
        root.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');

        document.querySelectorAll('[data-da-theme-toggle], #daThemeToggle').forEach(function (toggle) {
            updateToggleControl(toggle, theme);
        });

        window.dispatchEvent(new CustomEvent('dormai-theme-change', { detail: { theme: theme } }));
    }

    function init() {
        applyTheme(readTheme());

        document.querySelectorAll('[data-da-theme-toggle], #daThemeToggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const next = readTheme() === 'dark' ? 'light' : 'dark';
                saveTheme(next);
                applyTheme(next);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

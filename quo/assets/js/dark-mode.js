/**
 * Dark Mode Toggle — Gravitti Quotation System
 * Persists user preference via localStorage
 */
(function() {
    const toggle = document.getElementById('darkModeToggle');
    if (!toggle) return;

    const icon = toggle.querySelector('i');

    function updateIcon(isDark) {
        if (icon) {
            icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        }
    }

    // Read current state from <html> attribute (set by inline script in <head>)
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    updateIcon(isDark);

    toggle.addEventListener('click', function() {
        const currentlyDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const newTheme = currentlyDark ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('gv-theme', newTheme);
        updateIcon(newTheme === 'dark');
    });
})();

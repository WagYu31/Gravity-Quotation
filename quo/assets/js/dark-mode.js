/**
 * Dark Mode Toggle — Gravitti Quotation System
 * Animated sliding switch with sun/moon icons
 * Persists user preference via localStorage
 */
(function() {
    const toggle = document.getElementById('darkModeToggle');
    if (!toggle) return;

    const sunIcon = toggle.querySelector('.icon-sun');
    const moonIcon = toggle.querySelector('.icon-moon');

    function updateIcons(isDark) {
        if (sunIcon && moonIcon) {
            sunIcon.style.display = isDark ? 'none' : 'inline';
            moonIcon.style.display = isDark ? 'inline' : 'none';
        }
    }

    // Initialize from current state
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    updateIcons(isDark);

    toggle.addEventListener('click', function() {
        const currentlyDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const newTheme = currentlyDark ? 'light' : 'dark';

        // Apply theme
        if (newTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }

        // Save preference
        localStorage.setItem('gv-theme', newTheme);

        // Update icons
        updateIcons(newTheme === 'dark');
    });
})();

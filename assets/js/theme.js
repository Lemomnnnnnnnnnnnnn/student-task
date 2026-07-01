// assets/js/theme.js
// Shared dark mode logic for every page. Saves the choice in localStorage
// so it "remembers" and applies automatically on every page you visit.

// Apply saved theme immediately (before the page fully renders, to avoid flashing)
(function () {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') {
        document.documentElement.classList.add('dark');
    }
})();

// Called by the 🌙/☀️ button on every page
function toggleTheme() {
    document.documentElement.classList.toggle('dark');
    const isDark = document.documentElement.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateToggleIcon();
}

// Keep the button icon in sync with the current theme
function updateToggleIcon() {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('.theme-toggle').forEach(function (btn) {
        btn.textContent = isDark ? '☀️' : '🌙';
    });
}

document.addEventListener('DOMContentLoaded', updateToggleIcon);

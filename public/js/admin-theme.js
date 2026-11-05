const adminThemeRoot = document.documentElement;
const adminThemeStorageKey = 'sewaride-admin-theme';

function applyAdminTheme(theme) {
    const isDark = theme === 'dark';

    adminThemeRoot.classList.toggle('admin-theme-dark', isDark);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const nextTheme = isDark ? 'Light Mode' : 'Dark Mode';
        const currentTheme = isDark ? 'Dark Mode' : 'Light Mode';

        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('aria-label', `Tampilan saat ini: ${currentTheme}. Ganti ke ${nextTheme}.`);
        button.setAttribute('title', `Ganti ke ${nextTheme}`);

        const label = button.querySelector('[data-theme-label]');

        if (label) {
            label.textContent = currentTheme;
        }
    });
}

applyAdminTheme(window.localStorage.getItem(adminThemeStorageKey) === 'dark' ? 'dark' : 'light');

document.addEventListener('DOMContentLoaded', () => {
    applyAdminTheme(adminThemeRoot.classList.contains('admin-theme-dark') ? 'dark' : 'light');

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = adminThemeRoot.classList.contains('admin-theme-dark') ? 'light' : 'dark';

            window.localStorage.setItem(adminThemeStorageKey, nextTheme);
            applyAdminTheme(nextTheme);
        });
    });
});

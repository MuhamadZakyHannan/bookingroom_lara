import './bootstrap';

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-toggle]')) return;

    const darkMode = document.documentElement.classList.toggle('dark');
    localStorage.theme = darkMode ? 'dark' : 'light';
});

(() => {
    'use strict';

    const clockEl = document.getElementById('kiosque-clock');
    const dateEl = document.getElementById('kiosque-date');

    const updateClock = () => {
        const now = new Date();
        if (clockEl) {
            clockEl.textContent = now.toLocaleTimeString('fr-FR', { hour12: false });
        }
        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        }
    };

    updateClock();
    setInterval(updateClock, 1000);
})();


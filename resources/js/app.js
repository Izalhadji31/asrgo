

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('paymentStatusWatcher', (statusUrl) => ({
    timer: null,

    init() {
        this.timer = window.setInterval(() => this.checkStatuses(), 5000);
    },

    destroy() {
        window.clearInterval(this.timer);
    },

    async checkStatuses() {
        try {
            const response = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) return;

            const data = await response.json();
            if (data.changed) window.location.reload();
        } catch {
            // The next polling cycle will retry when the network is unavailable.
        }
    },
}));

Alpine.start();

import './bootstrap';

/**
 * Alpine.js is provided and started by Livewire 4.
 * Toast stack: square popups at the left corner.
 * 5s after the last toast, items disappear one by one.
 */
document.addEventListener('alpine:init', () => {
    Alpine.store('toasts', {
        items: [],
        idleTimer: null,
        cascadeTimer: null,

        push(message, type = 'success') {
            const text = String(message || '').trim();
            if (! text) {
                return;
            }

            this.items.push({
                id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                message: text,
                type: type === 'error' ? 'error' : 'success',
                visible: true,
            });

            this.armIdleDismiss();
        },

        armIdleDismiss() {
            clearTimeout(this.idleTimer);
            clearInterval(this.cascadeTimer);

            this.idleTimer = setTimeout(() => {
                this.cascadeTimer = setInterval(() => {
                    if (this.items.length === 0) {
                        clearInterval(this.cascadeTimer);
                        this.cascadeTimer = null;
                        return;
                    }

                    // Remove oldest toast first.
                    this.items.shift();
                }, 450);
            }, 5000);
        },
    });
});

window.AaleToast = {
    push(message, type = 'success') {
        if (window.Alpine?.store('toasts')) {
            Alpine.store('toasts').push(message, type);
        }
    },
};

document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (payload = {}) => {
        const data = Array.isArray(payload) ? (payload[0] || {}) : payload;
        window.AaleToast.push(data.message ?? '', data.type ?? 'success');
    });
});

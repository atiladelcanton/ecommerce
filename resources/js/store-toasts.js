export function registerStoreToasts(Alpine) {
    Alpine.data('storeToasts', () => ({
        toasts: [],
        sequence: 0,
        announcement: '',
        urgentAnnouncement: '',
        timers: new Map(),
        removalTimers: new Map(),
        duration: 4000,

        notify(detail = {}) {
            if (!detail || typeof detail.title !== 'string' || typeof detail.message !== 'string') return;
            const type = ['success', 'error', 'warning', 'info'].includes(detail.type) ? detail.type : 'info';
            const toast = {
                id: ++this.sequence,
                type,
                title: detail.title,
                message: detail.message,
                actionLabel: typeof detail.actionLabel === 'string' ? detail.actionLabel : '',
                actionUrl: this.actionUrl(detail.actionUrl),
                open: false,
                remaining: this.duration,
                startedAt: 0,
                pauses: [],
            };

            // Remove the oldest entry and its timers before adding a fourth card.
            for (const old of this.toasts.slice(2)) this.clearTimers(old.id);
            this.toasts = [toast, ...this.toasts.slice(0, 2)];
            const announcement = `${toast.title}. ${toast.message}`;
            const channel = type === 'error' ? 'urgentAnnouncement' : 'announcement';
            this[channel] = '';
            this.$nextTick(() => {
                this[channel] = announcement;
                const current = this.toasts.find(item => item.id === toast.id);
                if (!current) return;
                current.open = true;
                this.start(current.id);
            });
        },

        actionUrl(value) {
            if (typeof value !== 'string' || !value.trim()) return null;
            try {
                const url = new URL(value, window.location.origin);
                return ['http:', 'https:'].includes(url.protocol) && url.origin === window.location.origin
                    ? url.pathname + url.search + url.hash : null;
            } catch {
                return null;
            }
        },

        start(id) {
            const toast = this.toasts.find(item => item.id === id);
            if (!toast?.open || toast.pauses.length) return;
            toast.startedAt = performance.now();
            clearTimeout(this.timers.get(id));
            this.timers.set(id, setTimeout(() => this.dismiss(id), toast.remaining));
        },

        pause(id, reason) {
            const toast = this.toasts.find(item => item.id === id);
            if (!toast?.open || toast.pauses.includes(reason)) return;
            if (!toast.pauses.length) {
                toast.remaining = Math.max(0, toast.remaining - (performance.now() - toast.startedAt));
                clearTimeout(this.timers.get(id));
                this.timers.delete(id);
            }
            toast.pauses.push(reason);
        },

        resume(id, reason) {
            const toast = this.toasts.find(item => item.id === id);
            if (!toast?.open || !toast.pauses.includes(reason)) return;
            toast.pauses = toast.pauses.filter(value => value !== reason);
            this.start(id);
        },

        dismiss(id) {
            const toast = this.toasts.find(item => item.id === id);
            if (!toast?.open) return;
            this.clearTimers(id);
            toast.open = false;
            this.removalTimers.set(id, setTimeout(() => {
                this.toasts = this.toasts.filter(item => item.id !== id);
                this.removalTimers.delete(id);
            }, 200));
        },

        clearTimers(id) {
            clearTimeout(this.timers.get(id));
            clearTimeout(this.removalTimers.get(id));
            this.timers.delete(id);
            this.removalTimers.delete(id);
        },

        destroy() {
            for (const id of new Set([...this.timers.keys(), ...this.removalTimers.keys()])) this.clearTimers(id);
        },
    }));
}

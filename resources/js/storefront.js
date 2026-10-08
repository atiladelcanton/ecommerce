export function registerStorefront(Alpine) {
    Alpine.data('storeCarousel', (count) => ({
        index: 0, count, paused: false, hovered: false, focused: false, timer: null,
        media: null, motionChange: null,
        init() {
            this.media = window.matchMedia('(prefers-reduced-motion: reduce)');
            this.paused = this.media.matches;
            this.motionChange = (event) => { this.paused = event.matches; };
            this.media.addEventListener('change', this.motionChange);
            this.timer = setInterval(() => {
                if (!this.paused && !this.hovered && !this.focused && !document.hidden && this.count > 1) this.next();
            }, 6500);
        },
        next() { this.index = (this.index + 1) % this.count; },
        previous() { this.index = (this.index - 1 + this.count) % this.count; },
        destroy() { clearInterval(this.timer); this.media?.removeEventListener('change', this.motionChange); },
    }));
    Alpine.data('storeSearch', () => ({
        open: false, active: -1,
        move(delta) {
            this.open = true;
            const count = this.$root.querySelectorAll('[data-suggestion]').length;
            if (count) this.active = (this.active + delta + count) % count;
        },
        choose(event) {
            if (!this.open || this.active < 0) return;
            const option = this.$root.querySelectorAll('[data-suggestion]')[this.active];
            if (option) { event.preventDefault(); option.click(); }
        },
    }));
}

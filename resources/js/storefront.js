export function registerStorefront(Alpine) {
    Alpine.data('storeHeader', () => ({
        desktop: window.matchMedia('(min-width: 1024px)').matches,
        searchOpen: false, categoriesOpen: false, media: null, mediaChange: null,
        init() {
            this.media = window.matchMedia('(min-width: 1024px)');
            this.mediaChange = (event) => {
                this.desktop = event.matches;
                this.categoriesOpen = false;
                if (this.desktop && this.$refs.menu.open) this.$refs.menu.close();
            };
            this.media.addEventListener('change', this.mediaChange);
        },
        toggleSearch() {
            this.searchOpen = !this.searchOpen;
            if (this.searchOpen) this.$nextTick(() => this.$root.querySelector('#store-search').focus());
        },
        openCategories() {
            if (window.matchMedia('(hover: hover)').matches) this.categoriesOpen = true;
        },
        closeCategories() {
            if (!this.categoriesOpen) return;
            this.categoriesOpen = false;
            this.$refs.categoriesTrigger.focus();
        },
        destroy() { this.media?.removeEventListener('change', this.mediaChange); },
    }));
    Alpine.data('storeCarousel', (count) => ({
        index: 0, previousIndex: -1, direction: 1, count, timer: null,
        hovered: false, focused: false, touching: false, reducedMotion: false,
        media: null, motionChange: null, visibilityChange: null,
        touchX: null, touchY: null, suppressClickUntil: 0,
        init() {
            this.media = window.matchMedia('(prefers-reduced-motion: reduce)');
            this.reducedMotion = this.media.matches;
            this.motionChange = (event) => { this.reducedMotion = event.matches; this.restart(); };
            this.visibilityChange = () => this.restart();
            this.media.addEventListener('change', this.motionChange);
            document.addEventListener('visibilitychange', this.visibilityChange);
            for (const state of ['hovered', 'focused', 'touching']) {
                this.$watch(state, () => this.restart());
            }
            this.restart();
        },
        restart() {
            clearTimeout(this.timer);
            this.timer = null;
            if (this.count < 2 || this.hovered || this.focused || this.touching || this.reducedMotion || document.hidden) return;
            this.timer = setTimeout(() => {
                if (!this.hovered && !this.focused && !this.touching && !this.reducedMotion && !document.hidden) this.next();
            }, 5500);
        },
        goTo(index, direction = index > this.index ? 1 : -1) {
            if (index < 0 || index >= this.count) return;
            if (index !== this.index) {
                this.direction = direction;
                this.previousIndex = this.index;
                this.index = index;
            }
            this.restart();
        },
        next() { this.goTo((this.index + 1) % this.count, 1); },
        previous() { this.goTo((this.index - 1 + this.count) % this.count, -1); },
        touchStart(event) {
            if (event.touches.length !== 1) { this.touchCancel(); return; }
            this.touchX = event.touches[0].clientX;
            this.touchY = event.touches[0].clientY;
            this.touching = true;
        },
        touchEnd(event) {
            if (this.touchX === null) return;
            const deltaX = event.changedTouches[0].clientX - this.touchX;
            const deltaY = event.changedTouches[0].clientY - this.touchY;
            this.touchCancel();
            if (this.count > 1 && Math.abs(deltaX) >= 50 && Math.abs(deltaX) > Math.abs(deltaY) * 1.2) {
                this.suppressClickUntil = Date.now() + 350;
                deltaX < 0 ? this.next() : this.previous();
            }
        },
        touchCancel() { this.touchX = null; this.touchY = null; this.touching = false; },
        destroy() {
            clearTimeout(this.timer);
            this.timer = null;
            this.media?.removeEventListener('change', this.motionChange);
            document.removeEventListener('visibilitychange', this.visibilityChange);
        },
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

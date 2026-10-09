import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import assert from 'node:assert/strict';

const source = readFileSync(new URL('../../resources/views/pagination/storefront.blade.php', import.meta.url), 'utf8');
const factory = new Function('document', 'window', `return (${source.match(/x-data="([\s\S]*?)"/)[1]});`);

for (const reducedMotion of [false, true]) {
    test(`pagination scrolls to the grid after rendering, reduced motion: ${reducedMotion}`, () => {
        let nextTick, target, options;
        const state = factory({
            getElementById(id) {
                target = id;
                return { scrollIntoView(value) { options = value; } };
            },
        }, { matchMedia: () => ({ matches: reducedMotion }) });
        state.$nextTick = (callback) => { nextTick = callback; };
        state.scrollToGrid();
        assert.equal(target, undefined);
        nextTick();
        assert.equal(target, 'store-products-grid');
        assert.deepEqual(options, { behavior: reducedMotion ? 'instant' : 'smooth', block: 'start' });
    });
}

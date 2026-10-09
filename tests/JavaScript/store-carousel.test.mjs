import { test } from 'node:test';
import assert from 'node:assert/strict';
import { registerStorefront } from '../../resources/js/storefront.js';

function carousel(t, count = 3, reducedMotion = false) {
    const original = { window: globalThis.window, document: globalThis.document, setTimeout, clearTimeout };
    let now = 0, nextTimer = 0;
    const timers = new Map(), watchers = new Map(), mediaListeners = new Map(), documentListeners = new Map();
    const media = {
        matches: reducedMotion,
        addEventListener(name, callback) { mediaListeners.set(name, callback); },
        removeEventListener(name) { mediaListeners.delete(name); },
    };
    globalThis.window = { matchMedia: () => media };
    globalThis.document = {
        hidden: false,
        addEventListener(name, callback) { documentListeners.set(name, callback); },
        removeEventListener(name) { documentListeners.delete(name); },
    };
    globalThis.setTimeout = (callback, delay) => { const id = ++nextTimer; timers.set(id, { at: now + delay, callback }); return id; };
    globalThis.clearTimeout = (id) => timers.delete(id);
    t.after(() => Object.assign(globalThis, original));
    const factories = new Map();
    registerStorefront({ data: (name, factory) => factories.set(name, factory) });
    const state = factories.get('storeCarousel')(count);
    state.$watch = (name, callback) => {
        let value = state[name];
        watchers.set(name, callback);
        Object.defineProperty(state, name, {
            get: () => value,
            set: (next) => { if (next === value) return; value = next; callback(next); },
        });
    };
    state.init();
    function tick(duration) {
        const end = now + duration;
        while (true) {
            const entry = [...timers].sort((a, b) => a[1].at - b[1].at)[0];
            if (!entry || entry[1].at > end) break;
            now = entry[1].at;
            timers.delete(entry[0]);
            entry[1].callback();
        }
        now = end;
    }
    return { state, tick, timers, mediaListeners, documentListeners };
}

test('autoplay starts at 5.5 seconds and repeats with only one timer', (t) => {
    const { state, tick, timers } = carousel(t);
    tick(5499); assert.equal(state.index, 0);
    tick(1); assert.equal(state.index, 1);
    assert.equal(timers.size, 1);
    tick(5500); assert.equal(state.index, 2);
    tick(5500); assert.equal(state.index, 0);
    assert.equal(timers.size, 1);
});

test('hover pauses immediately and leaving starts a fresh countdown', (t) => {
    const { state, tick, timers } = carousel(t);
    tick(5000);
    state.hovered = true;
    assert.equal(timers.size, 0);
    tick(10000); assert.equal(state.index, 0);
    state.hovered = false;
    tick(5499); assert.equal(state.index, 0);
    tick(1); assert.equal(state.index, 1);
});

test('next previous and indicators navigate and restart the autoplay countdown', (t) => {
    const { state, tick, timers } = carousel(t);
    tick(5000);
    state.next(); assert.equal(state.index, 1); assert.equal(state.previousIndex, 0);
    tick(500); assert.equal(state.index, 1);
    state.previous(); assert.equal(state.index, 0); assert.equal(state.direction, -1);
    state.previous(); assert.equal(state.index, 2);
    state.goTo(1); assert.equal(state.index, 1);
    state.restart(); state.restart(); assert.equal(timers.size, 1);
    tick(5499); assert.equal(state.index, 1);
    tick(1); assert.equal(state.index, 2);
});

test('horizontal swipes navigate while vertical scroll and small gestures do not', (t) => {
    const { state, timers } = carousel(t);
    const start = (x, y) => state.touchStart({ touches: [{ clientX: x, clientY: y }] });
    const end = (x, y) => state.touchEnd({ changedTouches: [{ clientX: x, clientY: y }] });
    start(200, 100); assert.equal(timers.size, 0);
    end(100, 105); assert.equal(state.index, 1);
    assert.ok(state.suppressClickUntil > Date.now());
    start(100, 100); end(200, 105); assert.equal(state.index, 0);
    start(200, 100); end(100, 300); assert.equal(state.index, 0);
    start(200, 100); end(170, 100); assert.equal(state.index, 0);
    assert.equal(timers.size, 1);
    start(200, 100); state.touchCancel(); assert.equal(state.touchX, null);
});

test('single banner has no autoplay timer and remains on its only slide', (t) => {
    const { state, tick, timers } = carousel(t, 1);
    state.next(); state.previous(); state.goTo(0);
    tick(20000);
    assert.equal(state.index, 0);
    assert.equal(timers.size, 0);
});

test('focus hidden tabs and reduced motion pause autoplay and cleanup removes listeners', (t) => {
    const { state, tick, timers, mediaListeners, documentListeners } = carousel(t);
    state.focused = true; tick(10000); assert.equal(state.index, 0);
    state.focused = false;
    document.hidden = true; documentListeners.get('visibilitychange')();
    assert.equal(timers.size, 0);
    document.hidden = false; documentListeners.get('visibilitychange')();
    mediaListeners.get('change')({ matches: true });
    assert.equal(timers.size, 0);
    state.next(); assert.equal(state.index, 1);
    mediaListeners.get('change')({ matches: false });
    assert.equal(timers.size, 1);
    state.destroy();
    assert.equal(timers.size, 0);
    assert.equal(mediaListeners.size, 0);
    assert.equal(documentListeners.size, 0);
});

test('reduced-motion preference is respected from initialization', (t) => {
    const { state, timers } = carousel(t, 3, true);
    assert.equal(state.reducedMotion, true);
    assert.equal(timers.size, 0);
});

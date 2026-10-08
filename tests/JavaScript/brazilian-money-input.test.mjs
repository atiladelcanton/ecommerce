import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import assert from 'node:assert/strict';

// Exercise the actual Alpine handlers emitted by the reusable PHP component.
const source = readFileSync(new URL('../../app/Filament/Forms/Components/BrazilianMoneyInput.php', import.meta.url), 'utf8');
const handlers = [...source.matchAll(/'x-on:([^']+)' => <<<'JS'\n([\s\S]*?)\n\s+JS,/g)];
const run = Object.fromEntries(handlers.map(([, event, body]) => [event, new Function('$el', '$event', body)]));

function input(value = '') {
    return {
        value,
        selectionStart: value.length,
        setSelectionRange(start) { this.selectionStart = start; },
        dispatchEvent() { run['input.capture'](this); },
    };
}

function type(field, digits) {
    for (const digit of digits) {
        const cursor = field.selectionStart;
        field.value = field.value.slice(0, cursor) + digit + field.value.slice(cursor);
        field.selectionStart++;
        run['input.capture'](field);
    }
}

test('typing cents displays Brazilian currency continuously', () => {
    const field = input();
    type(field, '1');
    assert.equal(field.value, '0,01');
    type(field, '00');
    assert.equal(field.value, '1,00');
    const product = input();
    type(product, '129990');
    assert.equal(product.value, '1.299,90');
    const expensive = input();
    type(expensive, '1250000');
    assert.equal(expensive.value, '12.500,00');
});

test('formatting retains all BIGINT digits and supports empty optional values', () => {
    const field = input('9223372036854775807');
    run['input.capture'](field);
    assert.equal(field.value, '92.233.720.368.547.758,07');
    const blank = input();
    run['input.capture'](blank);
    assert.equal(blank.value, '');
});

test('backspace clears zero so an optional promotion can become empty', () => {
    const field = input('0,00');
    let prevented = false;
    run.beforeinput(field, { inputType: 'deleteContentBackward', preventDefault() { prevented = true; } });
    assert.equal(field.value, '');
    assert.ok(prevented);
});

test('editing the middle retains the digit-relative cursor position', () => {
    const field = input('1.299,90');
    field.selectionStart = 3;
    type(field, '5');
    assert.equal(field.value, '12.599,90');
    assert.equal(field.selectionStart, 4);
});

test('rejects invalid typed characters and invalid pasted amounts', () => {
    for (const data of ['e', '-', '+', 'a']) {
        let prevented = false;
        run.beforeinput(input(), { inputType: 'insertText', data, preventDefault() { prevented = true; } });
        assert.ok(prevented);
    }
    for (const text of ['-1.299,90', '1,299.90', 'abc', '12,999']) {
        let prevented = false;
        run.paste(input(), { clipboardData: { getData: () => text }, preventDefault() { prevented = true; } });
        assert.ok(prevented);
    }
    for (const text of ['R$ 1.299,90', '12.500,00', '129990']) {
        const field = input(text);
        run.paste(field, { clipboardData: { getData: () => text }, preventDefault() { assert.fail('Valid paste rejected'); } });
        run['input.capture'](field);
        assert.match(field.value, /^\d{1,3}(?:\.\d{3})*,\d{2}$/);
    }
});

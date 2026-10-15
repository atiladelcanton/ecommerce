import assert from 'node:assert/strict';
import test from 'node:test';
import { SabaMasks } from '../../resources/js/brazilian-masks.js';

test('formats Brazilian phone masks', () => {
    assert.equal(SabaMasks.phone('41999991234'), '(41) 99999-1234');
    assert.equal(SabaMasks.phone('4133334444'), '(41) 3333-4444');
});

test('formats CPF and CNPJ adaptively', () => {
    assert.equal(SabaMasks.document('52998224725'), '529.982.247-25');
    assert.equal(SabaMasks.document('12345678000199'), '12.345.678/0001-99');
});

test('formats postal code', () => {
    assert.equal(SabaMasks.postalCode('80000000'), '80000-000');
});

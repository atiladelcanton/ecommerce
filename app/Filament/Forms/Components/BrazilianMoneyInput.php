<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Support\BrazilianMoney;
use Closure;
use Filament\Forms\Components\TextInput;
use InvalidArgumentException;

final class BrazilianMoneyInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->prefix('R$')
            ->inputMode('numeric')
            ->placeholder('0,00')
            ->formatStateUsing(fn (mixed $state): ?string => is_int($state) ? BrazilianMoney::format($state) : $state)
            ->dehydrateStateUsing(fn (?string $state): ?int => BrazilianMoney::parse($state))
            ->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value)) {
                    $fail('Informe um valor em reais, como 1.299,90.');

                    return;
                }

                try {
                    BrazilianMoney::parse($value);
                } catch (InvalidArgumentException $exception) {
                    $fail($exception->getMessage());
                }
            })
            ->extraInputAttributes([
                // Capture runs before Livewire's input listener, so it receives the formatted value.
                // Strings only: even BIGINT values never pass through JS Number / parseFloat.
                'x-on:input.capture' => <<<'JS'
                    const input = $el;
                    const right = input.value.slice(input.selectionStart ?? input.value.length).replace(/\D/g, '').length;
                    let digits = input.value.replace(/\D/g, '');
                    if (digits === '') { input.value = ''; return; }
                    digits = digits.replace(/^0+(?=\d)/, '').padStart(3, '0');
                    const whole = digits.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    input.value = whole + ',' + digits.slice(-2);
                    let cursor = input.value.length;
                    let remaining = right;
                    while (cursor > 0 && remaining > 0) {
                        cursor--;
                        if (/\d/.test(input.value[cursor])) remaining--;
                    }
                    input.setSelectionRange(cursor, cursor);
                    JS,
                'x-on:beforeinput' => <<<'JS'
                    if ($event.inputType === 'deleteContentBackward' && $el.value === '0,00') {
                        $event.preventDefault();
                        $el.value = '';
                        $el.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if ($event.inputType === 'insertText' && $event.data && /[^0-9]/.test($event.data)) {
                        $event.preventDefault();
                    }
                    JS,
                'x-on:paste' => <<<'JS'
                    const text = $event.clipboardData.getData('text').trim();
                    if (! /^(?:R\$\s*)?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{2})?$/.test(text)) {
                        $event.preventDefault();
                    }
                    JS,
            ]);
    }
}

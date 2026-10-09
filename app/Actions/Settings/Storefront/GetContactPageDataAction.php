<?php

declare(strict_types=1);

namespace App\Actions\Settings\Storefront;

use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Links;

final class GetContactPageDataAction
{
    public function execute(): array
    {
        $settings = StoreSetting::query()->find(StoreSetting::MAIN_ID);
        $phone = preg_replace('/[^+0-9]/', '', $settings?->phone ?? '');
        $whatsappUrl = Links::whatsapp($settings?->whatsapp);
        $hasValue = static fn (?string $value): bool => $value !== null && $value !== '';
        $addressLines = array_values(array_filter([
            implode(', ', array_filter([$settings?->street, $settings?->number], $hasValue)),
            $settings?->complement,
            $settings?->neighborhood,
            implode(' / ', array_filter([$settings?->city, $settings?->state], $hasValue)),
            $settings?->zip_code ? 'CEP '.$settings->zip_code : null,
        ], $hasValue));

        return [
            'settings' => $settings,
            'phoneUrl' => $phone !== '' ? 'tel:'.$phone : null,
            'whatsappUrl' => $whatsappUrl,
            'addressLines' => $addressLines,
            'hasContactChannels' => $phone !== '' || $whatsappUrl !== null || filled($settings?->email) || $addressLines !== [],
        ];
    }
}

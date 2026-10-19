<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Domain\AI\Models\AiSetting;
use App\Infrastructure\AI\AiProviderRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UpdateAiSettingsAction
{
    /** @param array<string, mixed> $data */
    public function execute(array $data): AiSetting
    {
        $providers = array_keys(app(AiProviderRegistry::class)->implementedOptions());
        $validated = Validator::make($data, [
            'provider' => ['required', 'string', Rule::in($providers)],
            'is_enabled' => ['required', 'boolean'],
            'model' => ['required', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:5000'],
            'store_instructions' => ['nullable', 'string', 'max:10000'],
            'monthly_request_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ])->validate();

        return DB::transaction(function () use ($validated): AiSetting {
            $settings = AiSetting::query()->lockForUpdate()->find(AiSetting::MAIN_ID);
            if (! $settings) {
                $settings = new AiSetting;
                $settings->forceFill(['id' => AiSetting::MAIN_ID]);
            }

            $apiKey = trim((string) ($validated['api_key'] ?? ''));
            if ((bool) $validated['is_enabled'] && $apiKey === '' && blank($settings->api_key)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'api_key' => 'Informe uma API Key antes de ativar o assistente de IA.',
                ]);
            }

            unset($validated['api_key']);
            if ($apiKey !== '') {
                $settings->api_key = $apiKey;
            }
            $settings->fill($validated);
            $settings->save();

            return $settings->refresh();
        });
    }
}

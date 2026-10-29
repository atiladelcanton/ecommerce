<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Domain\Settings\Models\StoreSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class UpdateStoreSettingsAction
{
    public function execute(array $data): StoreSetting
    {
        foreach ($data as $field => $value) {
            if (is_string($value)) {
                $data[$field] = trim($value) === '' ? null : trim($value);
            }
        }
        if (is_string($data['state'] ?? null)) {
            $data['state'] = strtoupper($data['state']);
        }
        $rules = [
            'store_name' => ['required', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255', 'regex:/^store\/branding\/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$/i'],
            'color_primary' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_secondary' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_button' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cnpj' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ()\-.]+$/'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ()\-.]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'zip_code' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],
            'shipping_origin_zip_code' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],
            'number' => ['nullable', 'string', 'max:30'],
            'state' => ['nullable', 'string', 'regex:/^[A-Z]{2}$/'],
            'seo_description' => ['nullable', 'string', 'max:5000'],
            'blog_enabled' => ['sometimes', 'boolean'],
            'blog_show_on_home' => ['sometimes', 'boolean'],
        ];
        foreach (['state_register', 'economic_activity_code', 'legal_name', 'street', 'complement', 'neighborhood', 'city', 'seo_title'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }
        foreach (['instagram_url', 'facebook_url', 'youtube_url'] as $field) {
            $rules[$field] = ['nullable', 'url:http,https', 'max:2048'];
        }
        foreach (['color_primary', 'color_secondary', 'color_button'] as $field) {
            if (is_string($data[$field] ?? null)) {
                $data[$field] = strtoupper($data[$field]);
            }
        }

        $validated = Validator::make($data, $rules, [
            'required' => 'O campo :attribute é obrigatório.',
            'regex' => 'O formato de :attribute é inválido.',
        ], ['store_name' => 'nome da loja', 'zip_code' => 'CEP', 'shipping_origin_zip_code' => 'CEP de origem', 'state' => 'UF'])->validate();

        return DB::transaction(function () use ($validated): StoreSetting {
            // firstOrCreate handles a concurrent first save through the primary key.
            $settings = StoreSetting::query()->firstOrCreate(['id' => StoreSetting::MAIN_ID], $validated);
            $settings = StoreSetting::query()->lockForUpdate()->findOrFail($settings->id);
            $settings->update($validated);

            return $settings->refresh();
        }, attempts: 3);
    }
}

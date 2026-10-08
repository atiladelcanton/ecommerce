<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Banner;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ValidateBannerData
{
    public function execute(array $data, ?Banner $banner = null): array
    {
        foreach (['mobile_image', 'link', 'alt_text', 'starts_at', 'ends_at'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        $required = $banner ? 'sometimes' : 'required';
        $validated = Validator::make($data, [
            'title' => [$required, 'required', 'string', 'max:255'],
            'desktop_image' => [$required, 'required', 'string', 'max:255'],
            'mobile_image' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'url:http,https', 'max:2048'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'between:-2147483648,2147483647'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ], ['date' => 'Informe uma data válida.', 'required' => 'O campo :attribute é obrigatório.'], [
            'title' => 'título', 'desktop_image' => 'imagem desktop', 'link' => 'link',
        ])->validate();
        $starts = array_key_exists('starts_at', $validated) ? $validated['starts_at'] : $banner?->starts_at;
        $ends = array_key_exists('ends_at', $validated) ? $validated['ends_at'] : $banner?->ends_at;
        if ($starts !== null && $ends !== null && CarbonImmutable::parse($ends)->lt(CarbonImmutable::parse($starts))) {
            throw ValidationException::withMessages(['ends_at' => 'O fim da publicação deve ser igual ou posterior ao início.']);
        }

        return $validated;
    }
}

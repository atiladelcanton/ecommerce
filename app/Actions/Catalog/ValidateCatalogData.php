<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Exceptions\InvalidProductPrice;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\ValueObjects\ProductPricing;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class ValidateCatalogData
{
    public function category(array $data, ?Category $category = null): array
    {
        $data = $this->slug($data, $category === null);
        if (array_key_exists('image_path', $data) && $data['image_path'] === '') {
            $data['image_path'] = null;
        }

        return Validator::make($data, [
            'name' => [$category ? 'sometimes' : 'required', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('categories')->ignore($category?->id)],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:255', 'regex:~\Acategories/[a-z0-9_-]+\.(?:jpe?g|png|webp)\z~i'],
            'sort_order' => ['integer', 'between:-2147483648,2147483647'],
            'is_active' => ['boolean'],
        ], ['image_path.regex' => 'Selecione uma imagem enviada para esta categoria.'], ['image_path' => 'imagem de destaque'])->validate();
    }

    public function product(array $data, ?Product $product = null): array
    {
        $data = $this->slug($data, $product === null);
        if (($data['availability'] ?? null) instanceof ProductAvailability) {
            $data['availability'] = $data['availability']->value;
        }
        $required = $product ? 'sometimes' : 'required';
        $cents = static function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_int($value) || $value < 0) {
                $fail('O valor deve ser um inteiro não negativo em centavos.');
            }
        };
        $rules = [
            'category_id' => [$required, 'required', 'integer', Rule::exists('categories', 'id')],
            'name' => [$required, 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($product?->id)],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product?->id)],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'youtube_url' => ['nullable', 'url', 'max:2048', 'regex:~^https?://(?:www\.)?(?:youtube\.com|youtu\.be)/~i'],
            'availability' => [$required, 'required', Rule::enum(ProductAvailability::class)],
            'cost_price' => [$required, 'required', $cents],
            'price' => [$required, 'required', $cents],
            'promotional_price' => ['nullable', $cents],
            'stock' => ['nullable', 'integer', 'between:0,4294967295'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'whatsapp_quote_enabled' => ['boolean'],
            'weight' => ['nullable', 'numeric', 'between:0,999999999.999'],
        ];
        foreach (['height', 'width', 'length'] as $dimension) {
            $rules[$dimension] = ['nullable', 'numeric', 'between:0,9999999999.99'];
        }
        $validated = Validator::make($data, $rules)->validate();
        $promotion = array_key_exists('promotional_price', $validated) ? $validated['promotional_price'] : $product?->promotional_price;
        try {
            new ProductPricing($validated['price'] ?? $product->price, $promotion);
        } catch (InvalidProductPrice $exception) {
            throw ValidationException::withMessages([$exception->field => $exception->getMessage()]);
        }

        return $validated;
    }

    private function slug(array $data, bool $creating): array
    {
        if (($creating && ! isset($data['slug'])) || (array_key_exists('slug', $data) && ($data['slug'] === null || $data['slug'] === ''))) {
            $data['slug'] = Str::slug(is_string($data['name'] ?? null) ? $data['name'] : '');
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Exceptions\InvalidProductPrice;
use App\Domain\Catalog\Models\InventoryReservation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Services\InventoryAvailability;
use App\Domain\Catalog\ValueObjects\ProductPricing;
use App\Domain\Order\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class SyncProductVariantsAction
{
    public function execute(Product $product, array $rows): void
    {
        DB::transaction(function () use ($product, $rows): void {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $existing = $product->variants()->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $seen = [];
            $kept = [];
            $activeAttributeNames = null;
            foreach (array_values($rows) as $index => $input) {
                $path = 'variants.'.$index;
                $validator = Validator::make($input, [
                    'id' => ['nullable', 'integer'], 'attributes' => ['required', 'array', 'min:1'],
                    'attributes.*' => ['required', 'string', 'max:120'], 'sku' => ['nullable', 'string', 'max:255'],
                    'price' => ['nullable', 'integer', 'min:1'], 'promotional_price' => ['nullable', 'integer', 'min:1'],
                    'stock' => ['nullable', 'integer', 'between:0,4294967295'], 'is_active' => ['required', 'boolean'],
                    'sort_order' => ['sometimes', 'integer', 'min:0'],
                    'weight' => ['nullable', 'numeric', 'gt:0', 'max:999999999.999'],
                    'height' => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
                    'width' => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
                    'length' => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
                ]);
                if ($validator->fails()) {
                    $errors = [];
                    foreach ($validator->errors()->messages() as $field => $messages) {
                        $errors[$path.'.'.$field] = $messages;
                    }
                    throw ValidationException::withMessages($errors);
                }
                $row = $validator->validated();
                $row['stock'] = isset($row['stock']) ? (int) $row['stock'] : null;
                $variant = isset($row['id']) ? $existing->get($row['id']) : new ProductVariant;
                if (! $variant || ($variant->exists && in_array($variant->id, $kept, true))) {
                    throw ValidationException::withMessages([$path => 'Variação inválida para este produto.']);
                }
                $attributes = [];
                $canonical = [];
                foreach ($row['attributes'] as $name => $value) {
                    $name = trim((string) $name);
                    $value = trim($value);
                    if ($name === '' || $value === '' || mb_strlen($name) > 120 || isset($canonical[mb_strtolower($name)])) {
                        throw ValidationException::withMessages([$path.'.attributes' => 'Informe características distintas com nome e valor.']);
                    }
                    $attributes[$name] = $value;
                    $canonical[mb_strtolower($name)] = mb_strtolower($value);
                }
                ksort($canonical);
                if ($row['is_active']) {
                    $names = array_keys($canonical);
                    if ($activeAttributeNames !== null && $names !== $activeAttributeNames) {
                        throw ValidationException::withMessages([$path.'.attributes' => 'Todas as variações ativas devem usar as mesmas características.']);
                    }
                    $activeAttributeNames = $names;
                }
                $key = hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                if (isset($seen[$key])) {
                    throw ValidationException::withMessages([$path.'.attributes' => 'Esta combinação de características já foi cadastrada.']);
                }
                $seen[$key] = true;
                $name = implode(' / ', array_keys($attributes));
                $value = implode(' / ', array_values($attributes));
                if (mb_strlen($name) > 255 || mb_strlen($value) > 255) {
                    throw ValidationException::withMessages([$path.'.attributes' => 'Resuma os nomes e valores das características para até 255 caracteres.']);
                }
                foreach (['price', 'promotional_price'] as $moneyField) {
                    if (isset($row[$moneyField]) && ! is_int($row[$moneyField])) {
                        throw ValidationException::withMessages([$path.'.'.$moneyField => 'Informe o valor em centavos inteiros.']);
                    }
                }
                try {
                    new ProductPricing($row['price'] ?? $product->price, $row['promotional_price'] ?? (($row['price'] ?? null) === null ? $product->promotional_price : null));
                } catch (InvalidProductPrice $exception) {
                    throw ValidationException::withMessages([$path.'.'.$exception->field => $exception->getMessage()]);
                }
                $measurements = array_filter(array_intersect_key($row, array_flip(['weight', 'height', 'width', 'length'])), fn ($v) => $v !== null && $v !== '');
                if ($measurements !== [] && count($measurements) !== 4) {
                    throw ValidationException::withMessages([$path.'.weight' => 'Informe peso, altura, largura e comprimento da embalagem, ou deixe todos vazios para herdar o produto.']);
                }
                if ($variant->exists) {
                    if ($this->hasReservation($variant) && $variant->characteristics() != $attributes) {
                        throw ValidationException::withMessages([$path.'.attributes' => 'Não altere a combinação enquanto ela estiver reservada em pedidos.']);
                    }
                    try {
                        app(InventoryAvailability::class)->assertStockChange($variant, $row['stock'] ?? null);
                    } catch (ValidationException $exception) {
                        throw ValidationException::withMessages([$path.'.stock' => $exception->getMessage()]);
                    }
                    if (($row['stock'] ?? null) !== $variant->stock && $this->hasReservation($variant)) {
                        if (($row['stock'] ?? null) === null || $variant->stock === null) {
                            throw ValidationException::withMessages([$path.'.stock' => 'Não altere a origem do estoque enquanto houver reservas ativas.']);
                        }
                    }
                }
                unset($row['id']);
                $variant->fill(array_merge($row, ['product_id' => $product->id, 'attributes' => $attributes, 'combination_key' => $key, 'name' => $name, 'value' => $value, 'sort_order' => $index]))->save();
                $kept[] = $variant->id;
            }
            foreach ($existing->except($kept) as $variant) {
                if ($this->hasReservation($variant)) {
                    throw ValidationException::withMessages(['variants' => 'Há uma variação reservada em pedidos. Aguarde ou cancele esses pedidos antes de removê-la.']);
                }
                $variant->update(['is_active' => false]);
            }
        }, 3);
    }

    private function hasReservation(ProductVariant $variant): bool
    {
        return InventoryReservation::query()->whereIn('order_item_id', OrderItem::query()->where('product_variant_id', $variant->id)->select('id'))
            ->whereNull('consumed_at')->whereNull('released_at')
            ->where(fn ($q) => $q->where('expires_at', '>', now())->orWhere('payment_hold', true))->exists();
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Actions\Catalog\ResolveProductOfferAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Settings\Models\StoreSetting;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingItem;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Data\ShippingQuoteRequest;
use App\Domain\Shipping\Exceptions\PartialShippingQuoteFailure;
use App\Domain\Shipping\Exceptions\ShippingQuoteUnavailable;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

final class QuoteShippingAction
{
    public function __construct(
        private readonly ShippingProviderRegistry $providers,
    ) {}

    /** @return list<ShippingOption> */
    public function execute(array $cart, string $postalCode): array
    {
        $destination = preg_replace('/\D/', '', $postalCode) ?? '';
        $settings = StoreSetting::query()->find(StoreSetting::MAIN_ID);
        $origin = preg_replace('/\D/', '', (string) ($settings?->shipping_origin_zip_code ?: $settings?->zip_code)) ?? '';

        if ($destination !== '' && strlen($destination) !== 8) {
            throw ValidationException::withMessages(['shipping_option' => 'Informe um CEP válido para calcular a entrega.']);
        }

        $products = Product::query()->with('variants')
            ->whereKey(array_map(fn (array $line): int => $line['item']->productId, $cart['items']))
            ->get(['id', 'name', 'sku', 'weight', 'height', 'width', 'length', 'price', 'promotional_price'])
            ->keyBy('id');

        $items = [];
        foreach ($cart['items'] as $line) {
            $product = $products->get($line['item']->productId);
            if (! $product) {
                throw ValidationException::withMessages(['cart' => 'Alguns produtos não estão mais disponíveis. Revise seu carrinho.']);
            }
            $variant = $line['item']->productVariantId ? $product->variants->firstWhere('id', $line['item']->productVariantId) : null;
            $offer = app(ResolveProductOfferAction::class)->execute($product, $variant);
            $items[] = new ShippingItem(
                productId: $product->id,
                quantity: $line['item']->quantity,
                weight: $offer->measurements['weight'],
                height: $offer->measurements['height'],
                width: $offer->measurements['width'],
                length: $offer->measurements['length'],
                unitPriceCents: (int) $line['unitPrice'],
                name: $product->name.($line['variant'] ? ' - '.$line['variant']->value : ''),
                sku: $offer->sku,
                lineId: $line['item']->productVariantId ? $line['item']->key() : null,
            );
        }

        $providers = $this->providers->enabled();

        $cacheKey = 'shipping-quotes:'.hash('sha256', json_encode([
            'origin' => $origin,
            'destination' => $destination,
            'providers' => array_map(fn (ShippingProvider $provider): string => $provider->key(), $providers),
            'revision' => Cache::get('shipping-configuration-revision', 'initial'),
            'items' => array_map(fn (ShippingItem $item): array => [
                $item->productId, $item->quantity, $item->weight, $item->height, $item->width, $item->length, $item->unitPriceCents, $item->lineId, $item->name,
            ], $items),
        ], JSON_THROW_ON_ERROR));

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            $restored = array_map(ShippingOption::fromSnapshot(...), $cached);
            if (count(array_filter($restored, app(IsShippingOptionAvailableAction::class)->execute(...))) === count($restored)) {
                return $restored;
            }
        }

        $options = [];
        $errors = [];
        $unavailableMessage = null;
        foreach ($providers as $provider) {
            $providerKey = $provider->key();
            if ($this->providers->capabilities($providerKey)->requiresPostalCodes && ($destination === '' || strlen($origin) !== 8)) {
                continue;
            }
            try {
                $quoted = $provider->quote(new ShippingQuoteRequest(
                    destinationPostalCode: $destination,
                    items: $items,
                    originPostalCode: $origin,
                ));
                foreach ($quoted as $option) {
                    $options[$option->id] = $option;
                }
            } catch (PartialShippingQuoteFailure $exception) {
                $errors[] = $providerKey;
                foreach ($exception->options as $option) {
                    $options[$option->id] = $option;
                }
            } catch (Throwable $exception) {
                if ($exception instanceof ShippingQuoteUnavailable) {
                    $unavailableMessage = $exception->getMessage();
                }
                $errors[] = $providerKey;
                Log::warning('Shipping quote provider failed.', [
                    'provider' => $providerKey,
                    'exception_type' => $exception::class,
                    'reason' => $exception instanceof ShippingQuoteUnavailable ? $exception->reason : 'provider_unavailable',
                ]);
            }
        }

        $options = array_filter($options, app(IsShippingOptionAvailableAction::class)->execute(...));
        if ($options === [] && $destination === '') {
            return [];
        }
        if ($options === []) {
            throw ValidationException::withMessages(['shipping_option' => $unavailableMessage ?? 'Frete sob consulta. Entre em contato com a SABA para combinar a entrega.']);
        }

        $resolved = array_values($options);
        usort($resolved, fn (ShippingOption $a, ShippingOption $b): int => [$a->price, $a->estimatedDays ?? PHP_INT_MAX, $a->name] <=> [$b->price, $b->estimatedDays ?? PHP_INT_MAX, $b->name]);
        // Do not cache partial failures: recovery of a provider must be visible immediately.
        if ($errors === []) {
            Cache::put($cacheKey, array_map(fn (ShippingOption $option): array => $option->snapshot(), $resolved), now()->addMinutes(10));
        }

        return $resolved;
    }
}

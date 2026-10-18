<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Models\Order;
use App\Domain\Settings\Models\StoreSetting;
use App\Domain\Shipping\Data\ApiMoney;
use App\Domain\Shipping\Data\ShipmentCreateRequest;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Domain\Shipping\Models\ShippingAgency;
use Illuminate\Validation\ValidationException;

final class BuildShipmentRequestAction
{
    public function execute(
        Order $order,
        array $snapshot,
        array $packages,
        ?string $invoiceKey,
        ?string $dceKey,
        ?int $agencyId,
        ?string $operationKey = null,
        ShipmentDocumentType|string $documentType = ShipmentDocumentType::INVOICE,
    ): ShipmentCreateRequest {
        $documentType = $documentType instanceof ShipmentDocumentType
            ? $documentType
            : ShipmentDocumentType::tryFrom($documentType) ?? ShipmentDocumentType::INVOICE;

        $store = StoreSetting::query()->find(1);
        foreach (['store_name', 'cnpj', 'phone', 'email', 'street', 'number', 'neighborhood', 'city', 'state'] as $field) {
            if (! $store || ! filled($store->{$field})) {
                $this->incompleteStore();
            }
        }

        if ($documentType->isInvoice() && ! filled($store?->state_register)) {
            throw ValidationException::withMessages([
                'shipping' => 'Informe a Inscrição Estadual nas configurações da loja para gerar um envio comercial com NF-e.',
            ]);
        }

        $postalCode = preg_replace('/\D/', '', (string) ($store->shipping_origin_zip_code ?: $store->zip_code));
        if (strlen($postalCode) !== 8 || strlen(preg_replace('/\D/', '', $store->cnpj)) !== 14 || ! filter_var($store->email, FILTER_VALIDATE_EMAIL)) {
            $this->incompleteStore();
        }

        foreach (['street', 'number', 'neighborhood', 'city', 'state', 'postal_code'] as $field) {
            if (! filled($order->address?->{$field})) {
                throw ValidationException::withMessages(['shipping' => 'Complete o endereço do destinatário antes de gerar etiquetas.']);
            }
        }

        $invoiceKey = filled($invoiceKey) ? (preg_replace('/\D/', '', $invoiceKey) ?: null) : null;
        $dceKey = filled($dceKey) ? (preg_replace('/\D/', '', $dceKey) ?: null) : null;

        if ($documentType->isInvoice()) {
            if (! $invoiceKey) {
                throw ValidationException::withMessages(['invoice_key' => 'Informe a chave da NF-e deste pedido.']);
            }
            $this->validateInvoiceKey($invoiceKey);
            $dceKey = null;
        } else {
            $invoiceKey = null;
            if ($dceKey !== null && ! preg_match('/^\d{44}$/D', $dceKey)) {
                throw ValidationException::withMessages(['dce_key' => 'Informe uma chave DC-e com 44 dígitos.']);
            }
        }

        if (! filled($snapshot['service_external_id'] ?? null) || $packages === []) {
            throw ValidationException::withMessages(['shipping' => 'O envio não possui serviço e volumes válidos no snapshot.']);
        }

        $volumes = [];
        $insurance = 0;
        foreach ($packages as $package) {
            $volume = [];
            foreach (['height', 'width', 'length'] as $dimension) {
                $value = $package['dimensions'][$dimension] ?? null;
                if (! is_numeric($value) || (float) $value <= 0) {
                    throw ValidationException::withMessages(['shipping' => 'Há volumes sem dimensões válidas.']);
                }
                $volume[$dimension] = (float) $value;
            }
            if (! is_numeric($package['weight'] ?? null) || (float) $package['weight'] <= 0) {
                throw ValidationException::withMessages(['shipping' => 'Há volumes sem peso válido.']);
            }
            $volume['weight'] = (float) $package['weight'];
            $volumes[] = $volume;
            $insurance += $package['insurance_cents'] ?? ApiMoney::cents($package['insurance_value'] ?? '0');
        }

        $agency = $agencyId ? ShippingAgency::query()->whereKey($agencyId)->where('shipping_integration_id', $snapshot['integration_id'] ?? null)
            ->where('environment', $snapshot['environment'] ?? null)->whereHas('carriers', fn ($query) => $query->where('external_id', $snapshot['carrier_external_id'] ?? null)->where('shipping_integration_id', $snapshot['integration_id'] ?? null)->where('environment', $snapshot['environment'] ?? null))->where('is_active', true)->first() : null;
        if (($agencyId && ! $agency) || (($snapshot['metadata']['agency_required'] ?? false) && ! $agency)) {
            throw ValidationException::withMessages(['agency_id' => 'Selecione uma unidade de postagem ativa desta transportadora.']);
        }

        $productQuantities = [];
        foreach ($packages as $package) {
            foreach (($package['products'] ?? []) as $product) {
                $id = (string) ($product['id'] ?? '');
                $productQuantities[$id] = ($productQuantities[$id] ?? 0) + (int) ($product['quantity'] ?? 0);
            }
        }

        $products = [];
        foreach (($snapshot['metadata']['products'] ?? []) as $product) {
            $quantity = $productQuantities[(string) $product['id']] ?? (count($packages) === count($snapshot['packages'] ?? []) ? $product['quantity'] : 0);
            if ($quantity > 0) {
                $products[] = [
                    'name' => $product['name'],
                    'quantity' => $quantity,
                    'unitary_value' => new ApiMoney((int) $product['unit_price_cents']),
                ];
            }
        }
        if ($products === []) {
            throw ValidationException::withMessages(['shipping' => 'O snapshot não identifica os produtos de cada volume. Complete a preparação do envio antes de comprar.']);
        }

        if (($snapshot['metadata']['insured'] ?? true) && $insurance === 0) {
            foreach ($products as $product) {
                $unit = $product['unitary_value']->cents;
                if ($unit > intdiv(PHP_INT_MAX - $insurance, $product['quantity'])) {
                    throw ValidationException::withMessages(['shipping' => 'O valor segurado excede o limite permitido.']);
                }
                $insurance += $unit * $product['quantity'];
            }
        }

        $document = preg_replace('/\D/', '', (string) $order->customer_document);
        if (! in_array(strlen($document), [11, 14], true) || ! filled($order->customer_name) || ! filled($order->customer_phone) || ! filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['shipping' => 'Complete os dados do destinatário antes de gerar etiquetas.']);
        }

        $stateRegister = $documentType->isContentDeclaration()
            ? (mb_strtoupper(trim((string) $store->state_register)) === 'ISENTO' ? 'ISENTO' : '')
            : $this->stateRegister((string) $store->state_register);

        return new ShipmentCreateRequest(
            orderNumber: $order->number,
            serviceExternalId: $snapshot['service_external_id'],
            sender: [
                'name' => $store->legal_name ?: $store->store_name,
                'company_document' => preg_replace('/\D/', '', $store->cnpj),
                'state_register' => $stateRegister,
                'economic_activity_code' => preg_replace('/\D/', '', (string) ($store->economic_activity_code ?: '')),
                'phone' => preg_replace('/\D/', '', $store->phone),
                'email' => $store->email,
                'address' => $store->street,
                'number' => $store->number,
                'complement' => $store->complement ?: '',
                'district' => $store->neighborhood,
                'city' => $store->city,
                'state_abbr' => strtoupper((string) $store->state),
                'postal_code' => $postalCode,
            ],
            recipient: array_filter([
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => preg_replace('/\D/', '', $order->customer_phone),
                strlen($document) === 14 ? 'company_document' : 'document' => $document,
                'address' => $order->address->street,
                'number' => $order->address->number,
                'complement' => $order->address->complement ?: '',
                'district' => $order->address->neighborhood,
                'city' => $order->address->city,
                'state_abbr' => strtoupper((string) $order->address->state),
                'postal_code' => preg_replace('/\D/', '', $order->address->postal_code),
                'country_id' => 'BR',
            ], static fn (mixed $value): bool => $value !== null),
            products: $products,
            volumes: $volumes,
            insuranceValueCents: $insurance,
            documentType: $documentType,
            invoiceKey: $invoiceKey,
            dceKey: $dceKey,
            quoteSnapshot: $snapshot,
            agencyExternalId: $agency?->external_id,
            operationKey: $operationKey,
        );
    }

    private function validateInvoiceKey(string $invoiceKey): void
    {
        if (! preg_match('/^\d{44}$/D', $invoiceKey)) {
            throw ValidationException::withMessages(['invoice_key' => 'Informe uma chave de NF-e com 44 dígitos.']);
        }
        if (substr($invoiceKey, 20, 2) !== '55') {
            throw ValidationException::withMessages(['invoice_key' => 'A chave informada não é de uma NF-e modelo 55.']);
        }

        $sum = 0;
        $weight = 2;
        for ($index = 42; $index >= 0; $index--) {
            $sum += ((int) $invoiceKey[$index]) * $weight;
            $weight = $weight === 9 ? 2 : $weight + 1;
        }
        $digit = 11 - ($sum % 11);
        if ($digit >= 10) {
            $digit = 0;
        }
        if ($digit !== (int) $invoiceKey[43]) {
            throw ValidationException::withMessages(['invoice_key' => 'A chave da NF-e possui dígito verificador inválido. Confira a chave de acesso.']);
        }
    }

    private function stateRegister(string $value): string
    {
        $value = trim($value);
        if (mb_strtoupper($value) === 'ISENTO') {
            return 'ISENTO';
        }

        return preg_replace('/[^A-Za-z0-9]/', '', $value) ?? $value;
    }

    private function incompleteStore(): never
    {
        throw ValidationException::withMessages(['shipping' => 'Complete os dados da loja antes de gerar etiquetas.']);
    }
}

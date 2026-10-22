<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Address\LookupPostalCodeAction;
use App\Actions\Coupon\ApplyCouponAction;
use App\Actions\Coupon\QuoteCouponAction;
use App\Actions\Customer\CreateCustomerAddressAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutSummaryAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Order\ValidateCheckoutData;
use App\Domain\Address\Exceptions\PostalCodeLookupFailed;
use App\Domain\Customer\Models\CustomerAddress;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class CheckoutPage extends Component
{
    #[Locked]
    public string $checkoutToken;

    public ?string $postalCodeMessage = null;

    public string $couponInput = '';

    public function applyCoupon(ApplyCouponAction $action): void
    {
        $this->resetValidation('coupon');
        $action->apply($this->couponInput);
    }

    public function removeCoupon(ApplyCouponAction $action): void
    {
        $action->remove();
        $this->couponInput = '';
    }

    public string $addressChoice = 'new';

    public array $savedAddresses = [];

    public bool $saveNewAddress = false;

    public bool $makeDefaultAddress = false;

    public array $form = [
        'name' => '', 'email' => '', 'phone' => '', 'document' => '',
        'postal_code' => '', 'street' => '', 'number' => '', 'complement' => '',
        'neighborhood' => '', 'city' => '', 'state' => '', 'shipping_option' => 'standard',
    ];

    public function mount(GetCheckoutTokenAction $token, AuthFactory $auth): void
    {
        $this->checkoutToken = $token->execute();
        $this->couponInput = app(ApplyCouponAction::class)->current() ?? '';
        $customer = $auth->guard('customer')->user();
        if (! $customer) {
            return;
        }

        $this->form['name'] = $customer->name;
        $this->form['email'] = $customer->email;
        $this->form['phone'] = $customer->phone ?? '';
        $this->form['document'] = $customer->document;

        $addresses = $customer->addresses()->get();
        $this->savedAddresses = $addresses->map(fn (CustomerAddress $address): array => [
            'id' => $address->id,
            'label' => $address->label ?: 'Endereço',
            'postal_code' => $address->postal_code,
            'street' => $address->street,
            'number' => $address->number,
            'complement' => $address->complement,
            'neighborhood' => $address->neighborhood,
            'city' => $address->city,
            'state' => $address->state,
            'is_default' => $address->is_default,
        ])->all();

        $default = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        if ($default) {
            $this->addressChoice = 'saved:'.$default->id;
            $this->fillAddress($default);
        }
    }

    public function updatedAddressChoice(string $value): void
    {
        if ($value === 'new') {
            foreach (['postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state'] as $field) {
                $this->form[$field] = '';
            }
            $this->postalCodeMessage = null;

            return;
        }

        if (! str_starts_with($value, 'saved:')) {
            return;
        }
        $id = (int) substr($value, 6);
        $customer = auth('customer')->user();
        if (! $customer) {
            return;
        }
        $address = $customer->addresses()->whereKey($id)->first();
        if ($address) {
            $this->fillAddress($address);
        }
    }

    public function updated(string $property): void
    {
        if ($property !== 'form.postal_code') {
            return;
        }
        $this->postalCodeMessage = null;
        $this->resetValidation('postal_code');
        try {
            $address = app(LookupPostalCodeAction::class)->execute(is_string($this->form['postal_code'] ?? null) ? $this->form['postal_code'] : '');
        } catch (PostalCodeLookupFailed) {
            $this->postalCodeMessage = 'Não foi possível consultar o CEP agora. Você pode preencher o endereço manualmente.';

            return;
        }
        if (! $address) {
            $this->postalCodeMessage = 'CEP não encontrado. Preencha o endereço manualmente.';

            return;
        }
        $this->form['street'] = $address->street;
        $this->form['neighborhood'] = $address->neighborhood;
        $this->form['city'] = $address->city;
        $this->form['state'] = $address->state;
        if ($address->complement !== null && trim((string) ($this->form['complement'] ?? '')) === '') {
            $this->form['complement'] = $address->complement;
        }
        $this->resetValidation(['street', 'neighborhood', 'city', 'state']);
    }

    public function submit(CreateInitialOrderAction $action, CreateCustomerAddressAction $createAddress): void
    {
        $this->completeCheckout($action, $createAddress, false);
    }

    public function requestShippingQuote(CreateInitialOrderAction $action, CreateCustomerAddressAction $createAddress): void
    {
        $this->completeCheckout($action, $createAddress, true);
    }

    private function completeCheckout(CreateInitialOrderAction $action, CreateCustomerAddressAction $createAddress, bool $requestFreight): void
    {
        $this->resetValidation();
        $customer = auth('customer')->user();
        if ($customer) {
            // Identity belongs to the authenticated account; document cannot be changed during checkout.
            $this->form['name'] = $customer->name;
            $this->form['email'] = $customer->email;
            $this->form['phone'] = $customer->phone ?? '';
            $this->form['document'] = $customer->document;
            if (str_starts_with($this->addressChoice, 'saved:')) {
                $id = (int) substr($this->addressChoice, 6);
                $saved = $customer->addresses()->whereKey($id)->first();
                if (! $saved) {
                    throw ValidationException::withMessages(['address' => 'Selecione um endereço válido.']);
                }
                $this->fillAddress($saved);
            }
        }

        // Only an explicitly applied coupon (session state) reaches the order.
        $couponCode = app(ApplyCouponAction::class)->current();
        if ($couponCode) {
            $cart = app(\App\Actions\Cart\GetCartAction::class)->execute();
            app(QuoteCouponAction::class)->execute($couponCode, array_values($cart['items']), $cart['subtotal']);
        }
        $input = array_merge($this->form, ['coupon_code' => $couponCode]);
        $order = $requestFreight ? $action->requestShippingQuote($input, $this->checkoutToken) : $action->execute($input, $this->checkoutToken);

        if ($customer && $this->addressChoice === 'new' && $this->saveNewAddress) {
            $createAddress->execute($customer, array_merge($this->form, [
                'label' => 'Endereço',
                'is_default' => $this->makeDefaultAddress,
            ]));
        }

        $this->dispatch('cart-updated');
        $payment = $order->payments()->latest('id')->first();
        if ($payment && $payment->gateway === 'asaas' && ! $payment->is_demo && $payment->checkout_url && \App\Infrastructure\Payments\AsaasGateway::isCheckoutUrl($payment->checkout_url, $payment->environment)) {
            $this->redirect($payment->checkout_url);
            return;
        }
        if ($payment && ! $payment->is_demo) {
            $this->redirectRoute('store.order.payment-status', ['number' => $order->number]);
            return;
        }
        $this->redirectRoute('store.order.confirmation', ['number' => $order->number]);
    }

    public function render(): View
    {
        $couponCode = app(ApplyCouponAction::class)->current();
        $summary = app(GetCheckoutSummaryAction::class)->execute(
            is_string($this->form['postal_code'] ?? null) ? $this->form['postal_code'] : '',
            is_string($this->form['shipping_option'] ?? null) ? $this->form['shipping_option'] : '',
            $couponCode,
        );
        return view('livewire.store.checkout-page', [
            'couponCode' => $couponCode,
            'summary' => $summary,
            'states' => ValidateCheckoutData::STATES,
        ]);
    }

    private function fillAddress(CustomerAddress $address): void
    {
        foreach (['postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state'] as $field) {
            $this->form[$field] = (string) ($address->{$field} ?? '');
        }
    }
}

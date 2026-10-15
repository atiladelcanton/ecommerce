<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Customer\CreateCustomerAddressAction;
use App\Actions\Customer\DeleteCustomerAddressAction;
use App\Actions\Customer\SetDefaultCustomerAddressAction;
use App\Actions\Customer\UpdateCustomerAddressAction;
use App\Actions\Order\ValidateCheckoutData;
use App\Actions\Address\LookupPostalCodeAction;
use App\Domain\Address\Exceptions\PostalCodeLookupFailed;
use Illuminate\Http\JsonResponse;
use App\Domain\Customer\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CustomerAddressController
{
    public function index(Request $request): View
    {
        return view('store.customer.addresses.index', [
            'customer' => $request->user('customer'),
            'addresses' => $request->user('customer')->addresses()->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('store.customer.addresses.form', [
            'address' => null,
            'states' => ValidateCheckoutData::STATES,
        ]);
    }

    public function store(Request $request, CreateCustomerAddressAction $action): RedirectResponse
    {
        $action->execute($request->user('customer'), $request->all());
        return redirect()->route('customer.addresses')->with('toast', ['type' => 'success', 'title' => 'Endereço salvo', 'message' => 'Seu novo endereço foi cadastrado.']);
    }

    public function edit(Request $request, CustomerAddress $address): View
    {
        $this->authorizeAddress($request, $address);
        return view('store.customer.addresses.form', ['address' => $address, 'states' => ValidateCheckoutData::STATES]);
    }

    public function update(Request $request, CustomerAddress $address, UpdateCustomerAddressAction $action): RedirectResponse
    {
        $this->authorizeAddress($request, $address);
        $action->execute($address, $request->all());
        return redirect()->route('customer.addresses')->with('toast', ['type' => 'success', 'title' => 'Endereço atualizado', 'message' => 'As alterações foram salvas.']);
    }

    public function destroy(Request $request, CustomerAddress $address, DeleteCustomerAddressAction $action): RedirectResponse
    {
        $this->authorizeAddress($request, $address);
        $action->execute($address);
        return redirect()->route('customer.addresses')->with('toast', ['type' => 'success', 'title' => 'Endereço removido', 'message' => 'O endereço foi removido da sua conta.']);
    }

    public function makeDefault(Request $request, CustomerAddress $address, SetDefaultCustomerAddressAction $action): RedirectResponse
    {
        $this->authorizeAddress($request, $address);
        $action->execute($address);
        return redirect()->route('customer.addresses')->with('toast', ['type' => 'success', 'title' => 'Endereço principal alterado', 'message' => 'Este endereço será usado por padrão no checkout.']);
    }


    public function postalCode(string $postalCode, LookupPostalCodeAction $lookup): JsonResponse
    {
        try {
            $address = $lookup->execute($postalCode);
        } catch (PostalCodeLookupFailed) {
            return response()->json(['message' => 'Não foi possível consultar o CEP agora.'], 503);
        }
        if (! $address) return response()->json(['message' => 'CEP não encontrado.'], 404);
        return response()->json([
            'postal_code' => $address->postalCode,
            'street' => $address->street,
            'neighborhood' => $address->neighborhood,
            'city' => $address->city,
            'state' => $address->state,
            'complement' => $address->complement,
        ]);
    }

    private function authorizeAddress(Request $request, CustomerAddress $address): void
    {
        abort_unless($address->customer_id === $request->user('customer')->id, 404);
    }
}

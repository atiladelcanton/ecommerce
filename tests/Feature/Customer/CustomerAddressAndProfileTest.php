<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Actions\Customer\CreateCustomerAddressAction;
use App\Actions\Customer\DeleteCustomerAddressAction;
use App\Actions\Customer\SetDefaultCustomerAddressAction;
use App\Domain\Customer\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class CustomerAddressAndProfileTest extends TestCase
{
    use RefreshDatabase;

    private function address(array $overrides = []): array
    {
        return array_replace([
            'label' => 'Casa', 'postal_code' => '80000-000', 'street' => 'Rua Teste', 'number' => '10',
            'complement' => null, 'neighborhood' => 'Centro', 'city' => 'Curitiba', 'state' => 'PR', 'is_default' => false,
        ], $overrides);
    }

    public function test_first_address_becomes_default_and_only_one_default_is_kept(): void
    {
        $customer = Customer::factory()->create();
        $first = app(CreateCustomerAddressAction::class)->execute($customer, $this->address());
        $second = app(CreateCustomerAddressAction::class)->execute($customer, $this->address(['label' => 'Empresa', 'number' => '20']));
        $this->assertTrue($first->fresh()->is_default);
        $this->assertFalse($second->fresh()->is_default);

        app(SetDefaultCustomerAddressAction::class)->execute($second);
        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame(1, $customer->addresses()->where('is_default', true)->count());
    }

    public function test_deleting_default_promotes_another_address(): void
    {
        $customer = Customer::factory()->create();
        $first = app(CreateCustomerAddressAction::class)->execute($customer, $this->address());
        $second = app(CreateCustomerAddressAction::class)->execute($customer, $this->address(['label' => 'Empresa', 'number' => '20']));
        app(DeleteCustomerAddressAction::class)->execute($first);
        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_customer_cannot_edit_or_delete_another_customers_address(): void
    {
        $customer = Customer::factory()->create();
        $other = Customer::factory()->create();
        $address = app(CreateCustomerAddressAction::class)->execute($other, $this->address());
        Auth::guard('customer')->login($customer);

        $this->get(route('customer.addresses.edit', $address))->assertNotFound();
        $this->delete(route('customer.addresses.destroy', $address))->assertNotFound();
    }

    public function test_profile_updates_name_email_and_phone_but_not_document(): void
    {
        $customer = Customer::factory()->create(['document' => '52998224725']);
        Auth::guard('customer')->login($customer);

        $this->put(route('customer.profile.update'), [
            'name' => 'Novo Nome', 'email' => 'NOVO@SABA.TEST', 'phone' => '(41) 99999-1234', 'document' => '11111111111',
        ])->assertRedirect();

        $customer->refresh();
        $this->assertSame('Novo Nome', $customer->name);
        $this->assertSame('novo@saba.test', $customer->email);
        $this->assertSame('41999991234', $customer->phone);
        $this->assertSame('52998224725', $customer->document);
    }

    public function test_password_requires_current_password_and_new_password_works(): void
    {
        $customer = Customer::factory()->create(['password' => Hash::make('senha-atual')]);
        Auth::guard('customer')->login($customer);

        $this->from(route('customer.profile'))->put(route('customer.password.update'), [
            'current_password' => 'errada', 'password' => 'senha-nova-123', 'password_confirmation' => 'senha-nova-123',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('customer.password.update'), [
            'current_password' => 'senha-atual', 'password' => 'senha-nova-123', 'password_confirmation' => 'senha-nova-123',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('senha-nova-123', $customer->fresh()->password));
    }
}

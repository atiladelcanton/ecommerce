<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Actions\Cart\AddCartItemAction;
use App\Domain\Catalog\Models\Product;
use App\Livewire\Store\CheckoutPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Support\UsesDemoShipping;
use Tests\TestCase;

final class CheckoutPostalCodeTest extends TestCase
{
    use RefreshDatabase;
    use UsesDemoShipping;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
    }

    private function fakeAddress(array $overrides = []): void
    {
        Http::fake(['*' => Http::response(array_replace([
            'cep' => '01001-000', 'logradouro' => 'Praça da Sé', 'bairro' => 'Sé',
            'localidade' => 'São Paulo', 'uf' => 'SP', 'complemento' => 'lado ímpar', 'ibge' => '3550308',
        ], $overrides))]);
    }

    public function test_checkout_fills_address_and_complement_but_preserves_number(): void
    {
        $this->fakeAddress();
        Livewire::test(CheckoutPage::class)->set('form.number', '123')
            ->set('form.postal_code', '01001-000')->assertHasNoErrors()
            ->assertSet('form.street', 'Praça da Sé')->assertSet('form.neighborhood', 'Sé')
            ->assertSet('form.city', 'São Paulo')->assertSet('form.state', 'SP')
            ->assertSet('form.complement', 'lado ímpar')->assertSet('form.number', '123')
            ->assertSet('postalCodeMessage', null);
        Http::assertSentCount(1);
    }

    public function test_address_fields_remain_editable_and_user_complement_is_preserved(): void
    {
        $this->fakeAddress();
        Livewire::test(CheckoutPage::class)->set('form.complement', 'Apto 42')
            ->set('form.postal_code', '01001000')->assertSet('form.complement', 'Apto 42')
            ->set('form.street', 'Rua editada')->set('form.neighborhood', 'Bairro editado')
            ->set('form.city', 'Cidade editada')->set('form.state', 'RJ')->set('form.number', '456')
            ->set('form.complement', 'Sala 2')
            ->assertSet('form.street', 'Rua editada')->assertSet('form.neighborhood', 'Bairro editado')
            ->assertSet('form.city', 'Cidade editada')->assertSet('form.state', 'RJ')
            ->assertSet('form.number', '456')->assertSet('form.complement', 'Sala 2');
        Http::assertSentCount(1);
    }

    public function test_empty_provider_complement_does_not_erase_user_input(): void
    {
        $this->fakeAddress(['complemento' => '']);
        Livewire::test(CheckoutPage::class)->set('form.complement', 'Apto 42')
            ->set('form.postal_code', '01001000')->assertSet('form.complement', 'Apto 42');
    }

    public function test_incomplete_cep_does_not_call_api_and_can_be_corrected(): void
    {
        $this->fakeAddress();
        $checkout = Livewire::test(CheckoutPage::class)->set('form.postal_code', '01001-00')
            ->assertHasErrors('postal_code');
        Http::assertNothingSent();
        $checkout->set('form.postal_code', '01001-000')->assertHasNoErrors('postal_code')
            ->assertSet('form.street', 'Praça da Sé');
    }

    public function test_unknown_cep_shows_manual_entry_message_and_preserves_existing_address(): void
    {
        Http::fake(['*' => Http::response(['erro' => true])]);
        Livewire::test(CheckoutPage::class)->set('form.street', 'Rua manual')
            ->set('form.postal_code', '99999-999')->assertHasNoErrors()
            ->assertSee('CEP não encontrado. Preencha o endereço manualmente.')
            ->assertSet('form.street', 'Rua manual');
    }

    public function test_timeout_does_not_prevent_checkout_with_manually_entered_address(): void
    {
        Http::fake(fn () => throw new ConnectionException('Timed out'));
        Livewire::test(CheckoutPage::class)->set('form.postal_code', '01001-000')->assertHasNoErrors()
            ->assertSee('Não foi possível consultar o CEP agora. Você pode preencher o endereço manualmente.')
            ->set('form.name', 'Cliente de teste')->set('form.email', 'teste@example.com')
            ->set('form.phone', '(11) 3333-4444')->set('form.document', '529.982.247-25')
            ->set('form.street', 'Rua manual')->set('form.number', '0')->set('form.neighborhood', 'Centro')
            ->set('form.city', 'São Paulo')->set('form.state', 'SP')->call('submit')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('order_addresses', ['street' => 'Rua manual', 'postal_code' => '01001000', 'number' => '0']);
    }
}

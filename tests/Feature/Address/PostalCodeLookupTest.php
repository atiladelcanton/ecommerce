<?php

declare(strict_types=1);

namespace Tests\Feature\Address;

use App\Actions\Address\LookupPostalCodeAction;
use App\Domain\Address\Contracts\PostalCodeLookup;
use App\Domain\Address\Exceptions\PostalCodeLookupFailed;
use App\Infrastructure\Address\ViaCepPostalCodeLookup;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PostalCodeLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function address(array $overrides = []): array
    {
        return array_replace([
            'cep' => '01001-000', 'logradouro' => 'Praça da Sé', 'bairro' => 'Sé',
            'localidade' => 'São Paulo', 'uf' => 'SP', 'complemento' => 'lado ímpar', 'ibge' => '3550308',
        ], $overrides);
    }

    public function test_configured_default_resolves_viacep_through_contract(): void
    {
        $this->assertSame('viacep', config('address.provider'));
        $this->assertInstanceOf(ViaCepPostalCodeLookup::class, app(PostalCodeLookup::class));
    }

    public function test_valid_masked_cep_returns_typed_address_and_sends_only_digits(): void
    {
        Http::fake(['https://viacep.com.br/ws/01001000/json/' => Http::response($this->address())]);
        $address = app(LookupPostalCodeAction::class)->execute('01001-000');
        $this->assertSame('01001000', $address->postalCode);
        $this->assertSame('Praça da Sé', $address->street);
        $this->assertSame('Sé', $address->neighborhood);
        $this->assertSame('São Paulo', $address->city);
        $this->assertSame('SP', $address->state);
        $this->assertSame('lado ímpar', $address->complement);
        $this->assertSame('3550308', $address->ibgeCode);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://viacep.com.br/ws/01001000/json/');
    }

    #[DataProvider('invalidPostalCodes')]
    public function test_invalid_cep_never_calls_external_api(string $postalCode): void
    {
        Http::fake();
        $this->assertNull(app(PostalCodeLookup::class)->find($postalCode));
        try {
            app(LookupPostalCodeAction::class)->execute($postalCode);
            $this->fail('Expected CEP validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('postal_code', $exception->errors());
        }
        Http::assertNothingSent();
    }

    public static function invalidPostalCodes(): array
    {
        return [[''], ['1234567'], ['123456789'], ['abcdefghi']];
    }

    public function test_unknown_cep_returns_null_and_is_cached(): void
    {
        Http::fake(['*' => Http::response(['erro' => true])]);
        $lookup = app(LookupPostalCodeAction::class);
        $this->assertNull($lookup->execute('99999-999'));
        $this->assertNull($lookup->execute('99999999'));
        Http::assertSentCount(1);
    }

    public function test_success_cache_avoids_duplicate_requests_and_expires_after_24_hours(): void
    {
        Http::fake(['*' => Http::response($this->address())]);
        $lookup = app(LookupPostalCodeAction::class);
        $this->assertSame($lookup->execute('01001-000'), $lookup->execute('01001000'));
        Http::assertSentCount(1);
        $this->travel(25)->hours();
        $this->assertSame('SP', $lookup->execute('01001000')->state);
        Http::assertSentCount(2);
    }

    public function test_optional_empty_values_and_general_city_cep_are_supported(): void
    {
        Http::fake(['*' => Http::response($this->address(['logradouro' => '', 'bairro' => '', 'complemento' => ' ', 'ibge' => null]))]);
        $address = app(PostalCodeLookup::class)->find('01001000');
        $this->assertSame('', $address->street);
        $this->assertSame('', $address->neighborhood);
        $this->assertNull($address->complement);
        $this->assertNull($address->ibgeCode);
    }

    #[DataProvider('failedResponses')]
    public function test_http_or_malformed_responses_are_handled_as_temporary_failures(mixed $body, int $status): void
    {
        Http::fake(['*' => Http::response($body, $status)]);
        $this->expectException(PostalCodeLookupFailed::class);
        app(PostalCodeLookup::class)->find('01001000');
    }

    public static function failedResponses(): array
    {
        return [
            ['unavailable', 503], ['not json', 200], [['logradouro' => 'Rua'], 200],
            [['cep' => '01001-000', 'logradouro' => '', 'bairro' => '', 'localidade' => 'SP', 'uf' => ['SP']], 200],
        ];
    }

    public function test_timeout_becomes_handled_failure_and_does_not_poison_cache(): void
    {
        $fail = true;
        Http::fake(function () use (&$fail) {
            if ($fail) {
                throw new ConnectionException('Connection timed out');
            }

            return Http::response($this->address());
        });
        try {
            app(LookupPostalCodeAction::class)->execute('01001000');
            $this->fail('Expected a handled lookup failure.');
        } catch (PostalCodeLookupFailed $exception) {
            $this->assertInstanceOf(ConnectionException::class, $exception->getPrevious());
        }
        $fail = false;
        $this->assertSame('São Paulo', app(LookupPostalCodeAction::class)->execute('01001000')->city);
    }
}

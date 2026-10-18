<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Actions\Settings\UpdateStoreSettingsAction;
use App\Domain\Settings\Models\StoreSetting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class StoreSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_persist_identity_contact_and_address_and_remain_singleton(): void
    {
        $action = app(UpdateStoreSettingsAction::class);
        $settings = $action->execute([
            'store_name' => ' SABA ', 'legal_name' => 'SABA Demonstração', 'cnpj' => '00.000.000/0001-00',
            'whatsapp' => '+55 (11) 99999-0000', 'phone' => '(11) 3333-0000', 'email' => 'loja@example.com',
            'zip_code' => '01001-000', 'street' => 'Rua de Demonstração', 'number' => '10', 'complement' => 'Sala 1',
            'neighborhood' => 'Centro', 'city' => 'São Paulo', 'state' => 'sp',
            'instagram_url' => 'https://instagram.com/exemplo', 'facebook_url' => 'https://facebook.com/exemplo',
            'youtube_url' => 'https://youtube.com/@exemplo', 'seo_title' => 'Saba Equipamentos', 'seo_description' => 'Descrição da loja',
            'shipping_origin_zip_code' => '01001000',
        ]);
        $this->assertSame(StoreSetting::MAIN_ID, $settings->id);
        $this->assertSame('SABA', $settings->store_name);
        $this->assertSame('SP', $settings->state);
        $this->assertSame('+55 (11) 99999-0000', $settings->whatsapp);
        $this->assertSame('01001000', $settings->shipping_origin_zip_code);
        $updated = $action->execute(['store_name' => 'SABA Atualizada', 'phone' => '']);
        $this->assertSame($settings->id, $updated->id);
        $this->assertNull($updated->phone);
        $this->assertSame($settings->whatsapp, $updated->whatsapp);
        $this->assertDatabaseCount('store_settings', 1);
    }

    public function test_database_prevents_a_second_row_even_with_a_different_id(): void
    {
        app(UpdateStoreSettingsAction::class)->execute(['store_name' => 'SABA']);
        $this->expectException(QueryException::class);
        DB::table('store_settings')->insert(['id' => 2, 'store_name' => 'Outra loja']);
    }

    public function test_required_name_and_invalid_contact_values_are_rejected(): void
    {
        foreach ([[], ['store_name' => ''], ['store_name' => 'SABA', 'email' => 'invalid'], ['store_name' => 'SABA', 'shipping_origin_zip_code' => '123']] as $data) {
            try {
                app(UpdateStoreSettingsAction::class)->execute($data);
                $this->fail('Expected invalid settings to be rejected.');
            } catch (ValidationException) {
                $this->assertDatabaseCount('store_settings', 0);
            }
        }
    }
}

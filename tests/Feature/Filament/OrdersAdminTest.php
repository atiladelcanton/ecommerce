<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\OrdersKanban;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrdersAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        $this->actingAs(User::factory()->create());
    }

    public function test_order_and_kanban_admin_routes_render(): void
    {
        $this->get(OrderResource::getUrl('index'))->assertOk();
        $this->get(OrdersKanban::getUrl())->assertOk()->assertSee('Pedidos Kanban');
    }

    public function test_admin_topbar_contains_store_shortcut(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Ver loja')
            ->assertDontSee('Fluxo de Caixa');
    }
}

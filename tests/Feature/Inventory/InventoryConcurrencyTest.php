<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Order\ConfirmQuotedOrderAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Shipping\GetOrderShippingQuoteLinkAction;
use App\Actions\Shipping\PublishOrderShippingQuoteAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Order\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class InventoryConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_sessions_cannot_reserve_the_same_last_unit(): void
    {
        $this->requireMysql();
        $product = Product::factory()->create(['stock' => 1]);
        $results = $this->race(function (int $worker, string $directory) use ($product): string {
            session()->flush();
            session()->setId(Str::random(40));
            app(AddCartItemAction::class)->execute($product->id);
            file_put_contents($directory.'/ready-'.$worker, 'ready');
            $deadline = microtime(true) + 10;
            while (count(glob($directory.'/ready-*')) < 2 && microtime(true) < $deadline) {
                usleep(10000);
            }
            try {
                app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), app(GetCheckoutTokenAction::class)->execute());

                return 'reserved';
            } catch (ValidationException) {
                return 'unavailable';
            }
        });
        sort($results);
        $this->assertSame(['reserved', 'unavailable'], $results);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('inventory_reservations', 1);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_parallel_payment_confirmation_debits_stock_and_creates_payment_once(): void
    {
        $this->requireMysql();
        $product = Product::factory()->create(['stock' => 3]);
        app(AddCartItemAction::class)->execute($product->id);
        $order = app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), app(GetCheckoutTokenAction::class)->execute());
        $quote = app(PublishOrderShippingQuoteAction::class)->execute($order, ['carrier_name' => 'Transportadora', 'service_name' => 'Rodoviário', 'price_cents' => 4890, 'expires_at' => now()->addDays(7)], User::factory()->create()->id);
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);
        $results = $this->race(fn (): string => app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, $link)->status->value);
        $this->assertSame(['paid', 'paid'], $results);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('inventory_reservations', 1);
    }

    protected function tearDown(): void
    {
        // This class owns the isolated database. Remove its unfinished fixtures before
        // exercising a down migration that deliberately refuses unknown freight totals.
        if (Schema::hasTable('orders')) {
            Order::query()->whereNull('total_cents')->delete();
        }
        parent::tearDown();
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql' || ! function_exists('pcntl_fork')) {
            $this->markTestSkipped('Requires isolated MySQL database and pcntl.');
        }
        config(['cache.default' => 'database']);
        Cache::purge('database');
    }

    private function race(callable $operation): array
    {
        $directory = sys_get_temp_dir().'/saba-inventory-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        $children = [];
        try {
            for ($worker = 0; $worker < 2; $worker++) {
                $pid = pcntl_fork();
                if ($pid === -1) {
                    self::fail('Cannot fork inventory worker');
                }
                if ($pid === 0) {
                    DB::purge();
                    Cache::purge('database');
                    Http::preventStrayRequests();
                    try {
                        file_put_contents($directory.'/result-'.$worker, $operation($worker, $directory));
                    } catch (\Throwable $exception) {
                        file_put_contents($directory.'/result-'.$worker, 'ERROR:'.$exception::class.':'.$exception->getMessage());
                    }
                    exit(0);
                }
                $children[] = $pid;
            }
            foreach ($children as $pid) {
                pcntl_waitpid($pid, $status);
                $this->assertSame(0, pcntl_wexitstatus($status));
            }
            DB::purge();

            return [file_get_contents($directory.'/result-0'), file_get_contents($directory.'/result-1')];
        } finally {
            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }

    private function input(): array
    {
        return ['name' => 'Cliente', 'email' => 'cliente@example.test', 'phone' => '11999999999', 'document' => '52998224725', 'postal_code' => '13077001', 'street' => 'Rua teste', 'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP'];
    }
}

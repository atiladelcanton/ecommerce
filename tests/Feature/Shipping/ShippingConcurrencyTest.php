<?php

declare(strict_types=1);

namespace Tests\Feature\Shipping;

use App\Domain\Shipping\Models\Shipment;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\ShippingFixtures;
use Tests\TestCase;

final class ShippingConcurrencyTest extends TestCase
{
    use DatabaseMigrations;
    use ShippingFixtures;

    public function test_two_processes_purchase_the_same_fulfillment_only_once(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql' || ! function_exists('pcntl_fork')) {
            $this->markTestSkipped('Requires isolated MySQL testing database and pcntl.');
        }
        $order = $this->order();
        config(['cache.default' => 'database']);
        Cache::purge('database');
        $directory = sys_get_temp_dir().'/saba-shipping-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        $children = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $pid = pcntl_fork();
                if ($pid === -1) {
                    self::fail('Could not fork test worker.');
                }
                if ($pid === 0) {
                    DB::purge();
                    Cache::purge('database');
                    Http::fake(function ($request) use ($directory) {
                        $operation = basename(parse_url($request->url(), PHP_URL_PATH));
                        file_put_contents($directory.'/requests', $operation."\n", FILE_APPEND | LOCK_EX);
                        if ($operation === 'cart') {
                            usleep(250000);

                            return Http::response(['id' => 'concurrent-external', 'price' => '48.90']);
                        }

                        return Http::response(['success' => true]);
                    });
                    try {
                        $shipment = $this->generate($order);
                        file_put_contents($directory.'/result-'.$i, (string) $shipment->id);
                    } catch (\Throwable $exception) {
                        file_put_contents($directory.'/result-'.$i, $exception::class.': '.$exception->getMessage());
                        exit(1);
                    }
                    exit(0);
                }
                $children[] = $pid;
            }
            foreach ($children as $pid) {
                pcntl_waitpid($pid, $status);
                $this->assertSame(0, pcntl_wexitstatus($status), 'Shipping worker failed: '.implode(' ', array_map('file_get_contents', glob($directory.'/result-*'))));
            }
            DB::purge();
            Cache::purge('database');
            $requests = file($directory.'/requests', FILE_IGNORE_NEW_LINES);
            $this->assertSame(1, count(array_filter($requests, fn ($stage) => $stage === 'cart')));
            $this->assertSame(1, count(array_filter($requests, fn ($stage) => $stage === 'checkout')));
            $this->assertSame(1, count(array_filter($requests, fn ($stage) => $stage === 'generate')));
            $this->assertSame(file_get_contents($directory.'/result-0'), file_get_contents($directory.'/result-1'));
            $this->assertSame(1, Shipment::query()->where('order_id', $order->id)->count());
        } finally {
            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}

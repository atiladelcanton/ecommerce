<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Livewire\Store\ProductList;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class StorefrontPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function renderPagination(int $page, int $lastPage): string
    {
        $paginator = new LengthAwarePaginator(range(1, 12), $lastPage * 12, 12, $page);

        return view('pagination.storefront', ['paginator' => $paginator])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);

        return new DOMXPath($document);
    }

    public static function pageWindows(): array
    {
        return [
            'first page' => [1, 12, [1, 2, 3, 4, 12], [1, 2, 3]],
            'middle page' => [6, 12, [1, 4, 5, 6, 7, 8, 12], [5, 6, 7]],
            'last page' => [12, 12, [1, 9, 10, 11, 12], [10, 11, 12]],
            'many pages' => [500, 1000, [1, 498, 499, 500, 501, 502, 1000], [499, 500, 501]],
            'few pages' => [2, 3, [1, 2, 3], [1, 2, 3]],
        ];
    }

    #[DataProvider('pageWindows')]
    public function test_desktop_and_mobile_windows_are_bounded(int $page, int $lastPage, array $desktop, array $mobile): void
    {
        $html = $this->renderPagination($page, $lastPage);
        $xpath = $this->xpath($html);
        $numbers = $xpath->query('//nav/*[@aria-current="page" or starts-with(@aria-label, "Ver página")]');
        $desktopNumbers = [];
        $mobileNumbers = [];
        foreach ($numbers as $number) {
            $desktopNumbers[] = (int) $number->textContent;
            if (! str_contains($number->getAttribute('class'), 'storefront-pagination-desktop-only')) {
                $mobileNumbers[] = (int) $number->textContent;
            }
        }
        $this->assertSame($desktop, $desktopNumbers);
        $this->assertSame($mobile, $mobileNumbers);
        $this->assertSame((string) $page, $xpath->query('//*[@aria-current="page"]')->item(0)->textContent);
        $this->assertStringContainsString('Página '.$page.' de '.$lastPage, $html);
        $this->assertSame($page === 1, $xpath->query('//button[@aria-label="Página anterior"]')->item(0)->hasAttribute('disabled'));
        $this->assertSame($page === $lastPage, $xpath->query('//button[@aria-label="Próxima página"]')->item(0)->hasAttribute('disabled'));
        $this->assertLessThanOrEqual(7, count($desktopNumbers));
        if ($lastPage > 7) {
            $this->assertStringContainsString('…', $html);
        }
    }

    public function test_single_page_shows_summary_without_navigation(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 5), 5, 12, 1);
        $html = view('pagination.storefront', ['paginator' => $paginator])->render();
        $this->assertStringContainsString('Mostrando 1–5 de 5 produtos', $html);
        $this->assertStringNotContainsString('<nav', $html);
        $this->assertStringNotContainsString('<button', $html);
    }

    public function test_livewire_page_changes_preserve_all_existing_filters_search_and_sort(): void
    {
        $category = Category::factory()->create();
        foreach (range(1, 25) as $index) {
            Product::factory()->create([
                'category_id' => $category->id,
                'name' => 'Tinta SABA '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'price' => 10000, 'promotional_price' => 8000,
            ]);
        }
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Outro produto']);
        $filters = [
            'category' => $category->slug, 'availability' => 'immediate',
            'promotion' => true, 'search' => 'Tinta', 'minimum' => '50,00',
            'maximum' => '100,00', 'sort' => 'nome-az',
        ];
        $component = Livewire::test(ProductList::class);
        foreach ($filters as $field => $value) {
            $component->set($field, $value);
        }
        $component->assertSee('Mostrando 1–12 de 25 produtos')
            ->call('gotoPage', 2)->assertSee('Mostrando 13–24 de 25 produtos')
            ->assertSee('Tinta SABA 13')->assertDontSee('Tinta SABA 01')
            ->assertViewHas('products', fn ($products): bool => $products->perPage() === 12 && $products->currentPage() === 2);
        foreach ($filters as $field => $value) {
            $component->assertSet($field, $value);
        }
        $component->call('nextPage')->assertSee('Mostrando 25–25 de 25 produtos')
            ->call('previousPage')->assertSee('Mostrando 13–24 de 25 produtos');
        $component->assertSee('id="store-products-grid"', false)
            ->assertSee('.then(() => scrollToGrid())', false);
    }
}

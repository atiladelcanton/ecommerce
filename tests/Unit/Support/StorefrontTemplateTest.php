<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Storefront\Template;
use Tests\TestCase;

final class StorefrontTemplateTest extends TestCase
{
    public function test_default_template_is_original(): void
    {
        config()->set('storefront_templates.active', '1');

        $this->assertSame(1, Template::active());
        $this->assertFalse(Template::is(2));
    }

    public function test_warehouse_template_can_be_selected(): void
    {
        config()->set('storefront_templates.active', '2');

        $this->assertSame(2, Template::active());
        $this->assertTrue(Template::is(2));
    }

    public function test_unknown_template_falls_back_to_original(): void
    {
        config()->set('storefront_templates.active', '999');

        $this->assertSame(1, Template::active());
    }
}

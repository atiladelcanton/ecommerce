<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ArchitecturePlaceholderTest extends TestCase
{
    public function test_starter_has_domain_directories(): void
    {
        self::assertDirectoryExists(__DIR__.'/../../app/Domain/Catalog');
        self::assertDirectoryExists(__DIR__.'/../../app/Actions');
    }
}

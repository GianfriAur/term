<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\Focus;
use PHPUnit\Framework\TestCase;

final class FocusTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            ['Gained', 'Lost'],
            array_map(fn (Focus $c) => $c->name, Focus::cases()),
        );
    }
}

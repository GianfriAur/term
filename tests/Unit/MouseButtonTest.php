<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\MouseButton;
use PHPUnit\Framework\TestCase;

final class MouseButtonTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            ['Left', 'Right', 'Middle', 'None'],
            array_map(fn (MouseButton $c) => $c->name, MouseButton::cases()),
        );
    }
}

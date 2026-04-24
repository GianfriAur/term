<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\Colors;
use PHPUnit\Framework\TestCase;

final class ColorsTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            [
                'Reset',
                'Black', 'Red', 'Green', 'Yellow', 'Blue', 'Magenta', 'Cyan',
                'Gray', 'DarkGray',
                'LightRed', 'LightGreen', 'LightYellow', 'LightBlue',
                'LightMagenta', 'LightCyan', 'White',
            ],
            array_map(fn (Colors $c) => $c->name, Colors::cases()),
        );
    }
}

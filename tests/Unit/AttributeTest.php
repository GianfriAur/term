<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\Attribute;
use PHPUnit\Framework\TestCase;

final class AttributeTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            [
                'Reset', 'Bold', 'Dim', 'Italic', 'Underline',
                'Strike', 'SlowBlink', 'RapidBlink', 'Hidden', 'Reverse',
            ],
            array_map(fn (Attribute $c) => $c->name, Attribute::cases()),
        );
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\KeyEventKind;
use PHPUnit\Framework\TestCase;

final class KeyEventKindTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            ['Press', 'Repeat', 'Release'],
            array_map(fn (KeyEventKind $c) => $c->name, KeyEventKind::cases()),
        );
    }
}

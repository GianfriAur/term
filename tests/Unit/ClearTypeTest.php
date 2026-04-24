<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\ClearType;
use PHPUnit\Framework\TestCase;

final class ClearTypeTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            ['All', 'FromCursorDown', 'Purge', 'CurrentLine', 'FromCursorUp', 'UntilNewLine'],
            array_map(fn (ClearType $c) => $c->name, ClearType::cases()),
        );
    }
}

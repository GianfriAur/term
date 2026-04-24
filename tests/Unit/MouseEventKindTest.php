<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\MouseEventKind;
use PHPUnit\Framework\TestCase;

final class MouseEventKindTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            [
                'Down', 'Up', 'Drag', 'Moved',
                'ScrollDown', 'ScrollUp', 'ScrollLeft', 'ScrollRight',
            ],
            array_map(fn (MouseEventKind $c) => $c->name, MouseEventKind::cases()),
        );
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\KeyCode;
use PHPUnit\Framework\TestCase;

final class KeyCodeTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            [
                'Backspace', 'Enter', 'Left', 'Right', 'Up', 'Down',
                'Home', 'End', 'PageUp', 'PageDown', 'Tab', 'BackTab',
                'Delete', 'Insert', 'FKey', 'Char', 'Esc',
            ],
            array_map(fn (KeyCode $c) => $c->name, KeyCode::cases()),
        );
    }
}

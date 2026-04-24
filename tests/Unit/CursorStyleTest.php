<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\CursorStyle;
use PHPUnit\Framework\TestCase;

final class CursorStyleTest extends TestCase
{
    public function testEnumCases(): void
    {
        self::assertEqualsCanonicalizing(
            [
                'DefaultUserShape', 'BlinkingBlock', 'SteadyBlock',
                'BlinkingUnderScore', 'SteadyUnderScore',
                'BlinkingBar', 'SteadyBar',
            ],
            array_map(fn (CursorStyle $c) => $c->name, CursorStyle::cases()),
        );
    }
}

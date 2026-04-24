<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorUp;
use PHPUnit\Framework\TestCase;

final class MoveCursorUpTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorUp(1));
    }

    public function testExposesLines(): void
    {
        self::assertSame(5, (new MoveCursorUp(5))->lines);
        self::assertSame(0, (new MoveCursorUp(0))->lines);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorUp(3)', (string) new MoveCursorUp(3));
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorDown;
use PHPUnit\Framework\TestCase;

final class MoveCursorDownTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorDown(1));
    }

    public function testExposesLines(): void
    {
        self::assertSame(5, (new MoveCursorDown(5))->lines);
        self::assertSame(0, (new MoveCursorDown(0))->lines);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorDown(3)', (string) new MoveCursorDown(3));
    }
}

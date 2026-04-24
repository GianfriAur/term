<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorLeft;
use PHPUnit\Framework\TestCase;

final class MoveCursorLeftTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorLeft(1));
    }

    public function testExposesCols(): void
    {
        self::assertSame(5, (new MoveCursorLeft(5))->cols);
        self::assertSame(0, (new MoveCursorLeft(0))->cols);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorLeft(3)', (string) new MoveCursorLeft(3));
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\RestoreCursorPosition;
use PHPUnit\Framework\TestCase;

final class RestoreCursorPositionTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new RestoreCursorPosition());
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('RestoreCursorPosition()', (string) new RestoreCursorPosition());
    }
}

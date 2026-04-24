<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\SaveCursorPosition;
use PHPUnit\Framework\TestCase;

final class SaveCursorPositionTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new SaveCursorPosition());
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('SaveCursorPosition()', (string) new SaveCursorPosition());
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\RequestCursorPosition;
use PHPUnit\Framework\TestCase;

final class RequestCursorPositionTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new RequestCursorPosition());
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('RequestCursorPosition()', (string) new RequestCursorPosition());
    }
}

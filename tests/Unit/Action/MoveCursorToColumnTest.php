<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorToColumn;
use PHPUnit\Framework\TestCase;

final class MoveCursorToColumnTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorToColumn(1));
    }

    public function testExposesCol(): void
    {
        self::assertSame(5, (new MoveCursorToColumn(5))->col);
        self::assertSame(0, (new MoveCursorToColumn(0))->col);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorToColumn(3)', (string) new MoveCursorToColumn(3));
    }
}

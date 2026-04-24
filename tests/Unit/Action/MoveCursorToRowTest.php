<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\MoveCursorToRow;
use PHPUnit\Framework\TestCase;

final class MoveCursorToRowTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new MoveCursorToRow(1));
    }

    public function testExposesRow(): void
    {
        self::assertSame(5, (new MoveCursorToRow(5))->row);
        self::assertSame(0, (new MoveCursorToRow(0))->row);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('MoveCursorToRow(3)', (string) new MoveCursorToRow(3));
    }
}

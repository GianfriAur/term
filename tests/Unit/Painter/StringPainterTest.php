<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Painter;

use PhpTui\Term\Action\MoveCursor;
use PhpTui\Term\Action\PrintString;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Painter;
use PhpTui\Term\Painter\StringPainter;
use PHPUnit\Framework\TestCase;

final class StringPainterTest extends TestCase
{
    public function testImplementsPainterInterface(): void
    {
        self::assertInstanceOf(Painter::class, new StringPainter());
    }

    public function testEmptyPainterRendersToEmptyString(): void
    {
        self::assertSame('', (new StringPainter())->toString());
    }

    public function testPrintStringRendersAtOrigin(): void
    {
        $painter = new StringPainter();
        $painter->paint([new PrintString('hello')]);

        self::assertSame('hello', $painter->toString());
    }

    public function testMoveCursorRepositionsBeforeNextPrint(): void
    {
        $painter = new StringPainter();
        $painter->paint([
            new PrintString('AA'),
            new MoveCursor(2, 1),
            new PrintString('BB'),
        ]);

        self::assertSame("AA\nBB", $painter->toString());
    }

    public function testGapsAreFilledWithSpaces(): void
    {
        $painter = new StringPainter();
        $painter->paint([
            new MoveCursor(1, 5),
            new PrintString('X'),
        ]);

        self::assertSame('    X', $painter->toString());
    }

    public function testLaterWritesOverwriteEarlierAtSameCell(): void
    {
        $painter = new StringPainter();
        $painter->paint([
            new PrintString('aaa'),
            new MoveCursor(1, 2),
            new PrintString('B'),
        ]);

        self::assertSame('aBa', $painter->toString());
    }

    public function testNonPrintNonMoveActionsAreIgnored(): void
    {
        $painter = new StringPainter();
        $painter->paint([new Reset(), new PrintString('hi'), new Reset()]);

        self::assertSame('hi', $painter->toString());
    }

    public function testMultiByteCharactersOccupyOneCellEach(): void
    {
        $painter = new StringPainter();
        $painter->paint([new PrintString('a£b')]);

        self::assertSame('a£b', $painter->toString());
    }
}

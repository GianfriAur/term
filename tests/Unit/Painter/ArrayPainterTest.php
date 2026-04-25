<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Painter;

use PhpTui\Term\Action\PrintString;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Painter;
use PhpTui\Term\Painter\ArrayPainter;
use PHPUnit\Framework\TestCase;

final class ArrayPainterTest extends TestCase
{
    public function testImplementsPainterInterface(): void
    {
        self::assertInstanceOf(Painter::class, ArrayPainter::new());
    }

    public function testStartsWithNoActions(): void
    {
        self::assertSame([], ArrayPainter::new()->actions());
    }

    public function testPaintAccumulatesActionsInOrder(): void
    {
        $a = new PrintString('a');
        $b = new Reset();
        $c = new PrintString('c');

        $painter = ArrayPainter::new();
        $painter->paint([$a, $b, $c]);

        self::assertSame([$a, $b, $c], $painter->actions());
    }

    public function testSubsequentPaintsAreAppended(): void
    {
        $a = new PrintString('a');
        $b = new PrintString('b');

        $painter = ArrayPainter::new();
        $painter->paint([$a]);
        $painter->paint([$b]);

        self::assertSame([$a, $b], $painter->actions());
    }

    public function testPaintingEmptyListDoesNothing(): void
    {
        $painter = ArrayPainter::new();
        $painter->paint([]);

        self::assertSame([], $painter->actions());
    }
}

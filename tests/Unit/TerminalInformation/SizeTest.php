<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\TerminalInformation;

use PhpTui\Term\TerminalInformation;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;
use Stringable;

final class SizeTest extends TestCase
{
    /** @noinspection PhpConditionAlreadyCheckedInspection */
    public function testImplementsTerminalInformationAndStringable(): void
    {
        $size = new Size(24, 80);

        self::assertInstanceOf(TerminalInformation::class, $size);
        self::assertInstanceOf(Stringable::class, $size);
    }

    public function testExposesLinesAndCols(): void
    {
        $size = new Size(24, 80);

        self::assertSame(24, $size->lines);
        self::assertSame(80, $size->cols);
    }

    public function testStringRepresentationIsColsByLines(): void
    {
        self::assertSame('80x24', (string) new Size(24, 80));
        self::assertSame('0x0', (string) new Size(0, 0));
        self::assertSame('132x50', (string) new Size(50, 132));
    }
}

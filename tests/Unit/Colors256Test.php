<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\Colors256;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class Colors256Test extends TestCase
{
    /**
     * @dataProvider provideKnownIndices
     * @param array{int,int,int} $expected
     */
    public function testIndexToRgbReturnsExpectedTriplet(int $index, array $expected): void
    {
        self::assertSame($expected, Colors256::indexToRgb($index));
    }

    /**
     * @return iterable<string, array{int, array{int,int,int}}>
     */
    public static function provideKnownIndices(): iterable
    {
        yield 'first ansi black'    => [0,   [0, 0, 0]];
        yield 'ansi red'            => [1,   [128, 0, 0]];
        yield 'ansi bright white'   => [15,  [255, 255, 255]];
        yield '6x6x6 cube start'    => [16,  [0, 0, 0]];
        yield '6x6x6 cube middle'   => [123, [135, 255, 255]];
        yield '6x6x6 cube end'      => [231, [255, 255, 255]];
        yield 'grayscale start'     => [232, [8, 8, 8]];
        yield 'grayscale end'       => [255, [238, 238, 238]];
    }

    public function testIndexToRgbCoversEveryIndexFrom0To255(): void
    {
        for ($i = 0; $i <= 255; $i++) {
            $rgb = Colors256::indexToRgb($i);
            self::assertCount(3, $rgb);
            foreach ($rgb as $component) {
                self::assertGreaterThanOrEqual(0, $component);
                self::assertLessThanOrEqual(255, $component);
            }
        }
    }

    public function testIndexToRgbThrowsForNegativeIndex(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('256 Color index "-1" is invalid');

        Colors256::indexToRgb(-1);
    }

    public function testIndexToRgbThrowsForIndexAbove255(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('256 Color index "256" is invalid');

        Colors256::indexToRgb(256);
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\ScrollDown;
use PHPUnit\Framework\TestCase;

final class ScrollDownTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new ScrollDown());
    }

    public function testExposesRowsWithDefault(): void
    {
        self::assertSame(1, (new ScrollDown())->rows);
        self::assertSame(5, (new ScrollDown(5))->rows);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('ScrollDown(1)', (string) new ScrollDown());
        self::assertSame('ScrollDown(3)', (string) new ScrollDown(3));
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\ScrollUp;
use PHPUnit\Framework\TestCase;

final class ScrollUpTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new ScrollUp());
    }

    public function testExposesRowsWithDefault(): void
    {
        self::assertSame(1, (new ScrollUp())->rows);
        self::assertSame(5, (new ScrollUp(5))->rows);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('ScrollUp(1)', (string) new ScrollUp());
        self::assertSame('ScrollUp(3)', (string) new ScrollUp(3));
    }
}

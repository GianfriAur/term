<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Event;

use PhpTui\Term\Event;
use PhpTui\Term\Event\CursorPositionEvent;
use PHPUnit\Framework\TestCase;

final class CursorPositionEventTest extends TestCase
{
    public function testImplementsEventInterface(): void
    {
        self::assertInstanceOf(Event::class, new CursorPositionEvent(0, 0));
    }

    public function testExposesXAndY(): void
    {
        $event = new CursorPositionEvent(12, 34);

        self::assertSame(12, $event->x);
        self::assertSame(34, $event->y);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('CursorPosition(0, 0)', (string) new CursorPositionEvent(0, 0));
        self::assertSame('CursorPosition(12, 34)', (string) new CursorPositionEvent(12, 34));
    }
}

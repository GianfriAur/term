<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Event;

use PhpTui\Term\Event;
use PhpTui\Term\Event\FocusEvent;
use PhpTui\Term\Focus;
use PHPUnit\Framework\TestCase;

final class FocusEventTest extends TestCase
{
    public function testImplementsEventInterface(): void
    {
        self::assertInstanceOf(Event::class, FocusEvent::gained());
        self::assertInstanceOf(Event::class, FocusEvent::lost());
    }

    public function testGainedFactoryExposesGainedFocus(): void
    {
        self::assertSame(Focus::Gained, FocusEvent::gained()->focus);
    }

    public function testLostFactoryExposesLostFocus(): void
    {
        self::assertSame(Focus::Lost, FocusEvent::lost()->focus);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('Focus(Gained)', (string) FocusEvent::gained());
        self::assertSame('Focus(Lost)', (string) FocusEvent::lost());
    }
}

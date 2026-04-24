<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Event;

use PhpTui\Term\Event;
use PhpTui\Term\Event\TerminalResizedEvent;
use PHPUnit\Framework\TestCase;

final class TerminalResizedEventTest extends TestCase
{
    public function testImplementsEventInterface(): void
    {
        self::assertInstanceOf(Event::class, new TerminalResizedEvent());
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('TerminalResized()', (string) new TerminalResizedEvent());
    }
}

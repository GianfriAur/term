<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\EventProvider;

use PhpTui\Term\Event\TerminalResizedEvent;
use PhpTui\Term\EventProvider;
use PhpTui\Term\EventProvider\SignalEventProvider;
use PHPUnit\Framework\TestCase;

final class SignalEventProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!function_exists('pcntl_signal') || !function_exists('posix_kill')) {
            self::markTestSkipped('pcntl/posix extensions are required');
        }

        // Drain leftover events from prior tests — state is process-wide static.
        $provider = SignalEventProvider::registered();
        while ($provider->next() !== null) {
        }
    }

    public function testImplementsEventProviderInterface(): void
    {
        self::assertInstanceOf(EventProvider::class, SignalEventProvider::registered());
    }

    public function testEmitsTerminalResizedEventOnSigwinch(): void
    {
        $provider = SignalEventProvider::registered();

        posix_kill(posix_getpid(), SIGWINCH);
        pcntl_signal_dispatch();

        self::assertInstanceOf(TerminalResizedEvent::class, $provider->next());
        self::assertNull($provider->next());
    }

    public function testReturnsNullWhenNoSignalReceived(): void
    {
        self::assertNull(SignalEventProvider::registered()->next());
    }
}

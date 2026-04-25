<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\EventProvider;

use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\EventParser;
use PhpTui\Term\EventProvider;
use PhpTui\Term\EventProvider\SyncTtyEventProvider;
use PhpTui\Term\Reader\ArrayReader;
use PHPUnit\Framework\TestCase;

final class SyncTtyEventProviderTest extends TestCase
{
    public function testImplementsEventProviderInterface(): void
    {
        self::assertInstanceOf(
            EventProvider::class,
            new SyncTtyEventProvider(new ArrayReader([]), EventParser::new()),
        );
    }

    public function testNewFactoryReturnsInstance(): void
    {
        try {
            self::assertInstanceOf(SyncTtyEventProvider::class, SyncTtyEventProvider::new());
        } finally {
            stream_set_blocking(STDIN, true);
        }
    }

    public function testReturnsNullWhenReaderIsEmpty(): void
    {
        $provider = new SyncTtyEventProvider(new ArrayReader([]), EventParser::new());

        self::assertNull($provider->next());
    }

    public function testParsesAndReturnsEventsFromReader(): void
    {
        $provider = new SyncTtyEventProvider(new ArrayReader(['ab']), EventParser::new());

        self::assertEquals(CharKeyEvent::new('a'), $provider->next());
        self::assertEquals(CharKeyEvent::new('b'), $provider->next());
        self::assertNull($provider->next());
    }

    public function testDrainsAcrossMultipleReaderChunks(): void
    {
        $provider = new SyncTtyEventProvider(
            new ArrayReader(['x', 'y', 'z']),
            EventParser::new(),
        );

        self::assertEquals(CharKeyEvent::new('x'), $provider->next());
        self::assertEquals(CharKeyEvent::new('y'), $provider->next());
        self::assertEquals(CharKeyEvent::new('z'), $provider->next());
        self::assertNull($provider->next());
    }

    public function testBuffersAdditionalEventsBetweenCalls(): void
    {
        $provider = new SyncTtyEventProvider(new ArrayReader(['ab']), EventParser::new());

        $first = $provider->next();
        $second = $provider->next();

        self::assertEquals(CharKeyEvent::new('a'), $first);
        self::assertEquals(CharKeyEvent::new('b'), $second);
    }
}

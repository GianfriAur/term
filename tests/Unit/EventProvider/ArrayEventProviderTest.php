<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\EventProvider;

use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\EventProvider;
use PhpTui\Term\EventProvider\ArrayEventProvider;
use PHPUnit\Framework\TestCase;

final class ArrayEventProviderTest extends TestCase
{
    public function testImplementsEventProviderInterface(): void
    {
        self::assertInstanceOf(EventProvider::class, new ArrayEventProvider([]));
    }

    public function testEmptyProviderReturnsNull(): void
    {
        self::assertNull((new ArrayEventProvider([]))->next());
    }

    public function testReturnsEventsInOrder(): void
    {
        $a = CharKeyEvent::new('a');
        $b = CharKeyEvent::new('b');
        $c = CharKeyEvent::new('c');

        $provider = new ArrayEventProvider([$a, $b, $c]);

        self::assertSame($a, $provider->next());
        self::assertSame($b, $provider->next());
        self::assertSame($c, $provider->next());
        self::assertNull($provider->next());
    }

    public function testFromEventsFactoryStripsNamedKeys(): void
    {
        $a = CharKeyEvent::new('a');
        $b = CharKeyEvent::new('b');

        $provider = ArrayEventProvider::fromEvents($a, $b);

        self::assertSame($a, $provider->next());
        self::assertSame($b, $provider->next());
        self::assertNull($provider->next());
    }

    public function testFromEventsAcceptsNullEntries(): void
    {
        $a = CharKeyEvent::new('a');

        $provider = ArrayEventProvider::fromEvents(null, $a, null);

        self::assertNull($provider->next());
        self::assertSame($a, $provider->next());
        self::assertNull($provider->next());
    }
}

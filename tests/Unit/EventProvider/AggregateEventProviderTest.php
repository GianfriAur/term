<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\EventProvider;

use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\EventProvider;
use PhpTui\Term\EventProvider\AggregateEventProvider;
use PhpTui\Term\EventProvider\ArrayEventProvider;
use PHPUnit\Framework\TestCase;

final class AggregateEventProviderTest extends TestCase
{
    public function testImplementsEventProviderInterface(): void
    {
        self::assertInstanceOf(EventProvider::class, new AggregateEventProvider([]));
    }

    public function testNoProvidersYieldsNull(): void
    {
        self::assertNull((new AggregateEventProvider([]))->next());
    }

    public function testFallsThroughEmptyProvidersToTheFirstWithEvents(): void
    {
        $event = CharKeyEvent::new('f');

        $provider = new AggregateEventProvider([
            new ArrayEventProvider([]),
            new ArrayEventProvider([$event]),
        ]);

        self::assertSame($event, $provider->next());
        self::assertNull($provider->next());
    }

    public function testDrainsEachProviderInOrder(): void
    {
        $first = CharKeyEvent::new('g');
        $second = CharKeyEvent::new('f');

        $provider = new AggregateEventProvider([
            new ArrayEventProvider([$first]),
            new ArrayEventProvider([$second]),
        ]);

        self::assertSame($first, $provider->next());
        self::assertSame($second, $provider->next());
        self::assertNull($provider->next());
    }

    public function testStopsAtFirstProviderThatReturnsAnEvent(): void
    {
        $earlyEvent = CharKeyEvent::new('e');
        $lateEvent = CharKeyEvent::new('l');

        $provider = new AggregateEventProvider([
            new ArrayEventProvider([$earlyEvent]),
            new ArrayEventProvider([$lateEvent]),
        ]);

        self::assertSame($earlyEvent, $provider->next());
    }
}

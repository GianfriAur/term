<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\InformationProvider;

use PhpTui\Term\InformationProvider;
use PhpTui\Term\InformationProvider\AggregateInformationProvider;
use PhpTui\Term\InformationProvider\ClosureInformationProvider;
use PhpTui\Term\TerminalInformation;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;

final class AggregateInformationProviderTest extends TestCase
{
    public function testImplementsInformationProvider(): void
    {
        self::assertInstanceOf(
            InformationProvider::class,
            AggregateInformationProvider::new([]),
        );
    }

    public function testReturnsNullWhenNoProvidersAreRegistered(): void
    {
        self::assertNull(AggregateInformationProvider::new([])->for(Size::class));
    }

    public function testReturnsNullWhenNoProviderHasAMatch(): void
    {
        $provider = AggregateInformationProvider::new([
            ClosureInformationProvider::new(static fn (): ?TerminalInformation => null),
            ClosureInformationProvider::new(static fn (): ?TerminalInformation => null),
        ]);

        self::assertNull($provider->for(Size::class));
    }

    public function testReturnsFirstMatchingResultAndShortCircuits(): void
    {
        $first = new Size(10, 20);
        $secondCalls = 0;

        $provider = AggregateInformationProvider::new([
            ClosureInformationProvider::new(static fn (): TerminalInformation => $first),
            ClosureInformationProvider::new(static function () use (&$secondCalls): ?TerminalInformation {
                $secondCalls++;
                return new Size(99, 99);
            }),
        ]);

        self::assertSame($first, $provider->for(Size::class));
        self::assertSame(0, $secondCalls);
    }

    public function testFallsThroughToLaterProviderWhenEarlierReturnsNull(): void
    {
        $expected = new Size(40, 80);

        $provider = AggregateInformationProvider::new([
            ClosureInformationProvider::new(static fn (): ?TerminalInformation => null),
            ClosureInformationProvider::new(static fn (): TerminalInformation => $expected),
        ]);

        self::assertSame($expected, $provider->for(Size::class));
    }

    public function testForwardsTheRequestedClassFqnToProviders(): void
    {
        $received = [];
        $provider = AggregateInformationProvider::new([
            ClosureInformationProvider::new(static function (string $fqn) use (&$received): ?TerminalInformation {
                $received[] = $fqn;
                return null;
            }),
        ]);

        $provider->for(Size::class);

        self::assertSame([Size::class], $received);
    }
}

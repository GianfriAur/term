<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\InformationProvider;

use PhpTui\Term\InformationProvider;
use PhpTui\Term\InformationProvider\ClosureInformationProvider;
use PhpTui\Term\TerminalInformation;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;

final class ClosureInformationProviderTest extends TestCase
{
    public function testImplementsInformationProvider(): void
    {
        self::assertInstanceOf(
            InformationProvider::class,
            ClosureInformationProvider::new(static fn (): ?TerminalInformation => null),
        );
    }

    public function testForwardsRequestedClassFqnToTheClosure(): void
    {
        $received = null;
        $provider = ClosureInformationProvider::new(
            static function (string $fqn) use (&$received): ?TerminalInformation {
                $received = $fqn;
                return null;
            },
        );

        $provider->for(Size::class);

        self::assertSame(Size::class, $received);
    }

    public function testReturnsTheClosureResult(): void
    {
        $size = new Size(10, 20);
        $provider = ClosureInformationProvider::new(static fn (): TerminalInformation => $size);

        self::assertSame($size, $provider->for(Size::class));
    }

    public function testReturnsNullWhenClosureReturnsNull(): void
    {
        $provider = ClosureInformationProvider::new(static fn (): ?TerminalInformation => null);

        self::assertNull($provider->for(Size::class));
    }

    public function testInvokesTheClosureOnEveryCall(): void
    {
        $calls = 0;
        $provider = ClosureInformationProvider::new(
            static function () use (&$calls): ?TerminalInformation {
                $calls++;
                return null;
            },
        );

        $provider->for(Size::class);
        $provider->for(Size::class);
        $provider->for(Size::class);

        self::assertSame(3, $calls);
    }
}

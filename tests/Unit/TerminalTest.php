<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\Action;
use PhpTui\Term\Action\PrintString;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Event;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\EventProvider;
use PhpTui\Term\InformationProvider;
use PhpTui\Term\Painter;
use PhpTui\Term\RawMode;
use PhpTui\Term\Terminal;
use PhpTui\Term\TerminalInformation;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;

final class TerminalTest extends TestCase
{
    public function testNewFactoryReturnsTerminalWithDefaults(): void
    {
        try {
            self::assertInstanceOf(Terminal::class, Terminal::new());
        } finally {
            // SyncTtyEventProvider::new() flips STDIN to non-blocking; restore it.
            stream_set_blocking(STDIN, true);
        }
    }

    public function testQueueIsFluentAndDoesNotPaintUntilFlush(): void
    {
        $painter = $this->fakePainter();
        $terminal = $this->terminal(painter: $painter);

        $result = $terminal->queue(new Reset());

        self::assertSame($terminal, $result);
        self::assertSame([], $painter->batches);
    }

    public function testQueueAcceptsMultipleActionsAndPreservesOrder(): void
    {
        $painter = $this->fakePainter();
        $terminal = $this->terminal(painter: $painter);
        $a = new PrintString('a');
        $b = new PrintString('b');
        $c = new Reset();

        $terminal->queue($a, $b);
        $terminal->queue($c);
        $terminal->flush();

        self::assertCount(1, $painter->batches);
        self::assertSame([$a, $b, $c], $painter->batches[0]);
    }

    public function testFlushPaintsQueuedActionsAndClearsQueue(): void
    {
        $painter = $this->fakePainter();
        $terminal = $this->terminal(painter: $painter);
        $action = new PrintString('hi');

        $terminal->queue($action);
        $result = $terminal->flush();

        self::assertSame($terminal, $result);
        self::assertSame([[$action]], $painter->batches);

        $terminal->flush();
        self::assertSame([[$action], []], $painter->batches);
    }

    public function testFlushWithEmptyQueuePaintsEmptyArray(): void
    {
        $painter = $this->fakePainter();
        $terminal = $this->terminal(painter: $painter);

        $terminal->flush();

        self::assertSame([[]], $painter->batches);
    }

    public function testExecutePaintsEachActionInItsOwnBatchAndBypassesQueue(): void
    {
        $painter = $this->fakePainter();
        $terminal = $this->terminal(painter: $painter);
        $a = new PrintString('a');
        $b = new Reset();

        $terminal->queue(new PrintString('queued'));
        $terminal->execute($a, $b);

        self::assertSame([[$a], [$b]], $painter->batches);

        $terminal->flush();
        self::assertCount(3, $painter->batches);
        self::assertEquals([new PrintString('queued')], $painter->batches[2]);
    }

    public function testExecuteWithNoActionsDoesNotPaint(): void
    {
        $painter = $this->fakePainter();
        $terminal = $this->terminal(painter: $painter);

        $terminal->execute();

        self::assertSame([], $painter->batches);
    }

    public function testInfoDelegatesToInformationProvider(): void
    {
        $size = new Size(80, 24);
        $infoProvider = new class($size) implements InformationProvider {
            /** @var array<class-string, ?TerminalInformation> */
            public array $requested = [];
            public function __construct(private readonly Size $size)
            {
            }
            public function for(string $classFqn): ?TerminalInformation
            {
                $this->requested[] = $classFqn;
                return $classFqn === Size::class ? $this->size : null;
            }
        };

        $terminal = $this->terminal(infoProvider: $infoProvider);

        self::assertSame($size, $terminal->info(Size::class));
        self::assertSame([Size::class], $infoProvider->requested);
    }

    public function testInfoReturnsNullWhenProviderHasNoMatch(): void
    {
        $terminal = $this->terminal(
            infoProvider: new class() implements InformationProvider {
                public function for(string $classFqn): ?TerminalInformation
                {
                    return null;
                }
            },
        );

        self::assertNull($terminal->info(Size::class));
    }

    public function testEventsReturnsTheConfiguredEventProvider(): void
    {
        $events = new class() implements EventProvider {
            public function next(): ?Event
            {
                return CharKeyEvent::new('z');
            }
        };

        self::assertSame($events, $this->terminal(eventProvider: $events)->events());
    }

    public function testEnableRawModeDelegatesToRawMode(): void
    {
        $rawMode = $this->fakeRawMode();
        $this->terminal(rawMode: $rawMode)->enableRawMode();

        self::assertSame(['enable'], $rawMode->calls);
    }

    public function testDisableRawModeDelegatesToRawMode(): void
    {
        $rawMode = $this->fakeRawMode();
        $this->terminal(rawMode: $rawMode)->disableRawMode();

        self::assertSame(['disable'], $rawMode->calls);
    }

    private function terminal(
        ?Painter $painter = null,
        ?InformationProvider $infoProvider = null,
        ?RawMode $rawMode = null,
        ?EventProvider $eventProvider = null,
    ): Terminal {
        return new Terminal(
            $painter ?? $this->fakePainter(),
            $infoProvider ?? new class() implements InformationProvider {
                public function for(string $classFqn): ?TerminalInformation
                {
                    return null;
                }
            },
            $rawMode ?? $this->fakeRawMode(),
            $eventProvider ?? new class() implements EventProvider {
                public function next(): ?Event
                {
                    return null;
                }
            },
        );
    }

    /**
     * @return Painter&object{batches: Action[][]}
     */
    private function fakePainter(): Painter
    {
        return new class() implements Painter {
            /** @var Action[][] */
            public array $batches = [];

            public function paint(array $actions): void
            {
                $this->batches[] = $actions;
            }
        };
    }

    /**
     * @return RawMode&object{calls: string[]}
     */
    private function fakeRawMode(): RawMode
    {
        return new class() implements RawMode {
            /** @var string[] */
            public array $calls = [];
            private bool $enabled = false;

            public function enable(): void
            {
                $this->calls[] = 'enable';
                $this->enabled = true;
            }

            public function disable(): void
            {
                $this->calls[] = 'disable';
                $this->enabled = false;
            }

            public function isEnabled(): bool
            {
                return $this->enabled;
            }
        };
    }
}

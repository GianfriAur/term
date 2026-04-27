<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Functional;

use PhpTui\Term\Action\Clear;
use PhpTui\Term\Action\MoveCursor;
use PhpTui\Term\Action\PrintString;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Action\SetBackgroundColor;
use PhpTui\Term\Action\SetForegroundColor;
use PhpTui\Term\Actions;
use PhpTui\Term\AnsiParser;
use PhpTui\Term\ClearType;
use PhpTui\Term\Colors;
use PhpTui\Term\EventProvider\ArrayEventProvider;
use PhpTui\Term\InformationProvider\AggregateInformationProvider;
use PhpTui\Term\Painter\AnsiPainter;
use PhpTui\Term\RawMode\TestRawMode;
use PhpTui\Term\Terminal;
use PhpTui\Term\Writer\StringWriter;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the full drawing pipeline: a user issues queued actions on a real
 * `Terminal`, the `AnsiPainter` renders them to a `StringWriter`, and the
 * captured byte stream is round-tripped through `AnsiParser` to confirm that
 * what reaches the terminal matches the user's intent.
 */
final class TerminalTest extends TestCase
{
    public function testQueueAndFlushProducesParseableAnsiStream(): void
    {
        $writer = StringWriter::new();
        $terminal = $this->terminal($writer);

        $terminal
            ->queue(
                Actions::clear(ClearType::All),
                Actions::moveCursor(0, 0),
                Actions::setForegroundColor(Colors::Red),
                Actions::setBackgroundColor(Colors::Blue),
                Actions::printString('Hello'),
                Actions::moveCursor(2, 5),
                Actions::printString('World'),
                Actions::reset(),
            )
            ->flush();

        self::assertEquals(
            [
                new Clear(ClearType::All),
                new MoveCursor(0, 0),
                new SetForegroundColor(Colors::Red),
                new SetBackgroundColor(Colors::Blue),
                new PrintString('Hello'),
                new MoveCursor(2, 5),
                new PrintString('World'),
                new Reset(),
            ],
            AnsiParser::parseString($writer->toString(), throw: true),
        );
    }

    public function testExecuteWritesEachActionImmediately(): void
    {
        $writer = StringWriter::new();
        $terminal = $this->terminal($writer);

        $terminal->execute(
            Actions::setForegroundColor(Colors::Green),
            Actions::printString('OK'),
            Actions::reset(),
        );

        self::assertEquals(
            [
                new SetForegroundColor(Colors::Green),
                new PrintString('OK'),
                new Reset(),
            ],
            AnsiParser::parseString($writer->toString(), throw: true),
        );
    }

    public function testQueueDoesNotEmitUntilFlush(): void
    {
        $writer = StringWriter::new();
        $terminal = $this->terminal($writer);

        $terminal->queue(Actions::printString('buffered'));

        self::assertSame('', $writer->toString());

        $terminal->flush();

        self::assertSame('buffered', $writer->toString());
    }

    public function testRawModeDelegationInIntegratedTerminal(): void
    {
        $rawMode = new TestRawMode();
        $terminal = $this->terminal(StringWriter::new(), $rawMode);

        self::assertFalse($rawMode->isEnabled());
        $terminal->enableRawMode();
        self::assertTrue($rawMode->isEnabled());
        $terminal->disableRawMode();
        self::assertFalse($rawMode->isEnabled());
    }

    private function terminal(StringWriter $writer, ?TestRawMode $rawMode = null): Terminal
    {
        return new Terminal(
            AnsiPainter::new($writer),
            AggregateInformationProvider::new([]),
            $rawMode ?? new TestRawMode(),
            new ArrayEventProvider([]),
        );
    }
}

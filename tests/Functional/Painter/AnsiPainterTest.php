<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Functional\Painter;

use PhpTui\Term\Action;
use PhpTui\Term\Action\Clear;
use PhpTui\Term\Action\CursorShow;
use PhpTui\Term\Action\EnableLineWrap;
use PhpTui\Term\Action\MoveCursor;
use PhpTui\Term\Action\MoveCursorDown;
use PhpTui\Term\Action\MoveCursorLeft;
use PhpTui\Term\Action\MoveCursorNextLine;
use PhpTui\Term\Action\MoveCursorPrevLine;
use PhpTui\Term\Action\MoveCursorRight;
use PhpTui\Term\Action\MoveCursorToColumn;
use PhpTui\Term\Action\MoveCursorToRow;
use PhpTui\Term\Action\MoveCursorUp;
use PhpTui\Term\Action\RequestCursorPosition;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Action\RestoreCursorPosition;
use PhpTui\Term\Action\SaveCursorPosition;
use PhpTui\Term\Action\ScrollDown;
use PhpTui\Term\Action\ScrollUp;
use PhpTui\Term\Action\SetCursorStyle;
use PhpTui\Term\Action\SetModifier;
use PhpTui\Term\Action\SetRgbBackgroundColor;
use PhpTui\Term\Action\SetRgbForegroundColor;
use PhpTui\Term\Action\SetTerminalTitle;
use PhpTui\Term\Actions;
use PhpTui\Term\AnsiParser;
use PhpTui\Term\Attribute;
use PhpTui\Term\ClearType;
use PhpTui\Term\Colors;
use PhpTui\Term\CursorStyle;
use PhpTui\Term\Painter\AnsiPainter;
use PhpTui\Term\Writer\StringWriter;
use PHPUnit\Framework\TestCase;

final class AnsiPainterTest extends TestCase
{
    /**
     * @dataProvider provideRoundtrippableActions
     */
    public function testActionRoundtripsThroughPainterAndParser(Action $action): void
    {
        $writer = StringWriter::new();
        AnsiPainter::new($writer)->paint([$action]);

        $parsed = AnsiParser::parseString($writer->toString(), throw: true);

        self::assertEquals([$action], $parsed);
    }

    public function testBatchOfActionsRoundtripsAsAGroup(): void
    {
        $batch = [
            Actions::clear(ClearType::All),
            Actions::moveCursor(0, 0),
            Actions::setForegroundColor(Colors::Red),
            Actions::printString('hello '),
            Actions::setBackgroundColor(Colors::Blue),
            Actions::printString('world'),
            Actions::reset(),
        ];

        $writer = StringWriter::new();
        AnsiPainter::new($writer)->paint($batch);

        self::assertEquals($batch, AnsiParser::parseString($writer->toString(), throw: true));
    }

    public function testAdjacentPrintStringsAreCompressedOnRoundtrip(): void
    {
        // The parser flattens consecutive PrintString actions back into one.
        // Pinned so refactors of either side have to revisit it.
        $writer = StringWriter::new();
        AnsiPainter::new($writer)->paint([
            Actions::printString('foo'),
            Actions::printString('bar'),
            Actions::printString('baz'),
        ]);

        self::assertEquals(
            [Actions::printString('foobarbaz')],
            AnsiParser::parseString($writer->toString(), throw: true),
        );
    }

    /**
     * @return iterable<string, array{Action}>
     */
    public static function provideRoundtrippableActions(): iterable
    {
        yield 'RequestCursorPosition' => [new RequestCursorPosition()];
        yield 'Reset' => [new Reset()];

        yield 'CursorShow on' => [new CursorShow(true)];
        yield 'CursorShow off' => [new CursorShow(false)];

        yield 'AlternateScreenEnable on' => [Actions::alternateScreenEnable()];
        yield 'AlternateScreenEnable off' => [Actions::alternateScreenDisable()];

        yield 'EnableLineWrap on' => [new EnableLineWrap(true)];
        yield 'EnableLineWrap off' => [new EnableLineWrap(false)];

        yield 'EnableCursorBlinking on' => [Actions::enableCusorBlinking()];
        yield 'EnableCursorBlinking off' => [Actions::disableCursorBlinking()];

        yield 'MoveCursor 2;3' => [new MoveCursor(2, 3)];
        yield 'MoveCursorUp 1' => [new MoveCursorUp(1)];
        yield 'MoveCursorDown 1' => [new MoveCursorDown(1)];
        yield 'MoveCursorRight 1' => [new MoveCursorRight(1)];
        yield 'MoveCursorLeft 1' => [new MoveCursorLeft(1)];
        yield 'MoveCursorNextLine 1' => [new MoveCursorNextLine(1)];
        yield 'MoveCursorNextLine 100' => [new MoveCursorNextLine(100)];
        yield 'MoveCursorPrevLine 1' => [new MoveCursorPrevLine(1)];
        yield 'MoveCursorToColumn 0' => [new MoveCursorToColumn(0)];
        yield 'MoveCursorToRow 0' => [new MoveCursorToRow(0)];

        yield 'ScrollUp 2' => [new ScrollUp(2)];
        yield 'ScrollDown 2' => [new ScrollDown(2)];

        yield 'Clear All' => [new Clear(ClearType::All)];
        yield 'Clear Purge' => [new Clear(ClearType::Purge)];
        yield 'Clear FromCursorDown' => [new Clear(ClearType::FromCursorDown)];
        yield 'Clear FromCursorUp' => [new Clear(ClearType::FromCursorUp)];
        yield 'Clear CurrentLine' => [new Clear(ClearType::CurrentLine)];
        yield 'Clear UntilNewLine' => [new Clear(ClearType::UntilNewLine)];

        yield 'SetRgbForegroundColor 2,3,4' => [new SetRgbForegroundColor(2, 3, 4)];
        yield 'SetRgbBackgroundColor 2,3,4' => [new SetRgbBackgroundColor(2, 3, 4)];

        yield 'SetForegroundColor Blue' => [Actions::setForegroundColor(Colors::Blue)];
        yield 'SetBackgroundColor Green' => [Actions::setBackgroundColor(Colors::Green)];

        yield 'Modifier Bold on' => [new SetModifier(Attribute::Bold, true)];
        yield 'Modifier Dim on' => [new SetModifier(Attribute::Dim, true)];
        yield 'Modifier Italic on' => [new SetModifier(Attribute::Italic, true)];
        yield 'Modifier Italic off' => [new SetModifier(Attribute::Italic, false)];
        yield 'Modifier Underline on' => [new SetModifier(Attribute::Underline, true)];
        yield 'Modifier Underline off' => [new SetModifier(Attribute::Underline, false)];
        yield 'Modifier SlowBlink on' => [new SetModifier(Attribute::SlowBlink, true)];
        yield 'Modifier Reverse on' => [new SetModifier(Attribute::Reverse, true)];
        yield 'Modifier Hidden on' => [new SetModifier(Attribute::Hidden, true)];
        yield 'Modifier Strike on' => [new SetModifier(Attribute::Strike, true)];

        yield 'SaveCursorPosition' => [new SaveCursorPosition()];
        yield 'RestoreCursorPosition' => [new RestoreCursorPosition()];

        yield 'CursorStyle DefaultUserShape' => [new SetCursorStyle(CursorStyle::DefaultUserShape)];
        yield 'CursorStyle BlinkingBlock' => [new SetCursorStyle(CursorStyle::BlinkingBlock)];
        yield 'CursorStyle SteadyBlock' => [new SetCursorStyle(CursorStyle::SteadyBlock)];
        yield 'CursorStyle BlinkingUnderScore' => [new SetCursorStyle(CursorStyle::BlinkingUnderScore)];
        yield 'CursorStyle SteadyUnderScore' => [new SetCursorStyle(CursorStyle::SteadyUnderScore)];
        yield 'CursorStyle BlinkingBar' => [new SetCursorStyle(CursorStyle::BlinkingBar)];
        yield 'CursorStyle SteadyBar' => [new SetCursorStyle(CursorStyle::SteadyBar)];

        yield 'SetTerminalTitle' => [new SetTerminalTitle('Hello')];
    }
}

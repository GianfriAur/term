<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\Action;
use PhpTui\Term\Action\EnableCursorBlinking;
use PhpTui\Term\Action\EnableLineWrap;
use PhpTui\Term\Action\MoveCursorDown;
use PhpTui\Term\Action\MoveCursorLeft;
use PhpTui\Term\Action\MoveCursorNextLine;
use PhpTui\Term\Action\MoveCursorPrevLine;
use PhpTui\Term\Action\MoveCursorRight;
use PhpTui\Term\Action\MoveCursorToColumn;
use PhpTui\Term\Action\MoveCursorToRow;
use PhpTui\Term\Action\MoveCursorUp;
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
use PhpTui\Term\Colors256;
use PhpTui\Term\CursorStyle;
use PhpTui\Term\ParseError;
use PHPUnit\Framework\TestCase;

final class AnsiParserTest extends TestCase
{
    public function testParseStringEmpty(): void
    {
        self::assertSame([], AnsiParser::parseString(''));
    }

    public function testParseStringPlainTextIsCompressedIntoSinglePrintString(): void
    {
        self::assertEquals(
            [Actions::printString('Hello World')],
            AnsiParser::parseString('Hello World'),
        );
    }

    public function testDrainEmitsAndClearsBuffer(): void
    {
        $parser = new AnsiParser();
        $parser->advance('hi', true);

        self::assertEquals([Actions::printString('hi')], $parser->drain());
        self::assertSame([], $parser->drain());
    }

    public function testDrainCompressesAdjacentPrintStringsAroundActions(): void
    {
        self::assertEquals(
            [
                Actions::reset(),
                Actions::printString('Hello World'),
                Actions::moveCursor(2, 3),
                Actions::printString('Good'),
            ],
            AnsiParser::parseString("\x1B[0mHello World\x1B[2;3HGood"),
        );
    }

    /**
     * @param Action[] $expected
     * @dataProvider provideSequence
     */
    public function testParsesSequence(string $sequence, array $expected): void
    {
        self::assertEquals($expected, AnsiParser::parseString($sequence));
    }

    /**
     * @return iterable<string, array{string, Action[]}>
     */
    public static function provideSequence(): iterable
    {
        // Bare CSI commands (no leading numbers).
        yield 'CSI J → FromCursorDown' => ["\x1B[J", [Actions::clear(ClearType::FromCursorDown)]];
        yield 'CSI K → UntilNewLine' => ["\x1B[K", [Actions::clear(ClearType::UntilNewLine)]];
        yield 'CSI S → ScrollUp default' => ["\x1B[S", [Actions::scrollUp()]];
        yield 'CSI T → ScrollDown default' => ["\x1B[T", [Actions::scrollDown()]];

        // Numbered clear / scroll.
        yield 'CSI 2J → All' => ["\x1B[2J", [Actions::clear(ClearType::All)]];
        yield 'CSI 3J → Purge' => ["\x1B[3J", [Actions::clear(ClearType::Purge)]];
        yield 'CSI 1J → FromCursorUp' => ["\x1B[1J", [Actions::clear(ClearType::FromCursorUp)]];
        yield 'CSI 2K → CurrentLine' => ["\x1B[2K", [Actions::clear(ClearType::CurrentLine)]];
        yield 'CSI 4S → ScrollUp(4)' => ["\x1B[4S", [new ScrollUp(4)]];
        yield 'CSI 7T → ScrollDown(7)' => ["\x1B[7T", [new ScrollDown(7)]];

        // Cursor movement (G/d are 0-based, parser subtracts 1 from the SGR coord).
        yield 'CSI 5A → up' => ["\x1B[5A", [new MoveCursorUp(5)]];
        yield 'CSI 5B → down' => ["\x1B[5B", [new MoveCursorDown(5)]];
        yield 'CSI 5C → right' => ["\x1B[5C", [new MoveCursorRight(5)]];
        yield 'CSI 5D → left' => ["\x1B[5D", [new MoveCursorLeft(5)]];
        yield 'CSI 100E → next line' => ["\x1B[100E", [new MoveCursorNextLine(100)]];
        yield 'CSI 1F → prev line' => ["\x1B[1F", [new MoveCursorPrevLine(1)]];
        yield 'CSI 1G → ToColumn 0' => ["\x1B[1G", [new MoveCursorToColumn(0)]];
        yield 'CSI 1d → ToRow 0' => ["\x1B[1d", [new MoveCursorToRow(0)]];
        yield 'CSI 4G → ToColumn 3' => ["\x1B[4G", [new MoveCursorToColumn(3)]];
        yield 'CSI 4d → ToRow 3' => ["\x1B[4d", [new MoveCursorToRow(3)]];

        // Move cursor (1-arg H form delegates to parseCursorPosition; we exercise the 2-arg form).
        yield 'CSI 7;11H → moveCursor' => ["\x1B[7;11H", [Actions::moveCursor(7, 11)]];

        // Request cursor position.
        yield 'CSI 6n → request cursor pos' => ["\x1B[6n", [Actions::requestCursorPosition()]];

        // Cursor style 0-6 q.
        yield 'CSI 0 q → DefaultUserShape' => ["\x1B[0 q", [new SetCursorStyle(CursorStyle::DefaultUserShape)]];
        yield 'CSI 1 q → BlinkingBlock' => ["\x1B[1 q", [new SetCursorStyle(CursorStyle::BlinkingBlock)]];
        yield 'CSI 2 q → SteadyBlock' => ["\x1B[2 q", [new SetCursorStyle(CursorStyle::SteadyBlock)]];
        yield 'CSI 3 q → BlinkingUnderScore' => ["\x1B[3 q", [new SetCursorStyle(CursorStyle::BlinkingUnderScore)]];
        yield 'CSI 4 q → SteadyUnderScore' => ["\x1B[4 q", [new SetCursorStyle(CursorStyle::SteadyUnderScore)]];
        yield 'CSI 5 q → BlinkingBar' => ["\x1B[5 q", [new SetCursorStyle(CursorStyle::BlinkingBar)]];
        yield 'CSI 6 q → SteadyBar' => ["\x1B[6 q", [new SetCursorStyle(CursorStyle::SteadyBar)]];

        // Private modes.
        yield 'CSI ?25h → cursor show' => ["\x1B[?25h", [Actions::cursorShow()]];
        yield 'CSI ?25l → cursor hide' => ["\x1B[?25l", [Actions::cursorHide()]];
        yield 'CSI ?12h → blink on' => ["\x1B[?12h", [new EnableCursorBlinking(true)]];
        yield 'CSI ?12l → blink off' => ["\x1B[?12l", [new EnableCursorBlinking(false)]];
        yield 'CSI ?7h → line wrap on' => ["\x1B[?7h", [new EnableLineWrap(true)]];
        yield 'CSI ?7l → line wrap off' => ["\x1B[?7l", [new EnableLineWrap(false)]];
        yield 'CSI ?1049h → alt screen on' => ["\x1B[?1049h", [Actions::alternateScreenEnable()]];
        yield 'CSI ?1049l → alt screen off' => ["\x1B[?1049l", [Actions::alternateScreenDisable()]];

        // ESC raw save/restore cursor.
        yield 'ESC 7 → save cursor' => ["\x1B7", [new SaveCursorPosition()]];
        yield 'ESC 8 → restore cursor' => ["\x1B8", [new RestoreCursorPosition()]];

        // OSC set title (ends with BEL).
        yield 'OSC 0;Title BEL → set title' => ["\x1B]0;Hello\x07", [new SetTerminalTitle('Hello')]];
        yield 'OSC 0;empty BEL → empty title' => ["\x1B]0;\x07", [new SetTerminalTitle('')]];

        // Reset SGR.
        yield 'SGR 0m → Reset' => ["\x1B[0m", [new Reset()]];

        // Color reset SGR.
        yield 'SGR 39m → fg reset' => ["\x1B[39m", [Actions::setForegroundColor(Colors::Reset)]];
        yield 'SGR 49m → bg reset' => ["\x1B[49m", [Actions::setBackgroundColor(Colors::Reset)]];

        // RGB true colors (5 parts: 38/48 ; 2 ; r ; g ; b).
        yield 'SGR 38;2 → rgb fg' => ["\x1B[38;2;10;20;30m", [new SetRgbForegroundColor(10, 20, 30)]];
        yield 'SGR 48;2 → rgb bg' => ["\x1B[48;2;10;20;30m", [new SetRgbBackgroundColor(10, 20, 30)]];

        // 256-color palette: parser normalises to RGB via Colors256::indexToRgb.
        yield 'SGR 38;5;4 → 256 fg' => [
            "\x1B[38;5;4m",
            [new SetRgbForegroundColor(...Colors256::indexToRgb(4))],
        ];
        yield 'SGR 48;5;2 → 256 bg' => [
            "\x1B[48;5;2m",
            [new SetRgbBackgroundColor(...Colors256::indexToRgb(2))],
        ];
    }

    /**
     * @dataProvider provideForegroundColors
     */
    public function testForegroundColorMapping(int $code, Colors $expected): void
    {
        self::assertEquals(
            [Actions::setForegroundColor($expected)],
            AnsiParser::parseString(sprintf("\x1B[%dm", $code)),
        );
    }

    /**
     * @return iterable<string, array{int, Colors}>
     */
    public static function provideForegroundColors(): iterable
    {
        yield 'Black' => [30, Colors::Black];
        yield 'Red' => [31, Colors::Red];
        yield 'Green' => [32, Colors::Green];
        yield 'Yellow' => [33, Colors::Yellow];
        yield 'Blue' => [34, Colors::Blue];
        yield 'Magenta' => [35, Colors::Magenta];
        yield 'Cyan' => [36, Colors::Cyan];
        yield 'Gray' => [37, Colors::Gray];
        yield 'DarkGray' => [90, Colors::DarkGray];
        yield 'LightRed' => [91, Colors::LightRed];
        yield 'LightGreen' => [92, Colors::LightGreen];
        yield 'LightYellow' => [93, Colors::LightYellow];
        yield 'LightBlue' => [94, Colors::LightBlue];
        yield 'LightMagenta' => [95, Colors::LightMagenta];
        yield 'LightCyan' => [96, Colors::LightCyan];
        yield 'White' => [97, Colors::White];
    }

    /**
     * @dataProvider provideBackgroundColors
     */
    public function testBackgroundColorMapping(int $code, Colors $expected): void
    {
        self::assertEquals(
            [Actions::setBackgroundColor($expected)],
            AnsiParser::parseString(sprintf("\x1B[%dm", $code)),
        );
    }

    /**
     * @return iterable<string, array{int, Colors}>
     */
    public static function provideBackgroundColors(): iterable
    {
        yield 'Black' => [40, Colors::Black];
        yield 'Red' => [41, Colors::Red];
        yield 'Yellow' => [43, Colors::Yellow];
        yield 'White' => [47, Colors::Gray];
        // 100..107 take the "starts with 10" branch which routes to setBackgroundColor.
        yield 'DarkGray (100)' => [100, Colors::DarkGray];
        yield 'LightRed (101)' => [101, Colors::LightRed];
        yield 'White (107)' => [107, Colors::White];
    }

    /**
     * @dataProvider provideModifierOn
     */
    public function testModifierOnMapping(int $code, Attribute $attribute): void
    {
        self::assertEquals(
            [new SetModifier($attribute, true)],
            AnsiParser::parseString(sprintf("\x1B[%dm", $code)),
        );
    }

    /**
     * @return iterable<string, array{int, Attribute}>
     */
    public static function provideModifierOn(): iterable
    {
        yield 'Bold' => [1, Attribute::Bold];
        yield 'Dim' => [2, Attribute::Dim];
        yield 'Italic' => [3, Attribute::Italic];
        yield 'Underline' => [4, Attribute::Underline];
        yield 'SlowBlink' => [5, Attribute::SlowBlink];
        yield 'Reverse' => [7, Attribute::Reverse];
        yield 'Hidden' => [8, Attribute::Hidden];
        yield 'Strike' => [9, Attribute::Strike];
    }

    /**
     * @dataProvider provideModifierOff
     */
    public function testModifierOffMapping(int $code, Attribute $attribute): void
    {
        self::assertEquals(
            [new SetModifier($attribute, false)],
            AnsiParser::parseString(sprintf("\x1B[%dm", $code)),
        );
    }

    /**
     * @return iterable<string, array{int, Attribute}>
     */
    public static function provideModifierOff(): iterable
    {
        yield 'Bold (22)' => [22, Attribute::Bold];
        yield 'Italic (23)' => [23, Attribute::Italic];
        yield 'Underline (24)' => [24, Attribute::Underline];
        yield 'SlowBlink (25)' => [25, Attribute::SlowBlink];
        yield 'Reverse (27)' => [27, Attribute::Reverse];
        yield 'Hidden (28)' => [28, Attribute::Hidden];
        yield 'Strike (29)' => [29, Attribute::Strike];
    }

    public function testIncompleteEscIsBufferedAcrossAdvanceCalls(): void
    {
        $parser = new AnsiParser();

        foreach (str_split("\x1B[5A") as $byte) {
            $parser->advance($byte, true);
        }

        self::assertEquals([new MoveCursorUp(5)], $parser->drain());
    }

    public function testIncompleteEscRemainsInBufferAndDoesNotEmit(): void
    {
        $parser = new AnsiParser();
        $parser->advance("\x1B", true);
        $parser->advance('[', true);

        self::assertSame([], $parser->drain());
    }

    public function testUnknownEscPrefixEmitsLiteralEscAndDropsTrailingByte(): void
    {
        self::assertEquals(
            [Actions::printString("\x1B")],
            AnsiParser::parseString("\x1BX"),
        );
    }

    public function testThrowFalseRecoversByDroppingMalformedSequence(): void
    {
        self::assertEquals(
            [Actions::printString('after')],
            AnsiParser::parseString("\x1B[Zafter"),
        );
    }

    public function testThrowTrueRaisesOnMalformedCsi(): void
    {
        $this->expectException(ParseError::class);

        AnsiParser::parseString("\x1B[Z", true);
    }

    public function testThrowTrueRaisesOnUnknownPrivateMode(): void
    {
        $this->expectException(ParseError::class);

        AnsiParser::parseString("\x1B[?9h", true);
    }

    public function testThrowTrueRaisesOnMalformedOsc(): void
    {
        $this->expectException(ParseError::class);

        AnsiParser::parseString("\x1B]9;Hello\x07", true);
    }

    public function testRoundtripsThroughParseStringPreservesPlainText(): void
    {
        self::assertEquals(
            [Actions::printString('abc')],
            AnsiParser::parseString('abc'),
        );
    }

    public function testRgbValuesAreClampedTo0_255(): void
    {
        self::assertEquals(
            [new SetRgbForegroundColor(0, 100, 255)],
            AnsiParser::parseString("\x1B[38;2;-5;100;999m"),
        );
    }

    public function testCursorPositionRequiresExactlyTwoCoordinates(): void
    {
        $this->expectException(ParseError::class);

        AnsiParser::parseString("\x1B[5H", true);
    }
}

<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Painter;

use PhpTui\Term\Action;
use PhpTui\Term\Action\AlternateScreenEnable;
use PhpTui\Term\Action\Clear;
use PhpTui\Term\Action\CursorShow;
use PhpTui\Term\Action\EnableCursorBlinking;
use PhpTui\Term\Action\EnableLineWrap;
use PhpTui\Term\Action\EnableMouseCapture;
use PhpTui\Term\Action\MoveCursor;
use PhpTui\Term\Action\MoveCursorDown;
use PhpTui\Term\Action\MoveCursorLeft;
use PhpTui\Term\Action\MoveCursorNextLine;
use PhpTui\Term\Action\MoveCursorPrevLine;
use PhpTui\Term\Action\MoveCursorRight;
use PhpTui\Term\Action\MoveCursorToColumn;
use PhpTui\Term\Action\MoveCursorToRow;
use PhpTui\Term\Action\MoveCursorUp;
use PhpTui\Term\Action\PrintString;
use PhpTui\Term\Action\RequestCursorPosition;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Action\RestoreCursorPosition;
use PhpTui\Term\Action\SaveCursorPosition;
use PhpTui\Term\Action\ScrollDown;
use PhpTui\Term\Action\ScrollUp;
use PhpTui\Term\Action\SetBackgroundColor;
use PhpTui\Term\Action\SetCursorStyle;
use PhpTui\Term\Action\SetForegroundColor;
use PhpTui\Term\Action\SetModifier;
use PhpTui\Term\Action\SetRgbBackgroundColor;
use PhpTui\Term\Action\SetRgbForegroundColor;
use PhpTui\Term\Action\SetTerminalTitle;
use PhpTui\Term\Attribute;
use PhpTui\Term\ClearType;
use PhpTui\Term\Colors;
use PhpTui\Term\CursorStyle;
use PhpTui\Term\Painter;
use PhpTui\Term\Painter\AnsiPainter;
use PhpTui\Term\Writer\StringWriter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AnsiPainterTest extends TestCase
{
    public function testImplementsPainterInterface(): void
    {
        self::assertInstanceOf(Painter::class, AnsiPainter::new(StringWriter::new()));
    }

    public function testPrintStringIsWrittenRawWithNoCsi(): void
    {
        self::assertSame('hello world', $this->paint(new PrintString('hello world')));
    }

    public function testPaintEmptyActionsDoesNotWrite(): void
    {
        $writer = StringWriter::new();
        AnsiPainter::new($writer)->paint([]);

        self::assertSame('', $writer->toString());
    }

    public function testPaintBatchesEveryActionInOrder(): void
    {
        self::assertSame(
            "hi\x1B[0m",
            $this->paint(new PrintString('hi'), new Reset()),
        );
    }

    /**
     * @dataProvider provideCsiActions
     */
    public function testCsiActions(string $expectedCsi, Action $action): void
    {
        self::assertSame("\x1B[" . $expectedCsi, $this->paint($action));
    }

    /**
     * @return iterable<string, array{string, Action}>
     */
    public static function provideCsiActions(): iterable
    {
        yield 'RequestCursorPosition' => ['6n', new RequestCursorPosition()];
        yield 'Reset' => ['0m', new Reset()];

        yield 'CursorShow true' => ['?25h', new CursorShow(true)];
        yield 'CursorShow false' => ['?25l', new CursorShow(false)];

        yield 'AlternateScreenEnable true' => ['?1049h', new AlternateScreenEnable(true)];
        yield 'AlternateScreenEnable false' => ['?1049l', new AlternateScreenEnable(false)];

        yield 'EnableLineWrap true' => ['?7h', new EnableLineWrap(true)];
        yield 'EnableLineWrap false' => ['?7l', new EnableLineWrap(false)];

        yield 'EnableCursorBlinking true' => ['?12h', new EnableCursorBlinking(true)];
        yield 'EnableCursorBlinking false' => ['?12l', new EnableCursorBlinking(false)];

        yield 'MoveCursor 2;3' => ['2;3H', new MoveCursor(2, 3)];
        yield 'MoveCursorUp 5' => ['5A', new MoveCursorUp(5)];
        yield 'MoveCursorDown 5' => ['5B', new MoveCursorDown(5)];
        yield 'MoveCursorRight 5' => ['5C', new MoveCursorRight(5)];
        yield 'MoveCursorLeft 5' => ['5D', new MoveCursorLeft(5)];
        yield 'MoveCursorNextLine 100' => ['100E', new MoveCursorNextLine(100)];
        yield 'MoveCursorPrevLine 1' => ['1F', new MoveCursorPrevLine(1)];
        // Both ToColumn and ToRow add 1 to the supplied 0-based coordinate.
        yield 'MoveCursorToColumn 0' => ['1G', new MoveCursorToColumn(0)];
        yield 'MoveCursorToRow 0' => ['1d', new MoveCursorToRow(0)];

        yield 'ScrollUp 2' => ['2S', new ScrollUp(2)];
        yield 'ScrollDown 2' => ['2T', new ScrollDown(2)];

        yield 'Clear All' => ['2J', new Clear(ClearType::All)];
        yield 'Clear Purge' => ['3J', new Clear(ClearType::Purge)];
        yield 'Clear FromCursorDown' => ['J', new Clear(ClearType::FromCursorDown)];
        yield 'Clear FromCursorUp' => ['1J', new Clear(ClearType::FromCursorUp)];
        yield 'Clear CurrentLine' => ['2K', new Clear(ClearType::CurrentLine)];
        yield 'Clear UntilNewLine' => ['K', new Clear(ClearType::UntilNewLine)];

        yield 'SetRgbForeground' => ['38;2;10;20;30m', new SetRgbForegroundColor(10, 20, 30)];
        yield 'SetRgbBackground' => ['48;2;10;20;30m', new SetRgbBackgroundColor(10, 20, 30)];
    }

    /**
     * Foreground color mapping: standard 16 colors map to SGR 30..37 / 90..97.
     *
     * @dataProvider provideForegroundColors
     */
    public function testForegroundColorMapping(int $expectedCode, Colors $color): void
    {
        self::assertSame(sprintf("\x1B[%dm", $expectedCode), $this->paint(new SetForegroundColor($color)));
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
     * Background colors map to foreground codes + 10.
     *
     * @dataProvider provideBackgroundColors
     */
    public function testBackgroundColorMapping(int $expectedCode, Colors $color): void
    {
        self::assertSame(sprintf("\x1B[%dm", $expectedCode), $this->paint(new SetBackgroundColor($color)));
    }

    /**
     * @return iterable<string, array{int, Colors}>
     */
    public static function provideBackgroundColors(): iterable
    {
        yield 'Black' => [40, Colors::Black];
        yield 'Red' => [41, Colors::Red];
        yield 'Green' => [42, Colors::Green];
        yield 'Yellow' => [43, Colors::Yellow];
        yield 'Blue' => [44, Colors::Blue];
        yield 'Magenta' => [45, Colors::Magenta];
        yield 'Cyan' => [46, Colors::Cyan];
        yield 'Gray' => [47, Colors::Gray];
        yield 'DarkGray' => [100, Colors::DarkGray];
        yield 'LightRed' => [101, Colors::LightRed];
        yield 'White' => [107, Colors::White];
    }

    public function testForegroundColorResetUsesDefaultCode(): void
    {
        self::assertSame("\x1B[39m", $this->paint(new SetForegroundColor(Colors::Reset)));
    }

    public function testBackgroundColorResetUsesDefaultCode(): void
    {
        self::assertSame("\x1B[49m", $this->paint(new SetBackgroundColor(Colors::Reset)));
    }

    /**
     * @dataProvider provideModifierOnIndex
     */
    public function testSetModifierEnable(int $expectedCode, Attribute $attribute): void
    {
        self::assertSame(
            sprintf("\x1B[%dm", $expectedCode),
            $this->paint(new SetModifier($attribute, true)),
        );
    }

    /**
     * @return iterable<string, array{int, Attribute}>
     */
    public static function provideModifierOnIndex(): iterable
    {
        yield 'Reset' => [0, Attribute::Reset];
        yield 'Bold' => [1, Attribute::Bold];
        yield 'Dim' => [2, Attribute::Dim];
        yield 'Italic' => [3, Attribute::Italic];
        yield 'Underline' => [4, Attribute::Underline];
        yield 'SlowBlink' => [5, Attribute::SlowBlink];
        yield 'RapidBlink' => [6, Attribute::RapidBlink];
        yield 'Reverse' => [7, Attribute::Reverse];
        yield 'Hidden' => [8, Attribute::Hidden];
        yield 'Strike' => [9, Attribute::Strike];
    }

    /**
     * @dataProvider provideModifierOffIndex
     */
    public function testSetModifierDisable(int $expectedCode, Attribute $attribute): void
    {
        self::assertSame(
            sprintf("\x1B[%dm", $expectedCode),
            $this->paint(new SetModifier($attribute, false)),
        );
    }

    /**
     * @return iterable<string, array{int, Attribute}>
     */
    public static function provideModifierOffIndex(): iterable
    {
        yield 'Reset' => [0, Attribute::Reset];
        yield 'Bold' => [22, Attribute::Bold];
        // Dim shares the disable code with Bold.
        yield 'Dim' => [22, Attribute::Dim];
        yield 'Italic' => [23, Attribute::Italic];
        yield 'Underline' => [24, Attribute::Underline];
        yield 'SlowBlink' => [25, Attribute::SlowBlink];
        // RapidBlink shares the disable code with SlowBlink.
        yield 'RapidBlink' => [25, Attribute::RapidBlink];
        yield 'Reverse' => [27, Attribute::Reverse];
        yield 'Hidden' => [28, Attribute::Hidden];
        yield 'Strike' => [29, Attribute::Strike];
    }

    /**
     * @dataProvider provideCursorStyle
     */
    public function testSetCursorStyle(int $expectedCode, CursorStyle $style): void
    {
        self::assertSame(
            sprintf("\x1B[%d q", $expectedCode),
            $this->paint(new SetCursorStyle($style)),
        );
    }

    /**
     * @return iterable<string, array{int, CursorStyle}>
     */
    public static function provideCursorStyle(): iterable
    {
        yield 'DefaultUserShape' => [0, CursorStyle::DefaultUserShape];
        yield 'BlinkingBlock' => [1, CursorStyle::BlinkingBlock];
        yield 'SteadyBlock' => [2, CursorStyle::SteadyBlock];
        yield 'BlinkingUnderScore' => [3, CursorStyle::BlinkingUnderScore];
        yield 'SteadyUnderScore' => [4, CursorStyle::SteadyUnderScore];
        yield 'BlinkingBar' => [5, CursorStyle::BlinkingBar];
        yield 'SteadyBar' => [6, CursorStyle::SteadyBar];
    }

    public function testSaveAndRestoreCursorPositionUseRawEsc(): void
    {
        self::assertSame("\x1B7", $this->paint(new SaveCursorPosition()));
        self::assertSame("\x1B8", $this->paint(new RestoreCursorPosition()));
    }

    public function testSetTerminalTitleEmitsOscSequence(): void
    {
        self::assertSame(
            "\x1B]0;Hello\x07",
            $this->paint(new SetTerminalTitle('Hello')),
        );
    }

    public function testEnableMouseCaptureEmitsCompoundCsiSequence(): void
    {
        self::assertSame(
            "\x1B[?1000h\x1B[?1002h\x1B[?1003h\x1B[?1015h\x1B[?1006h",
            $this->paint(new EnableMouseCapture(true)),
        );
    }

    public function testDisableMouseCaptureReversesTheCompoundSequence(): void
    {
        self::assertSame(
            "\x1B[?1006l\x1B[?1015l\x1B[?1003l\x1B[?1002l\x1B[?1000l",
            $this->paint(new EnableMouseCapture(false)),
        );
    }

    public function testThrowsOnUnknownAction(): void
    {
        $unknown = new class() implements Action {
            public function __toString(): string
            {
                return 'Unknown';
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Do not know how to handle action/');

        AnsiPainter::new(StringWriter::new())->paint([$unknown]);
    }

    private function paint(Action ...$actions): string
    {
        $writer = StringWriter::new();
        AnsiPainter::new($writer)->paint($actions);

        return $writer->toString();
    }
}

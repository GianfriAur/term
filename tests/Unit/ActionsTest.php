<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

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
use PhpTui\Term\Actions;
use PhpTui\Term\Attribute;
use PhpTui\Term\ClearType;
use PhpTui\Term\Colors;
use PhpTui\Term\CursorStyle;
use PHPUnit\Framework\TestCase;

final class ActionsTest extends TestCase
{
    public function testRequestCursorPosition(): void
    {
        self::assertInstanceOf(RequestCursorPosition::class, Actions::requestCursorPosition());
    }

    public function testAlternateScreenEnable(): void
    {
        $action = Actions::alternateScreenEnable();

        self::assertInstanceOf(AlternateScreenEnable::class, $action);
        self::assertTrue($action->enable);
    }

    public function testAlternateScreenDisable(): void
    {
        $action = Actions::alternateScreenDisable();

        self::assertInstanceOf(AlternateScreenEnable::class, $action);
        self::assertFalse($action->enable);
    }

    public function testPrintString(): void
    {
        $action = Actions::printString('hello world');

        self::assertInstanceOf(PrintString::class, $action);
        self::assertSame('hello world', $action->string);
    }

    public function testCursorShow(): void
    {
        $action = Actions::cursorShow();

        self::assertInstanceOf(CursorShow::class, $action);
        self::assertTrue($action->show);
    }

    public function testCursorHide(): void
    {
        $action = Actions::cursorHide();

        self::assertInstanceOf(CursorShow::class, $action);
        self::assertFalse($action->show);
    }

    public function testSetRgbForegroundColor(): void
    {
        $action = Actions::setRgbForegroundColor(10, 20, 30);

        self::assertInstanceOf(SetRgbForegroundColor::class, $action);
        self::assertSame(10, $action->r);
        self::assertSame(20, $action->g);
        self::assertSame(30, $action->b);
    }

    public function testSetRgbBackgroundColor(): void
    {
        $action = Actions::setRgbBackgroundColor(40, 50, 60);

        self::assertInstanceOf(SetRgbBackgroundColor::class, $action);
        self::assertSame(40, $action->r);
        self::assertSame(50, $action->g);
        self::assertSame(60, $action->b);
    }

    public function testSetForegroundColor(): void
    {
        $action = Actions::setForegroundColor(Colors::Red);

        self::assertInstanceOf(SetForegroundColor::class, $action);
        self::assertSame(Colors::Red, $action->color);
    }

    public function testSetBackgroundColor(): void
    {
        $action = Actions::setBackgroundColor(Colors::Blue);

        self::assertInstanceOf(SetBackgroundColor::class, $action);
        self::assertSame(Colors::Blue, $action->color);
    }

    public function testMoveCursor(): void
    {
        $action = Actions::moveCursor(7, 11);

        self::assertInstanceOf(MoveCursor::class, $action);
        self::assertSame(7, $action->line);
        self::assertSame(11, $action->col);
    }

    public function testReset(): void
    {
        self::assertInstanceOf(Reset::class, Actions::reset());
    }

    /**
     * @dataProvider provideModifierFactories
     */
    public function testModifierFactory(string $method, Attribute $expected): void
    {
        $on = Actions::$method(true);
        $off = Actions::$method(false);

        self::assertInstanceOf(SetModifier::class, $on);
        self::assertSame($expected, $on->modifier);
        self::assertTrue($on->enable);

        self::assertInstanceOf(SetModifier::class, $off);
        self::assertSame($expected, $off->modifier);
        self::assertFalse($off->enable);
    }

    /**
     * @return iterable<string, array{string, Attribute}>
     */
    public static function provideModifierFactories(): iterable
    {
        yield 'bold' => ['bold', Attribute::Bold];
        yield 'dim' => ['dim', Attribute::Dim];
        yield 'italic' => ['italic', Attribute::Italic];
        yield 'underline' => ['underline', Attribute::Underline];
        yield 'slowBlink' => ['slowBlink', Attribute::SlowBlink];
        yield 'rapidBlink' => ['rapidBlink', Attribute::RapidBlink];
        yield 'reverse' => ['reverse', Attribute::Reverse];
        yield 'hidden' => ['hidden', Attribute::Hidden];
        yield 'strike' => ['strike', Attribute::Strike];
    }

    public function testClear(): void
    {
        $action = Actions::clear(ClearType::All);

        self::assertInstanceOf(Clear::class, $action);
        self::assertSame(ClearType::All, $action->clearType);
    }

    public function testEnableMouseCapture(): void
    {
        $action = Actions::enableMouseCapture();

        self::assertInstanceOf(EnableMouseCapture::class, $action);
        self::assertTrue($action->enable);
    }

    public function testDisableMouseCapture(): void
    {
        $action = Actions::disableMouseCapture();

        self::assertInstanceOf(EnableMouseCapture::class, $action);
        self::assertFalse($action->enable);
    }

    public function testScrollUpUsesDefaultRows(): void
    {
        $action = Actions::scrollUp();

        self::assertInstanceOf(ScrollUp::class, $action);
        self::assertSame(1, $action->rows);
    }

    public function testScrollUpAcceptsCustomRows(): void
    {
        self::assertSame(5, Actions::scrollUp(5)->rows);
    }

    public function testScrollDownUsesDefaultRows(): void
    {
        $action = Actions::scrollDown();

        self::assertInstanceOf(ScrollDown::class, $action);
        self::assertSame(1, $action->rows);
    }

    public function testScrollDownAcceptsCustomRows(): void
    {
        self::assertSame(9, Actions::scrollDown(9)->rows);
    }

    public function testSetTitle(): void
    {
        $action = Actions::setTitle('My Term');

        self::assertInstanceOf(SetTerminalTitle::class, $action);
        self::assertSame('My Term', $action->title);
    }

    public function testLineWrapEnable(): void
    {
        $action = Actions::lineWrap(true);

        self::assertInstanceOf(EnableLineWrap::class, $action);
        self::assertTrue($action->enable);
    }

    public function testLineWrapDisable(): void
    {
        self::assertFalse(Actions::lineWrap(false)->enable);
    }

    public function testMoveCursorNextLineUsesDefault(): void
    {
        $action = Actions::moveCursorNextLine();

        self::assertInstanceOf(MoveCursorNextLine::class, $action);
        self::assertSame(1, $action->nbLines);
    }

    public function testMoveCursorNextLineAcceptsCount(): void
    {
        self::assertSame(4, Actions::moveCursorNextLine(4)->nbLines);
    }

    public function testMoveCursorPreviousLineUsesDefault(): void
    {
        $action = Actions::moveCursorPreviousLine();

        self::assertInstanceOf(MoveCursorPrevLine::class, $action);
        self::assertSame(1, $action->nbLines);
    }

    public function testMoveCursorPreviousLineAcceptsCount(): void
    {
        self::assertSame(6, Actions::moveCursorPreviousLine(6)->nbLines);
    }

    public function testMoveCursorToColumn(): void
    {
        $action = Actions::moveCursorToColumn(12);

        self::assertInstanceOf(MoveCursorToColumn::class, $action);
        self::assertSame(12, $action->col);
    }

    public function testMoveCursorToRow(): void
    {
        $action = Actions::moveCursorToRow(3);

        self::assertInstanceOf(MoveCursorToRow::class, $action);
        self::assertSame(3, $action->row);
    }

    public function testMoveCursorUp(): void
    {
        $action = Actions::moveCursorUp(2);

        self::assertInstanceOf(MoveCursorUp::class, $action);
        self::assertSame(2, $action->lines);
    }

    public function testMoveCursorUpUsesDefault(): void
    {
        self::assertSame(1, Actions::moveCursorUp()->lines);
    }

    public function testMoveCursorRight(): void
    {
        $action = Actions::moveCursorRight(8);

        self::assertInstanceOf(MoveCursorRight::class, $action);
        self::assertSame(8, $action->cols);
    }

    public function testMoveCursorDown(): void
    {
        $action = Actions::moveCursorDown(15);

        self::assertInstanceOf(MoveCursorDown::class, $action);
        self::assertSame(15, $action->lines);
    }

    public function testMoveCursorLeft(): void
    {
        $action = Actions::moveCursorLeft(2);

        self::assertInstanceOf(MoveCursorLeft::class, $action);
        self::assertSame(2, $action->cols);
    }

    public function testSaveCursorPosition(): void
    {
        self::assertInstanceOf(SaveCursorPosition::class, Actions::saveCursorPosition());
    }

    public function testRestoreCursorPosition(): void
    {
        self::assertInstanceOf(RestoreCursorPosition::class, Actions::restoreCursorPosition());
    }

    public function testEnableCursorBlinking(): void
    {
        $action = Actions::enableCusorBlinking();

        self::assertInstanceOf(EnableCursorBlinking::class, $action);
        self::assertTrue($action->enable);
    }

    public function testDisableCursorBlinking(): void
    {
        $action = Actions::disableCursorBlinking();

        self::assertInstanceOf(EnableCursorBlinking::class, $action);
        self::assertFalse($action->enable);
    }

    public function testSetCursorStyle(): void
    {
        $action = Actions::setCursorStyle(CursorStyle::BlinkingBar);

        self::assertInstanceOf(SetCursorStyle::class, $action);
        self::assertSame(CursorStyle::BlinkingBar, $action->cursorStyle);
    }
}

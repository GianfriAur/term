<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use Generator;
use PhpTui\Term\Event;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\CursorPositionEvent;
use PhpTui\Term\Event\FocusEvent;
use PhpTui\Term\Event\FunctionKeyEvent;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\EventParser;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyEventKind;
use PhpTui\Term\KeyModifiers;
use PhpTui\Term\MouseButton;
use PhpTui\Term\MouseEventKind;
use PHPUnit\Framework\TestCase;

final class EventParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     * @dataProvider provideCsiSpecialKeyCode
     * @dataProvider provideCsiModifierKeyCode
     * @dataProvider provideCsiMouse
     * @dataProvider provideExtraCsiTilde
     * @dataProvider provideExtraMouse
     * @dataProvider provideExtraModifiers
     */
    public function testParse(string $line, ?Event $expected, bool $moreInput = false): void
    {
        $parser = new EventParser();
        $parser->advance($line, $moreInput);
        $events = $parser->drain();
        if (null === $expected) {
            self::assertCount(0, $events);

            return;
        }
        self::assertCount(1, $events);
        self::assertEquals($expected, $events[0]);
    }

    /**
     * @dataProvider provideRecoverableGarbage
     */
    public function testParserRecoversFromMalformedInputAndContinues(string $garbage): void
    {
        $parser = EventParser::new();
        // Malformed bytes followed by a clean ASCII char on the next byte.
        $parser->advance($garbage . 'z', false);

        $events = $parser->drain();

        self::assertCount(1, $events);
        self::assertEquals(CharKeyEvent::new('z'), $events[0]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRecoverableGarbage(): iterable
    {
        yield 'unknown CSI final byte' => ["\x1B[X"];
        yield 'unknown CSI tilde number' => ["\x1B[10~"];
        yield 'invalid UTF-8 start byte' => ["\xFF"];
        yield 'invalid UTF-8 continuation' => ["\xC2\x00"];
        // 0xC0 0x80 is overlong (codepoint 0) and rejected by mb_check_encoding.
        yield 'overlong UTF-8 encoding' => ["\xC0\x80"];
        yield 'normal mouse cb below 32' => ["\x1B[M\x10\x40\x40"];
        yield 'rxvt mouse missing fields' => ["\x1B[32;30M"];
        yield 'CSI with semicolon at offset 2' => ["\x1B[;"];
    }

    public function testCursorPositionWithSingleFieldEmitsNoEvent(): void
    {
        $parser = EventParser::new();
        $parser->advance("\x1B[20R", false);

        self::assertCount(0, $parser->drain());
    }

    public function testParseAcrossMultipleAdvanceCallsIsBufferedCorrectly(): void
    {
        $parser = EventParser::new();
        $parser->advance("\x1B[", true);
        self::assertCount(0, $parser->drain());

        $parser->advance('A', false);
        $events = $parser->drain();

        self::assertCount(1, $events);
        self::assertEquals(CodedKeyEvent::new(KeyCode::Up), $events[0]);
    }

    public function testIncompleteUtf8AcrossAdvanceCalls(): void
    {
        $parser = EventParser::new();
        $parser->advance("\xC2", true);
        self::assertCount(0, $parser->drain());

        $parser->advance("\xA3", false);
        $events = $parser->drain();

        self::assertCount(1, $events);
        self::assertEquals(CharKeyEvent::new('£'), $events[0]);
    }

    public function testNewFactoryReturnsInstance(): void
    {
        self::assertInstanceOf(EventParser::class, EventParser::new());
    }

    public function testDrainEmptiesBufferBetweenCalls(): void
    {
        $parser = EventParser::new();
        $parser->advance('a', false);

        self::assertCount(1, $parser->drain());
        self::assertCount(0, $parser->drain());
    }

    public function testDrainReturnsMultipleEventsInOrder(): void
    {
        $parser = EventParser::new();
        $parser->advance('abc', false);

        $events = $parser->drain();

        self::assertCount(3, $events);
        self::assertEquals(CharKeyEvent::new('a'), $events[0]);
        self::assertEquals(CharKeyEvent::new('b'), $events[1]);
        self::assertEquals(CharKeyEvent::new('c'), $events[2]);
    }

    /**
     * @return Generator<array{0:string,1:?Event,2?:bool}>
     */
    public static function provideParse(): Generator
    {
        yield 'number' => [
            '1',
            CharKeyEvent::new('1'),
        ];
        yield 'csi cursor position' => [
            "\x1B[20;10R",
            new CursorPositionEvent(9, 19),
        ];
        yield 'esc' => [
            "\x1B",
            CodedKeyEvent::new(KeyCode::Esc),
        ];
        yield 'possible esc sequence' => [
            "\x1B",
            null,
            true,
        ];
        yield 'Backspace' => [
            "\x7F",
            CodedKeyEvent::new(KeyCode::Backspace),
        ];
        yield 'Enter' => [
            "\r",
            CodedKeyEvent::new(KeyCode::Enter),
        ];
        yield 'Left' => [
            "\x1B[D",
            CodedKeyEvent::new(KeyCode::Left),
        ];
        yield 'Right' => [
            "\x1B[C",
            CodedKeyEvent::new(KeyCode::Right),
        ];
        yield 'Up' => [
            "\x1B[A",
            CodedKeyEvent::new(KeyCode::Up),
        ];
        yield 'Down' => [
            "\x1B[B",
            CodedKeyEvent::new(KeyCode::Down),
        ];
        yield 'Home' => [
            "\x1B[H",
            CodedKeyEvent::new(KeyCode::Home),
        ];
        yield 'BackTab' => [
            "\x1B[Z",
            CodedKeyEvent::new(KeyCode::BackTab, KeyModifiers::SHIFT),
        ];
        yield 'End' => [
            "\x1B[F",
            CodedKeyEvent::new(KeyCode::End),
        ];
        yield 'FocusGained' => [
            "\x1B[I",
            FocusEvent::gained(),
        ];
        yield 'FocusLost' => [
            "\x1B[O",
            FocusEvent::lost(),
        ];
        yield 'Tab' => [
            "\t",
            CodedKeyEvent::new(KeyCode::Tab),
        ];
        yield 'Left (D)' => [
            "\x1BOD",
            CodedKeyEvent::new(KeyCode::Left),
        ];
        yield 'Right (C)' => [
            "\x1BOC",
            CodedKeyEvent::new(KeyCode::Right),
        ];
        yield 'Up (A)' => [
            "\x1BOA",
            CodedKeyEvent::new(KeyCode::Up),
        ];
        yield 'Down (B)' => [
            "\x1BOB",
            CodedKeyEvent::new(KeyCode::Down),
        ];
        yield 'Home (H)' => [
            "\x1BOH",
            CodedKeyEvent::new(KeyCode::Home),
        ];
        yield 'End (F)' => [
            "\x1BOF",
            CodedKeyEvent::new(KeyCode::End),
        ];
        yield 'F1 (P)' => [
            "\x1BOP",
            FunctionKeyEvent::new(1),
        ];
        yield 'F2 (Q)' => [
            "\x1BOQ",
            FunctionKeyEvent::new(2),
        ];
        yield 'F3 (R)' => [
            "\x1BOR",
            FunctionKeyEvent::new(3),
        ];
        yield 'F4 (S)' => [
            "\x1BOS",
            FunctionKeyEvent::new(4),
        ];
        yield 'F1' => [
            "\x1B[P",
            FunctionKeyEvent::new(1),
        ];
        yield 'F2' => [
            "\x1B[Q",
            FunctionKeyEvent::new(2),
        ];
        yield 'F3' => [
            "\x1B[R",
            FunctionKeyEvent::new(3),
        ];
        yield 'F4' => [
            "\x1B[S",
            FunctionKeyEvent::new(4),
        ];
        yield 'double escape' => [
            "\x1B\x1B",
            CodedKeyEvent::new(KeyCode::Esc),
        ];
        yield 'escape then char' => [
            "\x1Ba",
            CharKeyEvent::new('a'),
        ];
        yield 'Char' => [
            'a',
            CharKeyEvent::new('a'),
        ];
        yield 'Uppercase Char' => [
            'A',
            CharKeyEvent::new('A', KeyModifiers::SHIFT),
        ];
    }

    /**
     * @return Generator<array{0:string,1:?Event,2?:bool}>
     */
    public static function provideCsiSpecialKeyCode(): Generator
    {
        yield 'Delete' => [
            "\x1B[3~",
            CodedKeyEvent::new(KeyCode::Delete),
        ];
        yield 'Home 1' => [
            "\x1B[1~",
            CodedKeyEvent::new(KeyCode::Home),
        ];
        yield 'Home 2' => [
            "\x1B[7~",
            CodedKeyEvent::new(KeyCode::Home),
        ];
        yield 'Insert' => [
            "\x1B[2~",
            CodedKeyEvent::new(KeyCode::Insert),
        ];
        yield 'CSI End 1' => [
            "\x1B[4~",
            CodedKeyEvent::new(KeyCode::End),
        ];
        yield 'CSI End 2' => [
            "\x1B[8~",
            CodedKeyEvent::new(KeyCode::End),
        ];
        yield 'PageUp' => [
            "\x1B[5~",
            CodedKeyEvent::new(KeyCode::PageUp),
        ];
        yield 'PageDown' => [
            "\x1B[6~",
            CodedKeyEvent::new(KeyCode::PageDown),
        ];
    }

    /**
     * @return Generator<array{0:string,1:?Event,2?:bool}>
     */
    public static function provideCsiModifierKeyCode(): Generator
    {
        yield 'special key code with types' => [
            "\x1B[1;1:3B",
            CodedKeyEvent::new(KeyCode::Down, KeyModifiers::NONE, KeyEventKind::Release),
        ];
        yield 'Shift F1' => [
            "\x1B[1;2P",
            FunctionKeyEvent::new(1, KeyModifiers::SHIFT),
        ];
        yield 'Alt F1' => [
            "\x1B[1;3P",
            FunctionKeyEvent::new(1, KeyModifiers::ALT),
        ];
        yield 'Ctl F1' => [
            "\x1B[1;5P",
            FunctionKeyEvent::new(1, KeyModifiers::CONTROL),
        ];
        yield 'Super F1' => [
            "\x1B[1;9P",
            FunctionKeyEvent::new(1, KeyModifiers::SUPER),
        ];
        yield 'Hyper F1' => [
            "\x1B[1;17P",
            FunctionKeyEvent::new(1, KeyModifiers::HYPER),
        ];
        yield 'Meta F1' => [
            "\x1B[1;33P",
            FunctionKeyEvent::new(1, KeyModifiers::META),
        ];

        yield 'Control a' => [
            "\x01",
            CharKeyEvent::new('a', KeyModifiers::CONTROL),
        ];
        yield 'Control z' => [
            "\x1A",
            CharKeyEvent::new('z', KeyModifiers::CONTROL),
        ];
        yield 'Control 4' => [
            "\x1C",
            CharKeyEvent::new('4', KeyModifiers::CONTROL),
        ];
        yield 'Control 7' => [
            "\x1F",
            CharKeyEvent::new('7', KeyModifiers::CONTROL),
        ];
        yield 'Control space' => [
            "\0",
            CharKeyEvent::new(' ', KeyModifiers::CONTROL),
        ];
        yield 'utf8 2 bytes £' => [
            "\xC2\xA3",
            CharKeyEvent::new('£'),
        ];
        yield 'utf8 3 bytes' => [
            "\xee\xad\x94",
            CharKeyEvent::new("\xee\xad\x94"),
        ];
        yield 'utf8 4 bytes 🐈' => [
            "\xf0\x9f\x90\x88",
            CharKeyEvent::new('🐈'),
        ];
    }

    /**
     * @return Generator<array{0:string,1:?Event,2?:bool}>
     */
    public static function provideCsiMouse(): Generator
    {
        yield 'CSI normal mouse' => [
            "\x1B[M0\x60\x70",
            MouseEvent::new(
                kind: MouseEventKind::Down,
                button: MouseButton::Left,
                column: 63,
                row: 79,
                modifiers: KeyModifiers::CONTROL,
            ),
        ];
        yield 'CSI RXVT normal mouse' => [
            "\x1B[32;30;40;M",
            MouseEvent::new(
                kind: MouseEventKind::Down,
                button: MouseButton::Left,
                column: 29,
                row: 39,
                modifiers: KeyModifiers::NONE,
            ),
        ];
        yield 'CSI SGR mouse' => [
            "\x1B[<0;20;10;M",
            MouseEvent::new(
                kind: MouseEventKind::Down,
                button: MouseButton::Left,
                column: 19,
                row: 9,
                modifiers: KeyModifiers::NONE,
            ),
        ];
        yield 'CSI SGR mouse UP' => [
            "\x1B[<0;20;10;m",
            MouseEvent::new(
                kind: MouseEventKind::Up,
                button: MouseButton::Left,
                column: 19,
                row: 9,
                modifiers: KeyModifiers::NONE,
            ),
        ];
    }

    /**
     * @return Generator<array{0:string,1:?Event,2?:bool}>
     */
    public static function provideExtraCsiTilde(): Generator
    {
        yield 'F5 via ~' => ["\x1B[15~", FunctionKeyEvent::new(5)];
        yield 'F6 via ~' => ["\x1B[17~", FunctionKeyEvent::new(6)];
        yield 'F11 via ~' => ["\x1B[23~", FunctionKeyEvent::new(11)];
        yield 'F-key range 28-29' => ["\x1B[28~", FunctionKeyEvent::new(13)];
        yield 'F-key range 31-34' => ["\x1B[31~", FunctionKeyEvent::new(14)];
    }

    /**
     * @return Generator<array{0:string,1:?Event,2?:bool}>
     */
    public static function provideExtraMouse(): Generator
    {
        yield 'SGR Middle button down' => [
            "\x1B[<1;5;5M",
            MouseEvent::new(MouseEventKind::Down, MouseButton::Middle, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR Right button down' => [
            "\x1B[<2;5;5M",
            MouseEvent::new(MouseEventKind::Down, MouseButton::Right, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR Drag Left' => [
            "\x1B[<32;5;5M",
            MouseEvent::new(MouseEventKind::Drag, MouseButton::Left, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR Drag Middle' => [
            "\x1B[<33;5;5M",
            MouseEvent::new(MouseEventKind::Drag, MouseButton::Middle, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR Drag Right' => [
            "\x1B[<34;5;5M",
            MouseEvent::new(MouseEventKind::Drag, MouseButton::Right, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR Moved (drag with no real button)' => [
            "\x1B[<35;5;5M",
            MouseEvent::new(MouseEventKind::Moved, MouseButton::None, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR ScrollUp' => [
            "\x1B[<64;5;5M",
            MouseEvent::new(MouseEventKind::ScrollUp, MouseButton::None, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR ScrollDown' => [
            "\x1B[<65;5;5M",
            MouseEvent::new(MouseEventKind::ScrollDown, MouseButton::None, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR ScrollLeft' => [
            "\x1B[<66;5;5M",
            MouseEvent::new(MouseEventKind::ScrollLeft, MouseButton::None, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR ScrollRight' => [
            "\x1B[<67;5;5M",
            MouseEvent::new(MouseEventKind::ScrollRight, MouseButton::None, 4, 4, KeyModifiers::NONE),
        ];
        yield 'SGR with SHIFT modifier' => [
            "\x1B[<4;5;5M",
            MouseEvent::new(MouseEventKind::Down, MouseButton::Left, 4, 4, KeyModifiers::SHIFT),
        ];
        yield 'SGR with ALT modifier' => [
            "\x1B[<8;5;5M",
            MouseEvent::new(MouseEventKind::Down, MouseButton::Left, 4, 4, KeyModifiers::ALT),
        ];
        // 'm' only converts Down → Up; non-Down kinds (Drag, Scroll…) stay.
        yield 'SGR Drag with release marker keeps Drag' => [
            "\x1B[<32;5;5m",
            MouseEvent::new(MouseEventKind::Drag, MouseButton::Left, 4, 4, KeyModifiers::NONE),
        ];
    }

    /**
     * @return Generator<array{0:string,1:?Event,2?:bool}>
     */
    public static function provideExtraModifiers(): Generator
    {
        yield 'CSI Repeat kind on Down' => [
            "\x1B[1;1:2B",
            CodedKeyEvent::new(KeyCode::Down, KeyModifiers::NONE, KeyEventKind::Repeat),
        ];

        yield 'Shift Up' => ["\x1B[1;2A", CodedKeyEvent::new(KeyCode::Up, KeyModifiers::SHIFT)];
        yield 'Shift Right' => ["\x1B[1;2C", CodedKeyEvent::new(KeyCode::Right, KeyModifiers::SHIFT)];
        yield 'Shift Left' => ["\x1B[1;2D", CodedKeyEvent::new(KeyCode::Left, KeyModifiers::SHIFT)];
        yield 'Shift End' => ["\x1B[1;2F", CodedKeyEvent::new(KeyCode::End, KeyModifiers::SHIFT)];
        yield 'Shift Home' => ["\x1B[1;2H", CodedKeyEvent::new(KeyCode::Home, KeyModifiers::SHIFT)];

        // F3 ('R') is intercepted by parseCsiCursorPosition before reaching here.
        yield 'Shift F2' => ["\x1B[1;2Q", FunctionKeyEvent::new(2, KeyModifiers::SHIFT)];
        yield 'Shift F4' => ["\x1B[1;2S", FunctionKeyEvent::new(4, KeyModifiers::SHIFT)];

        yield 'modifier section without digits' => [
            "\x1B[1;A",
            CodedKeyEvent::new(KeyCode::Up, KeyModifiers::NONE),
        ];

        yield 'kind sub-field without digits' => [
            "\x1B[1;2:A",
            CodedKeyEvent::new(KeyCode::Up, KeyModifiers::NONE),
        ];
    }

    /**
     * @dataProvider provideModifierKeyCodeErrors
     */
    public function testParserRecoversFromModifierKeyCodeErrors(string $bad): void
    {
        $parser = EventParser::new();
        $parser->advance($bad . 'z', false);

        $events = $parser->drain();

        self::assertCount(1, $events);
        self::assertEquals(CharKeyEvent::new('z'), $events[0]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideModifierKeyCodeErrors(): iterable
    {
        yield 'CSI with no separator and cursor letter' => ["\x1B[5A"];
        yield 'CSI with unknown final byte' => ["\x1B[1;2X"];
    }
}

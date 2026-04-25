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
}

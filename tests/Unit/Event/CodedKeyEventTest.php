<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Event;

use PhpTui\Term\Event;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\KeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyEventKind;
use PhpTui\Term\KeyModifiers;
use PHPUnit\Framework\TestCase;

final class CodedKeyEventTest extends TestCase
{
    /** @noinspection PhpConditionAlreadyCheckedInspection */
    public function testImplementsKeyEventInterface(): void
    {
        $event = CodedKeyEvent::new(KeyCode::Enter);

        self::assertInstanceOf(KeyEvent::class, $event);
        self::assertInstanceOf(Event::class, $event);
    }

    public function testExposesCodeModifiersAndKind(): void
    {
        $event = CodedKeyEvent::new(
            KeyCode::Left,
            KeyModifiers::ALT,
            KeyEventKind::Release,
        );

        self::assertSame(KeyCode::Left, $event->code);
        self::assertSame(KeyModifiers::ALT, $event->modifiers);
        self::assertSame(KeyEventKind::Release, $event->kind);
    }

    public function testDefaultsAreNoneAndPress(): void
    {
        $event = CodedKeyEvent::new(KeyCode::Esc);

        self::assertSame(KeyModifiers::NONE, $event->modifiers);
        self::assertSame(KeyEventKind::Press, $event->kind);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame(
            'CodedKeyEvent(code: Enter, modifiers: none, kind: Press)',
            (string) CodedKeyEvent::new(KeyCode::Enter),
        );
        self::assertSame(
            'CodedKeyEvent(code: Left, modifiers: ctl, kind: Repeat)',
            (string) CodedKeyEvent::new(KeyCode::Left, KeyModifiers::CONTROL, KeyEventKind::Repeat),
        );
    }
}

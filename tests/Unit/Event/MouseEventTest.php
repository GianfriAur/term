<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Event;

use PhpTui\Term\Event;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\KeyModifiers;
use PhpTui\Term\MouseButton;
use PhpTui\Term\MouseEventKind;
use PHPUnit\Framework\TestCase;

final class MouseEventTest extends TestCase
{
    public function testImplementsEventInterface(): void
    {
        self::assertInstanceOf(
            Event::class,
            MouseEvent::new(MouseEventKind::Down, MouseButton::Left, 0, 0, KeyModifiers::NONE),
        );
    }

    public function testExposesAllFields(): void
    {
        $event = MouseEvent::new(
            MouseEventKind::Drag,
            MouseButton::Right,
            42,
            17,
            KeyModifiers::SHIFT,
        );

        self::assertSame(MouseEventKind::Drag, $event->kind);
        self::assertSame(MouseButton::Right, $event->button);
        self::assertSame(42, $event->column);
        self::assertSame(17, $event->row);
        self::assertSame(KeyModifiers::SHIFT, $event->modifiers);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame(
            'MouseEvent(kind: Down, button: Left, col: 0, row: 0, modifiers: 0)',
            (string) MouseEvent::new(MouseEventKind::Down, MouseButton::Left, 0, 0, KeyModifiers::NONE),
        );
        self::assertSame(
            'MouseEvent(kind: ScrollUp, button: None, col: 5, row: 10, modifiers: 1)',
            (string) MouseEvent::new(MouseEventKind::ScrollUp, MouseButton::None, 5, 10, KeyModifiers::SHIFT),
        );
    }
}

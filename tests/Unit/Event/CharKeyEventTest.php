<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Event;

use PhpTui\Term\Event;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\KeyEvent;
use PhpTui\Term\KeyModifiers;
use PHPUnit\Framework\TestCase;

final class CharKeyEventTest extends TestCase
{
    /** @noinspection PhpConditionAlreadyCheckedInspection */
    public function testImplementsKeyEventInterface(): void
    {
        $event = CharKeyEvent::new('a');

        self::assertInstanceOf(KeyEvent::class, $event);
        self::assertInstanceOf(Event::class, $event);
    }

    public function testExposesCharAndModifiers(): void
    {
        $event = CharKeyEvent::new('x', KeyModifiers::SHIFT | KeyModifiers::CONTROL);

        self::assertSame('x', $event->char);
        self::assertSame(KeyModifiers::SHIFT | KeyModifiers::CONTROL, $event->modifiers);
    }

    public function testDefaultModifiersIsNone(): void
    {
        self::assertSame(KeyModifiers::NONE, CharKeyEvent::new('a')->modifiers);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame(
            'CharKeyEvent(char: a, modifiers: none)',
            (string) CharKeyEvent::new('a'),
        );
        self::assertSame(
            'CharKeyEvent(char: A, modifiers: shift)',
            (string) CharKeyEvent::new('A', KeyModifiers::SHIFT),
        );
    }
}

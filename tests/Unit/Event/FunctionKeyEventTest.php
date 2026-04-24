<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Event;

use PhpTui\Term\Event;
use PhpTui\Term\Event\FunctionKeyEvent;
use PhpTui\Term\Event\KeyEvent;
use PhpTui\Term\KeyEventKind;
use PhpTui\Term\KeyModifiers;
use PHPUnit\Framework\TestCase;

final class FunctionKeyEventTest extends TestCase
{
    /** @noinspection PhpConditionAlreadyCheckedInspection */
    public function testImplementsKeyEventInterface(): void
    {
        $event = FunctionKeyEvent::new(1);

        self::assertInstanceOf(KeyEvent::class, $event);
        self::assertInstanceOf(Event::class, $event);
    }

    public function testExposesNumberModifiersAndKind(): void
    {
        $event = FunctionKeyEvent::new(5, KeyModifiers::SHIFT, KeyEventKind::Release);

        self::assertSame(5, $event->number);
        self::assertSame(KeyModifiers::SHIFT, $event->modifiers);
        self::assertSame(KeyEventKind::Release, $event->kind);
    }

    public function testDefaultsAreNoneAndPress(): void
    {
        $event = FunctionKeyEvent::new(1);

        self::assertSame(KeyModifiers::NONE, $event->modifiers);
        self::assertSame(KeyEventKind::Press, $event->kind);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame(
            'FunctionKey(number: 1, modifier: none, kind: Press)',
            (string) FunctionKeyEvent::new(1),
        );
        self::assertSame(
            'FunctionKey(number: 12, modifier: ctl,alt, kind: Release)',
            (string) FunctionKeyEvent::new(12, KeyModifiers::CONTROL | KeyModifiers::ALT, KeyEventKind::Release),
        );
    }
}

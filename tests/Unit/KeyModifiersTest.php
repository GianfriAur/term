<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\KeyModifiers;
use PHPUnit\Framework\TestCase;

final class KeyModifiersTest extends TestCase
{
    public function testConstantValuesAreBitFlags(): void
    {
        self::assertSame(0b0000_0000, KeyModifiers::NONE);
        self::assertSame(0b0000_0001, KeyModifiers::SHIFT);
        self::assertSame(0b0000_0010, KeyModifiers::CONTROL);
        self::assertSame(0b0000_0100, KeyModifiers::ALT);
        self::assertSame(0b0000_1000, KeyModifiers::SUPER);
        self::assertSame(0b0001_0000, KeyModifiers::HYPER);
        self::assertSame(0b0010_0000, KeyModifiers::META);
    }

    public function testFlagsAreMutuallyDisjoint(): void
    {
        $flags = [
            KeyModifiers::SHIFT,
            KeyModifiers::CONTROL,
            KeyModifiers::ALT,
            KeyModifiers::SUPER,
            KeyModifiers::HYPER,
            KeyModifiers::META,
        ];

        foreach ($flags as $i => $a) {
            foreach ($flags as $j => $b) {
                if ($i === $j) {
                    continue;
                }
                self::assertSame(0, $a & $b, "flags at index {$i} and {$j} overlap");
            }
        }
    }

    public function testToStringForNone(): void
    {
        self::assertSame('none', KeyModifiers::toString(KeyModifiers::NONE));
    }

    /**
     * @param int-mask-of<KeyModifiers::*> $modifier
     * @dataProvider provideSingleModifier
     */
    public function testToStringForSingleModifier(int $modifier, string $expected): void
    {
        self::assertSame($expected, KeyModifiers::toString($modifier));
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function provideSingleModifier(): iterable
    {
        yield 'SHIFT'   => [KeyModifiers::SHIFT,   'shift'];
        yield 'CONTROL' => [KeyModifiers::CONTROL, 'ctl'];
        yield 'ALT'     => [KeyModifiers::ALT,     'alt'];
        yield 'SUPER'   => [KeyModifiers::SUPER,   'super'];
        yield 'HYPER'   => [KeyModifiers::HYPER,   'hyper'];
        yield 'META'    => [KeyModifiers::META,    'meta'];
    }

    public function testToStringForCombinedModifiersPreservesDeclarationOrder(): void
    {
        self::assertSame(
            'shift,ctl',
            KeyModifiers::toString(KeyModifiers::SHIFT | KeyModifiers::CONTROL),
        );
        self::assertSame(
            'ctl,alt',
            KeyModifiers::toString(KeyModifiers::CONTROL | KeyModifiers::ALT),
        );
        self::assertSame(
            'shift,alt,meta',
            KeyModifiers::toString(KeyModifiers::SHIFT | KeyModifiers::ALT | KeyModifiers::META),
        );
    }

    public function testToStringForAllModifiers(): void
    {
        $all = KeyModifiers::SHIFT
            | KeyModifiers::CONTROL
            | KeyModifiers::ALT
            | KeyModifiers::SUPER
            | KeyModifiers::HYPER
            | KeyModifiers::META;

        self::assertSame('shift,ctl,alt,super,hyper,meta', KeyModifiers::toString($all));
    }

    public function testOrderDoesNotDependOnInputBitOrder(): void
    {
        $a = KeyModifiers::SHIFT | KeyModifiers::META;
        $b = KeyModifiers::META | KeyModifiers::SHIFT;

        self::assertSame(KeyModifiers::toString($a), KeyModifiers::toString($b));
        self::assertSame('shift,meta', KeyModifiers::toString($a));
    }
}

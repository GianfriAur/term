<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\SetModifier;
use PhpTui\Term\Attribute;
use PHPUnit\Framework\TestCase;

final class SetModifierTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new SetModifier(Attribute::Bold, true));
    }

    public function testExposesModifierAndEnable(): void
    {
        foreach (Attribute::cases() as $modifier) {
            self::assertSame($modifier, (new SetModifier($modifier, true))->modifier);
            self::assertSame($modifier, (new SetModifier($modifier, false))->modifier);
        }

        self::assertTrue((new SetModifier(Attribute::Bold, true))->enable);
        self::assertFalse((new SetModifier(Attribute::Bold, false))->enable);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('SetModifier(Bold,on)', (string) new SetModifier(Attribute::Bold, true));
        self::assertSame('SetModifier(Italic,off)', (string) new SetModifier(Attribute::Italic, false));
    }
}

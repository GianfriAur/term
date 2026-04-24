<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\SetTerminalTitle;
use PHPUnit\Framework\TestCase;

final class SetTerminalTitleTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new SetTerminalTitle('hello'));
    }

    public function testExposesTitle(): void
    {
        self::assertSame('hello', (new SetTerminalTitle('hello'))->title);
        self::assertSame('', (new SetTerminalTitle(''))->title);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('SetTerminalTitle("hello")', (string) new SetTerminalTitle('hello'));
        self::assertSame('SetTerminalTitle("")', (string) new SetTerminalTitle(''));
    }
}

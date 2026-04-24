<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\Reset;
use PHPUnit\Framework\TestCase;

final class ResetTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new Reset());
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('Reset()', (string) new Reset());
    }
}

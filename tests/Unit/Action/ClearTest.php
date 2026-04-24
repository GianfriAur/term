<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\Clear;
use PhpTui\Term\ClearType;
use PHPUnit\Framework\TestCase;

final class ClearTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new Clear(ClearType::All));
    }

    public function testExposesClearType(): void
    {
        // all allowed cases already tested in ClearTypeTest (tests/Unit/ClearTypeTest.php)
        foreach (ClearType::cases() as $clearType) {
            self::assertSame($clearType, (new Clear($clearType))->clearType);
        }
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('Clear(All)', (string) new Clear(ClearType::All));
        self::assertSame('Clear(Purge)', (string) new Clear(ClearType::Purge));
    }
}

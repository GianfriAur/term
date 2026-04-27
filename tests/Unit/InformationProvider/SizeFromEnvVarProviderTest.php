<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\InformationProvider;

use PhpTui\Term\InformationProvider;
use PhpTui\Term\InformationProvider\SizeFromEnvVarProvider;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;

final class SizeFromEnvVarProviderTest extends TestCase
{
    /** @var string|false */
    private string|false $originalLines;
    /** @var string|false */
    private string|false $originalCols;

    protected function setUp(): void
    {
        $this->originalLines = getenv('LINES');
        $this->originalCols = getenv('COLUMNS');
    }

    protected function tearDown(): void
    {
        $this->restoreEnv('LINES', $this->originalLines);
        $this->restoreEnv('COLUMNS', $this->originalCols);
    }

    public function testImplementsInformationProvider(): void
    {
        self::assertInstanceOf(InformationProvider::class, SizeFromEnvVarProvider::new());
    }

    public function testReturnsNullWhenAskedForUnrelatedClass(): void
    {
        putenv('LINES=24');
        putenv('COLUMNS=80');

        self::assertNull(SizeFromEnvVarProvider::new()->for(self::class));
    }

    public function testReturnsSizeFromEnvironmentVariables(): void
    {
        putenv('LINES=42');
        putenv('COLUMNS=132');

        $size = SizeFromEnvVarProvider::new()->for(Size::class);

        self::assertInstanceOf(Size::class, $size);
        self::assertSame(42, $size->lines);
        self::assertSame(132, $size->cols);
    }

    public function testReturnsNullWhenLinesIsMissing(): void
    {
        putenv('LINES');
        putenv('COLUMNS=80');

        self::assertNull(SizeFromEnvVarProvider::new()->for(Size::class));
    }

    public function testReturnsNullWhenColumnsIsMissing(): void
    {
        putenv('LINES=24');
        putenv('COLUMNS');

        self::assertNull(SizeFromEnvVarProvider::new()->for(Size::class));
    }

    public function testReturnsNullWhenLinesIsEmpty(): void
    {
        putenv('LINES=');
        putenv('COLUMNS=80');

        self::assertNull(SizeFromEnvVarProvider::new()->for(Size::class));
    }

    public function testReturnsNullWhenColumnsIsEmpty(): void
    {
        putenv('LINES=24');
        putenv('COLUMNS=');

        self::assertNull(SizeFromEnvVarProvider::new()->for(Size::class));
    }

    private function restoreEnv(string $name, string|false $value): void
    {
        if ($value === false) {
            putenv($name);
            return;
        }
        putenv($name . '=' . $value);
    }
}

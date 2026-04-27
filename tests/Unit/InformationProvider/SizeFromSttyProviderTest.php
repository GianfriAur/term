<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\InformationProvider;

use PhpTui\Term\InformationProvider;
use PhpTui\Term\InformationProvider\SizeFromSttyProvider;
use PhpTui\Term\ProcessResult;
use PhpTui\Term\Tests\Helpers\FakeProcessRunner;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;

final class SizeFromSttyProviderTest extends TestCase
{
    public function testImplementsInformationProvider(): void
    {
        self::assertInstanceOf(InformationProvider::class, SizeFromSttyProvider::new());
    }

    public function testNewFactoryWithoutArgumentsUsesDefaultRunner(): void
    {
        self::assertInstanceOf(SizeFromSttyProvider::class, SizeFromSttyProvider::new());
    }

    public function testReturnsNullWhenAskedForUnrelatedClass(): void
    {
        $runner = new FakeProcessRunner();
        $provider = SizeFromSttyProvider::new($runner);

        self::assertNull($provider->for(self::class));
        self::assertSame([], $runner->calls);
    }

    public function testInvokesSttyWithMinusAFlag(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "speed 38400 baud; rows 24; columns 80; line = 0;\n", ''),
        ]);

        SizeFromSttyProvider::new($runner)->for(Size::class);

        self::assertSame([['stty', '-a']], $runner->calls);
    }

    public function testParsesLinuxStyleOutput(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "speed 38400 baud; rows 24; columns 80; line = 0;\n", ''),
        ]);

        $size = SizeFromSttyProvider::new($runner)->for(Size::class);

        self::assertInstanceOf(Size::class, $size);
        self::assertSame(24, $size->lines);
        self::assertSame(80, $size->cols);
    }

    public function testParsesBsdStyleOutput(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "speed 9600 baud; 50 rows; 200 columns;\n", ''),
        ]);

        $size = SizeFromSttyProvider::new($runner)->for(Size::class);

        self::assertInstanceOf(Size::class, $size);
        self::assertSame(50, $size->lines);
        self::assertSame(200, $size->cols);
    }

    public function testReturnsNullWhenSttyExitsNonZero(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(1, '', 'stty: stdin: not a terminal'),
        ]);

        self::assertNull(SizeFromSttyProvider::new($runner)->for(Size::class));
    }

    public function testReturnsNullWhenOutputDoesNotMatchAnyPattern(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "totally unrelated stty output without rows/columns\n", ''),
        ]);

        self::assertNull(SizeFromSttyProvider::new($runner)->for(Size::class));
    }
}

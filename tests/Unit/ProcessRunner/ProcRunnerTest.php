<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\ProcessRunner;

use PhpTui\Term\ProcessRunner;
use PhpTui\Term\ProcessRunner\ProcRunner;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ProcRunnerTest extends TestCase
{
    public function testImplementsProcessRunnerInterface(): void
    {
        self::assertInstanceOf(ProcessRunner::class, new ProcRunner());
    }

    public function testCapturesStdoutAndExitCode(): void
    {
        $result = (new ProcRunner())->run([PHP_BINARY, '-r', 'echo "hello";']);

        self::assertSame(0, $result->exitCode);
        self::assertSame('hello', $result->stdout);
        self::assertSame('', $result->stderr);
    }

    public function testCapturesStderr(): void
    {
        $result = (new ProcRunner())->run([PHP_BINARY, '-r', 'fwrite(STDERR, "oops");']);

        self::assertSame(0, $result->exitCode);
        self::assertSame('', $result->stdout);
        self::assertSame('oops', $result->stderr);
    }

    public function testReportsNonZeroExitCode(): void
    {
        $result = (new ProcRunner())->run([PHP_BINARY, '-r', 'exit(42);']);

        self::assertSame(42, $result->exitCode);
        self::assertSame('', $result->stdout);
    }

    public function testStdoutAndStderrAreIndependent(): void
    {
        $result = (new ProcRunner())->run([
            PHP_BINARY,
            '-r',
            'echo "out"; fwrite(STDERR, "err"); exit(3);',
        ]);

        self::assertSame(3, $result->exitCode);
        self::assertSame('out', $result->stdout);
        self::assertSame('err', $result->stderr);
    }
}

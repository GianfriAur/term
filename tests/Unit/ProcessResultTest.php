<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\ProcessResult;
use PHPUnit\Framework\TestCase;

final class ProcessResultTest extends TestCase
{
    public function testExposesExitCodeStdoutAndStderr(): void
    {
        $result = new ProcessResult(0, 'output', 'error');

        self::assertSame(0, $result->exitCode);
        self::assertSame('output', $result->stdout);
        self::assertSame('error', $result->stderr);
    }

    public function testAcceptsNonZeroExitCode(): void
    {
        $result = new ProcessResult(127, '', 'command not found');

        self::assertSame(127, $result->exitCode);
        self::assertSame('', $result->stdout);
        self::assertSame('command not found', $result->stderr);
    }
}

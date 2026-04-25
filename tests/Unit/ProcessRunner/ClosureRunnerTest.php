<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\ProcessRunner;

use PhpTui\Term\ProcessResult;
use PhpTui\Term\ProcessRunner;
use PhpTui\Term\ProcessRunner\ClosureRunner;
use PHPUnit\Framework\TestCase;

final class ClosureRunnerTest extends TestCase
{
    public function testImplementsProcessRunnerInterface(): void
    {
        self::assertInstanceOf(
            ProcessRunner::class,
            ClosureRunner::new(fn (array $cmd): ProcessResult => new ProcessResult(0, '', '')),
        );
    }

    public function testInvokesClosureWithCommandAndReturnsItsResult(): void
    {
        $expected = new ProcessResult(7, 'out', 'err');
        $captured = null;

        $runner = ClosureRunner::new(function (array $cmd) use (&$captured, $expected): ProcessResult {
            $captured = $cmd;

            return $expected;
        });

        $result = $runner->run(['stty', '-g']);

        self::assertSame(['stty', '-g'], $captured);
        self::assertSame($expected, $result);
    }

    public function testNewFactoryAndConstructorAreEquivalent(): void
    {
        $closure = fn (array $cmd): ProcessResult => new ProcessResult(0, 'x', '');

        $viaFactory = ClosureRunner::new($closure);
        $viaCtor = new ClosureRunner($closure);

        self::assertSame('x', $viaFactory->run([])->stdout);
        self::assertSame('x', $viaCtor->run([])->stdout);
    }
}

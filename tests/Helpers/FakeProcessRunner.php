<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Helpers;

use PhpTui\Term\ProcessResult;
use PhpTui\Term\ProcessRunner;

/**
 * Test fake for ProcessRunner. Returns pre-scripted ProcessResults in order,
 * and records every command that was run for later inspection.
 */
final class FakeProcessRunner implements ProcessRunner
{
    /** @var string[][] */
    public array $calls = [];

    /** @var ProcessResult[] */
    private array $results;

    /**
     * @param ProcessResult[] $results
     */
    public function __construct(array $results = [])
    {
        $this->results = $results;
    }

    public function run(array $command): ProcessResult
    {
        $this->calls[] = $command;

        $result = array_shift($this->results);
        if ($result === null) {
            return new ProcessResult(0, '', '');
        }

        return $result;
    }
}

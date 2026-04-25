<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\RawMode;

use PhpTui\Term\ProcessResult;
use PhpTui\Term\RawMode;
use PhpTui\Term\RawMode\SttyRawMode;
use PhpTui\Term\Tests\Helpers\FakeProcessRunner;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SttyRawModeTest extends TestCase
{
    public function testImplementsRawModeInterface(): void
    {
        self::assertInstanceOf(RawMode::class, SttyRawMode::new(new FakeProcessRunner()));
    }

    public function testStartsDisabled(): void
    {
        self::assertFalse(SttyRawMode::new(new FakeProcessRunner())->isEnabled());
    }

    public function testEnableRunsThreeSttyCommandsAndFlipsIsEnabled(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "saved-stty-settings\n", ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
        ]);
        $rawMode = SttyRawMode::new($runner);

        $rawMode->enable();

        self::assertTrue($rawMode->isEnabled());
        self::assertSame(
            [['stty', '-g'], ['stty', 'raw'], ['stty', '-echo']],
            $runner->calls,
        );
    }

    public function testEnableIsIdempotent(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "saved\n", ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
        ]);
        $rawMode = SttyRawMode::new($runner);

        $rawMode->enable();
        $rawMode->enable();

        self::assertCount(3, $runner->calls);
    }

    public function testEnableThrowsWhenSttyGetSettingsFails(): void
    {
        $runner = new FakeProcessRunner([new ProcessResult(1, '', '')]);
        $rawMode = SttyRawMode::new($runner);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not get stty settings');

        $rawMode->enable();
    }

    public function testEnableThrowsWhenSttyRawFails(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "saved\n", ''),
            new ProcessResult(1, '', ''),
        ]);
        $rawMode = SttyRawMode::new($runner);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not set raw mode');

        $rawMode->enable();
    }

    public function testEnableThrowsWhenSttyNoEchoFails(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "saved\n", ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(1, '', ''),
        ]);
        $rawMode = SttyRawMode::new($runner);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not disable echo');

        $rawMode->enable();
    }

    public function testDisableRestoresSavedSettingsAndFlipsIsEnabled(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "saved-settings\n", ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
        ]);
        $rawMode = SttyRawMode::new($runner);

        $rawMode->enable();
        $rawMode->disable();

        self::assertFalse($rawMode->isEnabled());
        self::assertSame(['stty', 'saved-settings'], $runner->calls[3]);
    }

    public function testDisableWithoutEnableIsNoop(): void
    {
        $runner = new FakeProcessRunner();
        $rawMode = SttyRawMode::new($runner);

        $rawMode->disable();

        self::assertFalse($rawMode->isEnabled());
        self::assertSame([], $runner->calls);
    }

    public function testDisableThrowsAndIncludesStderrWhenRestoreFails(): void
    {
        $runner = new FakeProcessRunner([
            new ProcessResult(0, "saved\n", ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(1, '', 'stty: invalid argument'),
        ]);
        $rawMode = SttyRawMode::new($runner);
        $rawMode->enable();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not restore from raw mode: stty: invalid argument');

        $rawMode->disable();
    }
}

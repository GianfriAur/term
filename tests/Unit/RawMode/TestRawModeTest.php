<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\RawMode;

use PhpTui\Term\RawMode;
use PhpTui\Term\RawMode\TestRawMode;
use PHPUnit\Framework\TestCase;

final class TestRawModeTest extends TestCase
{
    public function testImplementsRawModeInterface(): void
    {
        self::assertInstanceOf(RawMode::class, new TestRawMode());
    }

    public function testStartsDisabled(): void
    {
        self::assertFalse((new TestRawMode())->isEnabled());
    }

    public function testEnableMakesIsEnabledReturnTrue(): void
    {
        $rawMode = new TestRawMode();
        $rawMode->enable();

        self::assertTrue($rawMode->isEnabled());
    }

    public function testDisableMakesIsEnabledReturnFalse(): void
    {
        $rawMode = new TestRawMode();
        $rawMode->enable();
        $rawMode->disable();

        self::assertFalse($rawMode->isEnabled());
    }

    public function testEnableIsIdempotent(): void
    {
        $rawMode = new TestRawMode();
        $rawMode->enable();
        $rawMode->enable();

        self::assertTrue($rawMode->isEnabled());
    }

    public function testDisableWithoutEnableIsNoop(): void
    {
        $rawMode = new TestRawMode();
        $rawMode->disable();

        self::assertFalse($rawMode->isEnabled());
    }
}

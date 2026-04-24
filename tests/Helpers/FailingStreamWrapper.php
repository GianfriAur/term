<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Helpers;

/**
 * Test fixture: stream wrapper whose writes always fail (stream_write returns
 * false). Use to exercise fwrite-error code paths in Writer implementations.
 */
final class FailingStreamWrapper
{
    public const PROTOCOL = 'phptui-test-failing';

    /** @var resource|null */
    public $context;

    public static function register(): void
    {
        if (!in_array(self::PROTOCOL, stream_get_wrappers(), true)) {
            stream_wrapper_register(self::PROTOCOL, self::class);
        }
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        return true;
    }

    public function stream_write(string $data): int|false
    {
        return false;
    }

    public function stream_close(): void
    {
    }

    public function stream_eof(): bool
    {
        return true;
    }

    /**
     * @return array<int|string, int|string>|false
     */
    public function stream_stat(): array|false
    {
        return false;
    }
}

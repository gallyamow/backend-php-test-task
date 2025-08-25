<?php

declare(strict_types=1);

namespace App\Exception;

final class StorageException extends \RuntimeException implements AppExceptionInterface
{
    public const CODE_FAILED_TO_READ = 10;
    public const CODE_FAILED_TO_COMMIT = 20;
    public const CODE_FAILED_TO_FOUND = 30;

    public static function failedToRead(string $resource, \Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to read visits from "%s"', $resource),
            self::CODE_FAILED_TO_READ,
            $previous
        );
    }

    public static function failedToCommit(string $countryCode, \Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to commit visit for country "%s"', $countryCode),
            self::CODE_FAILED_TO_COMMIT,
            $previous
        );
    }

    public static function failedToFound(string $resource, \Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to found "%s"', $resource),
            self::CODE_FAILED_TO_FOUND,
            $previous
        );
    }
}

<?php

namespace Covaleski\Helpers;

use Covaleski\Helpers\Enums\FileMode;
use ErrorException;
use InvalidArgumentException;

/**
 * Provides helper methods to handle files.
 * 
 * Automatically sets error handlers to catch warnings and ensure return types.
 */
class File
{
    /**
     * Close a file.
     * 
     * @param resource $resource
     */
    public static function close(mixed $stream): void
    {
        Error::watch('fclose', $stream);
    }

    /**
     * Check whether the resource is closed.
     * 
     * @param resource $resource
     */
    public static function isClosed(mixed $resource): bool
    {
        return gettype($resource) === 'resource (closed)';
    }

    /**
     * Open a file.
     * 
     * @return resource
     * @throws ErrorException If cannot open the file.
     */
    public static function open(
        string $filename,
        FileMode $mode,
        mixed $context = null,
    ): mixed {
        return Error::watch('fopen', $filename, $mode->value, false, $context);
    }
}

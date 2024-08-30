<?php

namespace Covaleski\Helpers;

use Covaleski\Helpers\Enums\FileMode;
use ErrorException;

/**
 * Provides helper methods to handle files.
 * 
 * Automatically sets error handlers to catch warnings and ensure return types.
 */
class File
{
    /**
     * Close a resource.
     * 
     * @param resource $resource Resource to close.
     * @throws ErrorException If cannot close the stream.
     */
    public static function close(mixed $resource): void
    {
        Error::watch(function () use ($resource) {
            fclose($resource);
        });
    }

    /**
     * Open a file.
     * 
     * @param string $filename Filename to open.
     * @param FileMode $mode File mode to use.
     * @return resource File pointer.
     * @throws ErrorException If cannot open the file.
     */
    public static function open(
        string $filename,
        FileMode $mode,
        mixed $context = null,
    ): mixed {
        return Error::watch(function () use ($filename, $mode, $context) {
            return fopen($filename, $mode->value, false, $context);
        });
    }
}

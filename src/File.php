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
    public static function close(mixed $stream): void
    {
        Error::watch('fclose', $stream);
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
        return Error::watch('fopen', $filename, $mode->value, false, $context);
    }
}

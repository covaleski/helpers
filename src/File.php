<?php

namespace Covaleski\Helpers;

use Covaleski\Helpers\Enums\FileMode;
use ErrorException;

/**
 * Provides helper methods to handle files.
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
        fclose($stream);
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

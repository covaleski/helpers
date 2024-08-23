<?php

namespace Covaleski\Helpers;

use Covaleski\Helpers\Enums\FileMode;
use RuntimeException;
use Throwable;

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
     * @throws RuntimeException If cannot open the file.
     */
    public static function open(
        string $filename,
        FileMode $mode,
        mixed $context = null,
    ): mixed {
        set_error_handler(function (int $errno, string $errstr) {
            restore_error_handler();
            throw new RuntimeException($errstr, $errno);
        });
        $stream = fopen($filename, $mode->value, false, $context);
        restore_error_handler();
        return $stream;
    }
}

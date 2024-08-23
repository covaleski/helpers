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
        set_error_handler([static::class, 'handleError']);
        try {
            $stream = fopen($filename, $mode->value, false, $context);
            return $stream;
        } catch (ErrorException $exception) {
            throw $exception;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Handle an error.
     */
    protected static function handleError(
        int $code,
        string $message,
        string $filename,
        int $line,
    ): void {
        throw new ErrorException($message, $code, 1, $filename, $line);
    }
}

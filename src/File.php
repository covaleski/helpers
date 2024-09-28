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
     * @param null|resource $context Context stream resource.
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

    /**
     * Read the contents of a file from a resource or filename.
     * 
     * @param string|resource $file Filename or resource.
     * @param null|resource $context Context stream resource.
     * @param int $offset Amount of bytes to ignore.
     * @param null|int $length Amount of bytes to read.
     * @return string Read data.
     * @throws ErrorException If cannot read the filename or resource.
     */
    public static function read(
        mixed $file,
        mixed $context = null,
        int $offset = 0,
        null|int $length = null,
    ): string {
        return Error::watch(
            function () use ($file, $context, $offset, $length) {
                if (is_string($file)) {
                    return file_get_contents(
                        filename: $file,
                        use_include_path: false,
                        context: $context,
                        offset: $offset,
                        length: $length);
                } else {
                    return stream_get_contents($file, $length, $offset);
                }
            },
        );
    }
}

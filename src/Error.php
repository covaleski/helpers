<?php

namespace Covaleski\Helpers;

use Error as PhpError;
use ErrorException;

/**
 * Provides helper methods to handle PHP errors.
 */
class Error
{
    /**
     * Throw an `ErrorException` from error data.
     * 
     * @param int $code Error code.
     * @param string $message Error message.
     * @param string $filename Filename where the error was triggered.
     * @param int $line File line where the error was triggered.
     * @throws ErrorException Always (contains the provided error data).
     */
    public static function escalate(
        int $code,
        string $message,
        string $filename,
        int $line,
    ): void {
        throw new ErrorException($message, $code, 1, $filename, $line);
    }

    /**
     * Execute a function and escalate any thrown errors.
     * 
     * @template T
     * @param (callable(): T) $callback Function to call.
     * @param mixed ...$arguments Arguments to use with the callback.
     * @return T Value returned by the function if successfully executed.
     * @throws ErrorException If a warning/deprecation/error is triggered.
     * @throws ErrorException If an `Error` is thrown.
     */
    public static function watch(callable $callback, ...$arguments): mixed
    {
        set_error_handler([static::class, 'escalate']);
        try {
            return call_user_func_array($callback, $arguments);
        } catch (PhpError $error) {
            throw new ErrorException(
                code: $error->getCode(),
                message: $error->getMessage(),
                filename: $error->getFile(),
                line: $error->getLine(),
                previous: $error,
            );
        } finally {
            restore_error_handler();
        }
    }
}

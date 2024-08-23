<?php

namespace Covaleski\Helpers;

use ErrorException;

/**
 * Provides helper methods to handle PHP errors.
 */
class Error
{
    /**
     * Throw an `ErrorException` from error data.
     */
    public static function escalate(
        int $code,
        string $message,
        string $filename,
        int $line,
    ) : void {
        throw new ErrorException($message, $code, 1, $filename, $line);
    }

    /**
     * Execute a function and escalate any thrown errors.
     * 
     * @template T
     * @param (callable(): T) $callback
     * @return T
     */
    public static function watch(callable $callback, ...$arguments): mixed
    {
        set_error_handler([static::class, 'escalate']);
        try {
            return call_user_func_array($callback, $arguments);
        } finally {
            restore_error_handler();
        }
    }
}

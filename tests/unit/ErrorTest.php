<?php

namespace Tests\Unit;

use Covaleski\Helpers\Error;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Helpers\File
 */
#[CoversMethod(Error::class, 'escalate')]
#[CoversMethod(Error::class, 'watch')]
final class ErrorTest extends TestCase
{
    /**
     * Test if the helper can throw errors and warnings as exceptions.
     */
    public function testEscalatesErrorsAsExceptions(): void
    {
        /** @var null|array */
        $arguments = null;
        set_error_handler(function (...$error) use (&$arguments) {
            $arguments = $error;
        });
        fopen('data:foobar', 'r');
        trigger_error('Attention!', E_USER_WARNING);
        restore_error_handler();
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Attention!');
        Error::escalate(...$arguments);
    }

    /**
     * Test if the helper can watch and escalate for callback errors.
     */
    public function testWatchesForCallbackErrors(): void
    {
        $result = Error::watch(function (int $a, int $b): int {
            return $a * $b;
        }, 3, 9);
        $this->assertSame(27, $result);
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Kaboom!');
        Error::watch(function () {
            trigger_error('Kaboom!', E_USER_WARNING);
        });
    }
}

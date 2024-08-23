<?php

namespace Tests\Unit;

use Covaleski\Helpers\Error;
use Error as PhpError;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Helpers\File
 */
#[CoversMethod(Error::class, 'escalate')]
#[CoversMethod(Error::class, 'watch')]
final class ErrorTest extends TestCase
{
    /**
     * Provide test callbacks with errors.
     */
    public static function errorProvider(): array
    {
        return [
            ['Something is deprecated!', E_USER_DEPRECATED],
            ['I noticed something!', E_USER_NOTICE],
            ['Warning! Warning!', E_USER_WARNING],
            ['Panic!', E_USER_ERROR],
        ];
    }

    /**
     * Test if the helper can throw errors and warnings as exceptions.
     */
    #[DataProvider('errorProvider')]
    public function testEscalatesErrorsAsExceptions(string $str, int $no): void
    {
        /** @var null|array */
        $arguments = null;
        set_error_handler(function (...$error) use (&$arguments) {
            $arguments = $error;
        });
        trigger_error($str, $no);
        restore_error_handler();
        $this->expectException(ErrorException::class);
        $this->expectExceptionCode($no);
        $this->expectExceptionMessage($str);
        Error::escalate(...$arguments);
    }

    /**
     * Test if the helper can watch and escalate for callback errors.
     */
    public function testWatchesForCallbackErrors(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Kaboom!');
        Error::watch(function () {
            trigger_error('Kaboom!', E_USER_WARNING);
        });
    }

    /**
     * Test if the helper can watch and escalate for callback errors.
     */
    public function testWatchesForCallbackThrowables(): void
    {
        $error = new PhpError('Something went wrong!');
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Something went wrong!');
        try {
            Error::watch(function () use ($error) {
                throw $error;
            });
        } catch (ErrorException $exception) {
            $this->assertSame($error, $exception->getPrevious());
            throw $exception;
        }
    }
}

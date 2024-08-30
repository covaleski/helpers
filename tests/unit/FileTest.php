<?php

declare(strict_types=1);

namespace Tests\Unit;

use Covaleski\Helpers\Enums\FileMode;
use Covaleski\Helpers\Error;
use Covaleski\Helpers\File;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesMethod;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass \Covaleski\Helpers\File
 */
#[CoversMethod(File::class, 'close')]
#[CoversMethod(File::class, 'read')]
#[CoversMethod(File::class, 'open')]
#[UsesMethod(Error::class, 'escalate')]
#[UsesMethod(Error::class, 'watch')]
final class FileTest extends TestCase
{
    /**
     * Provides invalid filenames to open and associated error expectations.
     */
    public static function invalidOpeningProvider(): array
    {
        return [
            'inexistent file' => [
                [
                    (function () {
                        $filename = static::createTemporaryFile();
                        unlink($filename);
                        return $filename;
                    })(),
                    FileMode::READ_IF_EXISTS,
                ],
                'Failed to open stream: No such file or directory',
            ],
            'existent file' => [
                [
                    static::createTemporaryFile(),
                    FileMode::READ_WRITE_IF_NOT_EXISTS,
                ],
                'Failed to open stream: File exists',
            ],
            'invalid filename' => [
                [
                    'data:foobar',
                    FileMode::READ_IF_EXISTS,
                ],
                'Failed to open stream: rfc2397: no comma in URL',
            ],
        ];
    }

    /**
     * Provides reading parameters and filenames or pointers that must fail.
     */
    public static function invalidReadingProvider(): array
    {
        return [
            'closed resource' => [
                [
                    (function () {
                        $filename = static::createTemporaryFile('Hey, ho!');
                        $pointer = fopen($filename, 'r');
                        fclose($pointer);
                        return $pointer;
                    })(),
                    null,
                    5,
                    2,
                ],
                'stream_get_contents(): supplied resource is not a valid '
                    . 'stream resource',
            ],
            'data URI invalid fseek' => [
                [
                    'data:text/plain,Do not seek me to far!',
                    null,
                    381279,
                    10,
                ],
                'file_get_contents(): Failed to seek to position 381279 in '
                    . 'the stream',
            ],
            'write-only pointer' => [
                [
                    (function () {
                        $filename = static::createTemporaryFile('{foo:"bar"}');
                        $pointer = fopen($filename, 'c');
                        return $pointer;
                    })(),
                    null,
                    5,
                    5,
                ],
                'stream_get_contents(): Read of 8192 bytes failed with '
                    . 'errno=9 Bad file descriptor',
            ],
        ];
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass(): void
    {
        foreach (glob(dirname(__DIR__) . '/files/*') as $filename) {
            if (!str_ends_with($filename, '.gitkeep')) {
                unlink($filename);
            }
        }
    }

    /**
     * Provides readable file pointers and associated expectations.
     */
    public static function validReadingProvider(): array
    {
        $create = function (string $content) {
            return fopen(static::createTemporaryFile($content), 'r');
        };

        return [
            'temporary resource' => [
                [
                    $create('The quick brown fox jumps over the lazy dog.'),
                    null,
                ],
                [
                    [35, 8, 'lazy dog'],
                    [4, 15, 'quick brown fox'],
                    [85471, 1451, ''],
                ],
            ],
            'temporary filename' => [
                [
                    static::createTemporaryFile('Hello, World!'),
                    null,
                ],
                [
                    [0, null, 'Hello, World!'],
                    [0, 5, 'Hello'],
                    [7, 5, 'World'],
                    [12, null, '!'],
                    [0, 999, 'Hello, World!'],
                    [9999999, 2, ''],
                ],
            ],
            'data URI filename' => [
                [
                    'data:text/plain,Fusce in risus neque.',
                    null,
                ],
                [
                    [0, null, 'Fusce in risus neque.'],
                    [15, null, 'neque.'],
                    [6, 8, 'in risus'],
                    [15, 789451, 'neque.'],
                ],
            ],
            'data URI resource' => [
                [
                    fopen('data:text/plain,Maecenas quis dictum nisi.', 'r'),
                    null,
                ],
                [
                    [25, 9898745, '.'],
                    [0, 8, 'Maecenas'],
                    [9, 11, 'quis dictum'],
                    [0, 25, 'Maecenas quis dictum nisi'],
                    [0, null, 'Maecenas quis dictum nisi.'],
                ]
            ],
        ];
    }

    /**
     * Provides valid filenames to open and associated expectations.
     */
    public static function validOpeningProvider(): array
    {
        return [
            'empty file' => [
                [
                    $filename = static::createTemporaryFile(),
                    FileMode::WRITE,
                ],
                [
                    'mode' => 'c',
                    'stream_type' => 'STDIO',
                    'uri' => $filename,
                    'wrapper_type' => 'plainfile',
                ],
            ],
            'normal file' => [
                [
                    $filename = static::createTemporaryFile('Hello, World!'),
                    FileMode::READ_WRITE_APPEND,
                ],
                [
                    'mode' => 'a+',
                    'stream_type' => 'STDIO',
                    'uri' => $filename,
                    'wrapper_type' => 'plainfile',
                ],
            ],
            'data URI file' => [
                [
                    $filename = 'data:text/plain,Hello!',
                    FileMode::READ_IF_EXISTS,
                ],
                [
                    'mode' => 'r',
                    'stream_type' => 'RFC2397',
                    'uri' => $filename,
                    'wrapper_type' => 'RFC2397',
                ],
            ],
        ];
    }

    /**
     * Create a temporary file, close it and return its path.
     */
    protected static function createTemporaryFile(string $data = ''): string
    {
        $filename = dirname(__DIR__) . uniqid('/files/FileTest-');
        if (file_put_contents($filename, $data) === false) {
            throw new RuntimeException('Failed to create temporary file.');
        }
        return $filename;
    }

    /**
     * Test if the helper can open and close files properly.
     */
    #[DataProvider('validOpeningProvider')]
    public function testOpensAndClosesFiles(array $args, array $expected): void
    {
        $pointer = File::open(...$args);
        $this->assertIsResource($pointer);
        $this->assertIsNotClosedResource($pointer);
        $data = stream_get_meta_data($pointer);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertSame($value, $data[$key]);
        }
        File::close($pointer);
        $this->assertIsClosedResource($pointer);
    }

    /**
     * Test if throws exceptions when fails to open files.
     */
    #[DataProvider('invalidOpeningProvider')]
    public function testPanicsIfCannotOpen(array $args, string $expected): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage($expected);
        File::open(...$args);
    }

    /**
     * Test if throws exceptions when fails to close pointers.
     */
    public function testPanicsIfCannotClose(): void
    {
        $filename = 'data:text/plain,Hello!';
        $pointer = File::open($filename, FileMode::READ_IF_EXISTS);
        File::close($pointer);
        $this->expectException(ErrorException::class);
        $text = 'fclose(): supplied resource is not a valid stream resource';
        $this->expectExceptionMessage($text);
        File::close($pointer);
    }

    /**
     * Test if throws exceptions when fails to read files.
     */
    #[DataProvider('invalidReadingProvider')]
    public function testPanicsIfCannotRead(array $args, string $expected): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage($expected);
        File::read(...$args);
    }

    /**
     * Test if the helper can read files properly.
     */
    #[DataProvider('validReadingProvider')]
    public function testReadsFiles(array $arguments, array $tests): void
    {
        [$file, $context] = $arguments;
        foreach ($tests as [$offset, $length, $expected]) {
            $actual = File::read($file, $context, $offset, $length);
            $offset = var_export($offset, true);
            $length = var_export($length, true);
            $this->assertSame($expected, $actual, <<<TXT
                Assert read file contents.
                Offset: {$offset}
                Length: {$length}
                TXT);
        }
    }
}

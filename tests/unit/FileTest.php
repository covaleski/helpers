<?php

declare(strict_types=1);

namespace Tests\Unit;

use Covaleski\Helpers\Enums\FileMode;
use Covaleski\Helpers\Enums\WriteMode;
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
#[CoversMethod(File::class, 'write')]
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
            'r mode on inexistent file' => [
                [
                    (function () {
                        $filename = static::createTemporaryFile();
                        unlink($filename);
                        return $filename;
                    })(),
                    FileMode::READ_IF_EXISTS,
                ],
            ],
            'x+ mode on existent file' => [
                [
                    static::createTemporaryFile(),
                    FileMode::READ_WRITE_IF_NOT_EXISTS,
                ],
            ],
            'invalid data URI' => [
                [
                    'data:foobar',
                    FileMode::READ_IF_EXISTS,
                ],
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
            ],
            'invalid seek on data URI' => [
                [
                    'data:text/plain,Do not seek me to far!',
                    null,
                    381279,
                    10,
                ],
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
            ],
        ];
    }

    /**
     * Provides writing parameters and filenames or pointers that must fail.
     */
    public static function invalidWritingProvider(): array
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
                    ' Let\'s go!',
                    WriteMode::OVERWRITE,
                    null,
                    null,
                    null,
                ],
            ],
            'data URI' => [
                [
                    'data:text/plain,Can\'t write me!',
                    ' Append this.',
                    WriteMode::APPEND,
                    2,
                    10,
                    null,
                ],
            ],
            'read-only pointer' => [
                [
                    (function () {
                        $filename = static::createTemporaryFile('Read only!');
                        $pointer = fopen($filename, 'r');
                        return $pointer;
                    })(),
                    'Replace with this.',
                    WriteMode::TRUNCATE,
                    null,
                    null,
                    null,
                ],
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
     * Provides readable file pointers and associated expectations.
     */
    public static function validWritingProvider(): array
    {
        return [
            'temporary filename' => [
                [
                    static::createTemporaryFile('Hello, World!'),
                    ' Some text.',
                    WriteMode::APPEND,
                    null,
                    null,
                    null,
                ],
                [11, 'Hello, World! Some text.'],
            ],
            'temporary filename + offset + length' => [
                [
                    static::createTemporaryFile('Hello, World!'),
                    'James Doe',
                    WriteMode::OVERWRITE,
                    7,
                    5,
                    null,
                ],
                [5, 'Hello, James!'],
            ],
            'temporary filename + truncate + length' => [
                [
                    static::createTemporaryFile('Hello, World!'),
                    ' Some text.',
                    WriteMode::TRUNCATE,
                    null,
                    10,
                    null,
                ],
                [10, ' Some text'],
            ],
            'temporary filename + truncate + offset' => [
                [
                    static::createTemporaryFile('Hello, World!'),
                    ' Some text.',
                    WriteMode::TRUNCATE,
                    17,
                    null,
                    null,
                ],
                [11, "Hello, World!\0\0\0\0 Some text."],
            ],
            'empty file' => [
                [
                    fopen(static::createTemporaryFile(''), 'w+'),
                    'Add some text. Not this.',
                    WriteMode::OVERWRITE,
                    0,
                    14,
                    null,
                ],
                [14, 'Add some text.'],
            ],
            'inexistent file' => [
                [
                    (function () {
                        $filename = static::createTemporaryFile('');
                        unlink($filename);
                        return $filename;
                    })(),
                    'Add some text to the new file.',
                    WriteMode::APPEND,
                    null,
                    null,
                    null,
                ],
                [30, 'Add some text to the new file.'],
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
    public function testPanicsIfCannotOpen(array $arguments): void
    {
        $this->expectException(ErrorException::class);
        File::open(...$arguments);
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
        File::close($pointer);
    }

    /**
     * Test if throws exceptions when fails to read files.
     */
    #[DataProvider('invalidReadingProvider')]
    public function testPanicsIfCannotRead(array $arguments): void
    {
        $this->expectException(ErrorException::class);
        File::read(...$arguments);
    }

    /**
     * Test if throws exceptions when fails to write files.
     */
    #[DataProvider('invalidWritingProvider')]
    public function testPanicsIfCannotWrite(array $arguments): void
    {
        $this->expectException(ErrorException::class);
        File::write(...$arguments);
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

    /**
     * Test if the helper can write files properly.
     */
    #[DataProvider('validWritingProvider')]
    public function testWritesFiles(array $arguments, array $expected): void
    {
        [$expected_length, $expected_content] = $expected;
        $actual = File::write(...$arguments);
        $offset = var_export($arguments[3], true);
        $length = var_export($arguments[4], true);
        $message = <<<TXT
            Assert write file contents.
            Mode: {$arguments[2]->name}
            Offset: {$offset}
            Length: {$length}
            TXT;
        $this->assertSame($expected_length, $actual, $message);
        if (is_string($arguments[0])) {
            $content = file_get_contents($arguments[0]);
        } else {
            $content = stream_get_contents($arguments[0]);
        }
        $content = is_string($arguments[0])
            ? file_get_contents($arguments[0])
            : stream_get_contents($arguments[0], offset: 0);
        $this->assertSame($expected_content, $content, $message);
    }
}

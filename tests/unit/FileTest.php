<?php

declare(strict_types=1);

namespace Tests\Unit;

use Covaleski\Helpers\Enums\FileMode;
use Covaleski\Helpers\File;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass \Covaleski\Helpers\File
 */
#[CoversMethod(File::class, 'close')]
#[CoversMethod(File::class, 'open')]
#[CoversMethod(File::class, 'handleError')]
final class FileTest extends TestCase
{
    /**
     * Provides invalid temporary filenames and expected exception messages.
     */
    public static function invalidFilenameProvider(): array
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
     * Provides valid temporary filenames and associated expectations.
     */
    public static function validFilenameProvider(): array
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
    #[DataProvider('validFilenameProvider')]
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
     * Test if throws exceptions on failure.
     */
    #[DataProvider('invalidFilenameProvider')]
    public function testPanicsOnFailure(array $args, string $expected): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage($expected);
        File::open(...$args);
    }
}

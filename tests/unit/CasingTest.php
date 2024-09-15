<?php

namespace Tests\Unit;

use Covaleski\Helpers\Casing;
use Covaleski\Helpers\Enums\CasingMode;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Helpers\Casing
 */
#[CoversMethod(Casing::class, 'convert')]
#[CoversMethod(Casing::class, 'join')]
#[CoversMethod(Casing::class, 'joinPascal')]
#[CoversMethod(Casing::class, 'split')]
#[CoversMethod(Casing::class, 'splitCamel')]
final class CasingTest extends TestCase
{
    /**
     * Provide casing convertion test data.
     */
    public static function convertionProvider(): array
    {
        return [
            'from camel' => ['doSomethingNow', CasingMode::CAMEL, [
                [CasingMode::CAMEL, 'doSomethingNow'],
                [CasingMode::KEBAB, 'do-something-now'],
                [CasingMode::PASCAL, 'DoSomethingNow'],
                [CasingMode::SNAKE, 'do_something_now'],
            ]],
            'from kebab' => ['some-website-segment', CasingMode::KEBAB, [
                [CasingMode::CAMEL, 'someWebsiteSegment'],
                [CasingMode::KEBAB, 'some-website-segment'],
                [CasingMode::PASCAL, 'SomeWebsiteSegment'],
                [CasingMode::SNAKE, 'some_website_segment'],
            ]],
            'from pascal' => ['VeryCoolClass', CasingMode::PASCAL, [
                [CasingMode::CAMEL, 'veryCoolClass'],
                [CasingMode::KEBAB, 'very-cool-class'],
                [CasingMode::PASCAL, 'VeryCoolClass'],
                [CasingMode::SNAKE, 'very_cool_class'],
            ]],
            'from snake' => ['john_doe', CasingMode::SNAKE, [
                [CasingMode::CAMEL, 'johnDoe'],
                [CasingMode::KEBAB, 'john-doe'],
                [CasingMode::PASCAL, 'JohnDoe'],
                [CasingMode::SNAKE, 'john_doe'],
            ]],
        ];
    }

    /**
     * Test if can convert strings.
     */
    #[DataProvider('convertionProvider')]
    public function testCanConvert($subject, $from, $covertions): void
    {
        foreach ($covertions as [$to, $expected]) {
            $actual = Casing::convert($subject, $from, $to);
            $this->assertSame($expected, $actual);
        }
    }
}

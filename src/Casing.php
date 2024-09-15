<?php

namespace Covaleski\Helpers;

use Covaleski\Helpers\Enums\CasingMode;

/**
 * Provides helper methods to convert string casings.
 */
class Casing
{
    /**
     * Convert a string from a casing mode to another.
     */
    public static function convert(
        string $subject,
        CasingMode $from,
        CasingMode $to,
    ): string {
        return static::join(static::split($subject, $from), $to);
    }

    /**
     * Join a list of strings according to the specified mode.
     */
    public static function join(array $subject, CasingMode $mode): string
    {
        return match ($mode) {
            CasingMode::CAMEL => lcfirst(static::joinPascal($subject)),
            CasingMode::PASCAL => static::joinPascal($subject),
            CasingMode::SNAKE => strtolower(implode('_', $subject)),
            CasingMode::KEBAB => strtolower(implode('-', $subject)),
        };
    }

    /**
     * Split a string according to the specified mode.
     */
    public static function split(string $subject, CasingMode $mode): array
    {
        return match ($mode) {
            CasingMode::CAMEL,
            CasingMode::PASCAL => static::splitCamel($subject),
            CasingMode::SNAKE => explode('_', $subject),
            CasingMode::KEBAB => explode('-', $subject),
        };
    }

    /**
     * Join a list of strings into a Pascal case one.
     */
    protected static function joinPascal(array $subject): string
    {
        $segments = array_map(
            fn ($s) => ucfirst(strtolower($s)),
            $subject,
        );
        return implode('', $segments);
    }

    /**
     * Split a camel case string into a list of strings.
     */
    protected static function splitCamel(string $subject): array
    {
        $pattern = '/([[:upper:]][[:lower:]]+)/';
        $flags = PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE;
        return preg_split($pattern, $subject, -1, $flags);
    }
}

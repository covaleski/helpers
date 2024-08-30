<?php

namespace Covaleski\Helpers;

/**
 * Provides helper methods to get request data.
 */
class Request
{
    /**
     * Get a header line.
     */
    public static function getHeaderLine(string $name): null|string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $value = trim($_SERVER[$key] ?? '');
        return $value !== '' ? $value : null;
    }

    /**
     * Get a header list of values sorted by user preference.
     */
    public static function getHeaderOptions(string $name): array
    {
        $values = static::getHeaderValues($name);
        usort($values, function ($a, $b) {
            return static::compareHeaderValues($a, $b) * (-1);
        });
        return array_map(fn ($v) => trim(explode(';', $v)[0]), $values);
    }

    /**
     * Get a header list of values.
     */
    public static function getHeaderValues(string $name): array
    {
        $line = static::getHeaderLine($name);
        $values = array_map('trim', explode(',', $line));
        return array_filter($values, fn ($v) => $v !== '');
    }

    /**
     * Compare two header alternative values.
     */
    protected static function compareHeaderValues(string $a, string $b): int
    {
        return static::getQualityValue($a) <=> static::getQualityValue($b);
    }

    /**
     * Get the q-factor of a header value.
     */
    protected static function getQualityValue(string $value): float
    {
        $pattern = '/;\s*q\s*=\s*(\d+(\.\d+)?)/';
        preg_match($pattern, $value, $matches);
        return floatval($matches[1] ?? 1);
    }
}

<?php

declare(strict_types=1);

namespace Indx\Tests;

final class Assert
{
    public static function same(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(($message !== '' ? $message . ': ' : '')
                . 'expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true));
        }
    }

    public static function true(bool $value, string $message = ''): void
    {
        if (!$value) {
            throw new \RuntimeException($message !== '' ? $message : 'expected true');
        }
    }

    public static function false(bool $value, string $message = ''): void
    {
        if ($value) {
            throw new \RuntimeException($message !== '' ? $message : 'expected false');
        }
    }

    public static function contains(string $needle, string $haystack, string $message = ''): void
    {
        if (!str_contains($haystack, $needle)) {
            throw new \RuntimeException(($message !== '' ? $message . ': ' : '')
                . 'expected to find ' . var_export($needle, true)
                . ' in ' . var_export($haystack, true));
        }
    }

    public static function count(int $expected, array $list, string $message = ''): void
    {
        if (count($list) !== $expected) {
            throw new \RuntimeException(($message !== '' ? $message . ': ' : '')
                . 'expected ' . $expected . ' items, got ' . count($list));
        }
    }
}

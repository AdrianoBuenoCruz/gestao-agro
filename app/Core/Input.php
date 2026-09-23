<?php

declare(strict_types=1);

namespace App\Core;

final class Input
{
    public static function string(array $data, string $key, int $max = 255): string
    {
        return mb_substr(trim((string) ($data[$key] ?? '')), 0, $max, 'UTF-8');
    }

    public static function nullableString(array $data, string $key, int $max = 255): ?string
    {
        $value = self::string($data, $key, $max);
        return $value === '' ? null : $value;
    }

    public static function int(array $data, string $key): int
    {
        return filter_var($data[$key] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    }

    public static function nullableFloat(array $data, string $key): ?float
    {
        $value = str_replace(',', '.', trim((string) ($data[$key] ?? '')));
        return $value === '' || !is_numeric($value) ? null : (float) $value;
    }

    public static function date(array $data, string $key): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    public static function time(array $data, string $key): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) ? $value . ':00' : null;
    }
}


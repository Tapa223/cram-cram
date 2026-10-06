<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Configuration lue depuis le fichier .env à la racine (parseur maison, aucune dépendance).
 * Aucun secret n'est écrit dans le code source.
 */
final class Config
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $envFile): void
    {
        if (!is_file($envFile)) {
            throw new \RuntimeException('Fichier .env introuvable : copiez .env.example en .env puis adaptez-le.');
        }

        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if (preg_match('/^(["\'])(.*)\1$/', $value, $m)) {
                $value = $m[2];
            }
            self::$values[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = self::$values[$key] ?? null;
        return ($value === null || $value === '') ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::$values[$key] ?? null;
        if ($value === null || $value === '') {
            return $default;
        }
        return in_array(strtolower($value), ['1', 'true', 'on', 'yes'], true);
    }

    public static function int(string $key, int $default): int
    {
        $value = self::$values[$key] ?? null;
        return ($value !== null && is_numeric($value)) ? (int) $value : $default;
    }

    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }
}

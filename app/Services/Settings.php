<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Paramètres du site (table parametres), chargés une seule fois par requête.
 */
final class Settings
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    /** @return array<string, string> */
    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (Database::all('SELECT cle, valeur FROM parametres') as $row) {
                    self::$cache[(string) $row['cle']] = (string) ($row['valeur'] ?? '');
                }
            } catch (\PDOException $e) {
                \App\Core\ErrorHandler::log($e);
            }
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        $value = self::all()[$key] ?? '';
        return $value !== '' ? $value : $default;
    }

    /** @param array<string, string> $values */
    public static function save(array $values): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)'
        );
        foreach ($values as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        self::$cache = null;
    }
}

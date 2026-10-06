<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Base commune des modèles : accès par identifiant et génération de slugs uniques.
 */
abstract class Model
{
    protected const TABLE = '';

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM ' . static::TABLE . ' WHERE id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM ' . static::TABLE . ' WHERE id = ?', [$id]);
    }

    public static function count(string $where = '1 = 1', array $params = []): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM ' . static::TABLE . ' WHERE ' . $where, $params);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = slugify($title);
        $slug = $base;
        $n = 2;
        while (true) {
            $params = [$slug];
            $sql = 'SELECT COUNT(*) FROM ' . static::TABLE . ' WHERE slug = ?';
            if ($ignoreId !== null) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            if ((int) Database::value($sql, $params) === 0) {
                return $slug;
            }
            $slug = $base . '-' . $n++;
        }
    }

    /**
     * Insère une ligne à partir d'un tableau colonne => valeur (colonnes définies par le code, jamais par l'utilisateur).
     * @param array<string, mixed> $data
     */
    protected static function insert(array $data): int
    {
        $columns = array_keys($data);
        $sql = 'INSERT INTO ' . static::TABLE . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        Database::query($sql, array_values($data));
        return Database::lastId();
    }

    /** @param array<string, mixed> $data */
    protected static function updateRow(int $id, array $data): void
    {
        $sets = implode(', ', array_map(static fn (string $c): string => $c . ' = ?', array_keys($data)));
        Database::query('UPDATE ' . static::TABLE . ' SET ' . $sets . ' WHERE id = ?', [...array_values($data), $id]);
    }
}

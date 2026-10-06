<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Galeries photos rattachées aux projets, domaines d'action et activités.
 * Les noms de tables et de colonnes viennent exclusivement de la liste blanche ci-dessous.
 */
final class Galerie
{
    /** @var array<string, array{0: string, 1: string}> type => [table, colonne] */
    private const TYPES = [
        'projet'   => ['projet_medias', 'projet_id'],
        'domaine'  => ['domaine_medias', 'domaine_id'],
        'activite' => ['activite_medias', 'activite_id'],
    ];

    /** @return array{0: string, 1: string} */
    private static function target(string $type): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Type de galerie inconnu : ' . $type);
        }
        return self::TYPES[$type];
    }

    /** @return list<array<string, mixed>> */
    public static function items(string $type, int $ownerId): array
    {
        [$table, $col] = self::target($type);
        return Database::all(
            "SELECT m.id, m.fichier, m.texte_alt, m.titre, m.nom_original, m.largeur, m.hauteur
             FROM {$table} g JOIN medias m ON m.id = g.media_id
             WHERE g.{$col} = ? AND m.type_mime LIKE 'image/%'
             ORDER BY g.ordre, g.ajoute_le, m.id",
            [$ownerId]
        );
    }

    /** @param list<int> $mediaIds */
    public static function add(string $type, int $ownerId, array $mediaIds): int
    {
        [$table, $col] = self::target($type);
        $ordre = (int) Database::value("SELECT COALESCE(MAX(ordre), 0) FROM {$table} WHERE {$col} = ?", [$ownerId]);
        $added = 0;
        foreach (array_unique($mediaIds) as $mediaId) {
            $ordre++;
            $stmt = Database::query("INSERT IGNORE INTO {$table} ({$col}, media_id, ordre) VALUES (?, ?, ?)", [$ownerId, $mediaId, $ordre]);
            $added += $stmt->rowCount();
        }
        return $added;
    }

    /** @param list<int> $mediaIds */
    public static function remove(string $type, int $ownerId, array $mediaIds): int
    {
        if ($mediaIds === []) {
            return 0;
        }
        [$table, $col] = self::target($type);
        $marks = implode(',', array_fill(0, count($mediaIds), '?'));
        return Database::query("DELETE FROM {$table} WHERE {$col} = ? AND media_id IN ({$marks})", [$ownerId, ...$mediaIds])->rowCount();
    }
}

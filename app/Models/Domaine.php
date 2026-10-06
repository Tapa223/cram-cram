<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Domaine extends Model
{
    protected const TABLE = 'domaines_action';

    /** @return list<array<string, mixed>> */
    public static function allForAdmin(string $search = ''): array
    {
        $params = [];
        $where = '1 = 1';
        if ($search !== '') {
            $where = '(d.titre LIKE ? OR d.resume LIKE ?)';
            $params = ['%' . $search . '%', '%' . $search . '%'];
        }
        return Database::all(
            "SELECT d.*, m.fichier AS image_fichier,
                (SELECT COUNT(*) FROM projets p WHERE p.domaine_id = d.id) AS nb_projets,
                (SELECT COUNT(*) FROM activites a WHERE a.domaine_id = d.id) AS nb_activites
             FROM domaines_action d LEFT JOIN medias m ON m.id = d.image_id
             WHERE {$where} ORDER BY d.ordre, d.titre",
            $params
        );
    }

    /** @return list<array<string, mixed>> */
    public static function published(): array
    {
        return Database::all(
            'SELECT d.*, m.fichier AS image_fichier, m.texte_alt AS image_alt,
                (SELECT COUNT(*) FROM projets p WHERE p.domaine_id = d.id AND p.publie = 1) AS nb_projets
             FROM domaines_action d LEFT JOIN medias m ON m.id = d.image_id
             WHERE d.publie = 1 ORDER BY d.ordre, d.titre'
        );
    }

    /** @return list<array<string, mixed>> */
    public static function options(): array
    {
        return Database::all('SELECT id, titre FROM domaines_action ORDER BY ordre, titre');
    }

    /** @return array<string, mixed>|null */
    public static function findPublishedBySlug(string $slug): ?array
    {
        return Database::first(
            'SELECT d.*, m.fichier AS image_fichier, m.texte_alt AS image_alt
             FROM domaines_action d LEFT JOIN medias m ON m.id = d.image_id
             WHERE d.slug = ? AND d.publie = 1',
            [$slug]
        );
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        return self::insert($data);
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): void
    {
        self::updateRow($id, $data);
    }

    public static function nextOrder(): int
    {
        return (int) Database::value('SELECT COALESCE(MAX(ordre), 0) + 1 FROM domaines_action');
    }

    /** @param list<int> $ids */
    public static function setPublished(array $ids, bool $published): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("UPDATE domaines_action SET publie = ? WHERE id IN ({$in})", [$published ? 1 : 0, ...$ids])->rowCount();
    }

    /** @return list<array<string, mixed>> */
    public static function projectsPerDomain(): array
    {
        return Database::all(
            'SELECT d.titre, COUNT(p.id) AS total
             FROM domaines_action d LEFT JOIN projets p ON p.domaine_id = d.id
             GROUP BY d.id, d.titre, d.ordre ORDER BY d.ordre, d.titre'
        );
    }
}

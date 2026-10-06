<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Partenaire extends Model
{
    protected const TABLE = 'partenaires';

    public const CATEGORIES = [
        'financier'     => 'Bailleurs et partenaires financiers',
        'technique'     => 'Partenaires techniques et de recherche',
        'national'      => 'Partenaires nationaux de mise en œuvre',
        'international' => 'Partenaires internationaux',
    ];

    public const CATEGORIES_COURTES = [
        'financier'     => 'Financier',
        'technique'     => 'Technique et recherche',
        'national'      => 'National',
        'international' => 'International',
    ];

    /** @return list<array<string, mixed>> */
    public static function allForAdmin(string $search = '', string $categorie = '', string $publication = ''): array
    {
        $where = ['1 = 1'];
        $params = [];
        if ($search !== '') {
            $where[] = '(pa.nom LIKE ? OR pa.sigle LIKE ? OR pa.pays LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like);
        }
        if (isset(self::CATEGORIES[$categorie])) {
            $where[] = 'pa.categorie = ?';
            $params[] = $categorie;
        }
        if ($publication === 'publie') {
            $where[] = 'pa.publie = 1';
        } elseif ($publication === 'masque') {
            $where[] = 'pa.publie = 0';
        }
        return Database::all(
            'SELECT pa.*, m.fichier AS logo_fichier,
                (SELECT COUNT(*) FROM projet_partenaire pp WHERE pp.partenaire_id = pa.id) AS nb_projets
             FROM partenaires pa LEFT JOIN medias m ON m.id = pa.logo_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY FIELD(pa.categorie, \'financier\', \'technique\', \'national\', \'international\'), pa.ordre, pa.nom',
            $params
        );
    }

    /** @return array<string, int> */
    public static function countsByCategorie(): array
    {
        $counts = ['tous' => 0] + array_fill_keys(array_keys(self::CATEGORIES), 0);
        foreach (Database::all('SELECT categorie, COUNT(*) AS n FROM partenaires GROUP BY categorie') as $row) {
            $counts[(string) $row['categorie']] = (int) $row['n'];
            $counts['tous'] += (int) $row['n'];
        }
        return $counts;
    }

    /** @return array<string, list<array<string, mixed>>> */
    public static function publishedByCategorie(): array
    {
        $grouped = array_fill_keys(array_keys(self::CATEGORIES), []);
        $rows = Database::all(
            'SELECT pa.*, m.fichier AS logo_fichier FROM partenaires pa LEFT JOIN medias m ON m.id = pa.logo_id
             WHERE pa.publie = 1 ORDER BY pa.ordre, pa.nom'
        );
        foreach ($rows as $row) {
            $grouped[(string) $row['categorie']][] = $row;
        }
        return $grouped;
    }

    /** @return list<array<string, mixed>> */
    public static function options(): array
    {
        return Database::all(
            "SELECT id, nom, sigle, categorie FROM partenaires
             ORDER BY FIELD(categorie, 'financier', 'technique', 'national', 'international'), nom"
        );
    }

    /** @return array<string, mixed>|null */
    public static function findWithLogo(int $id): ?array
    {
        return Database::first(
            'SELECT pa.*, m.fichier AS logo_fichier FROM partenaires pa LEFT JOIN medias m ON m.id = pa.logo_id WHERE pa.id = ?',
            [$id]
        );
    }

    public static function nameTaken(string $nom, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM partenaires WHERE nom = ?';
        $params = [$nom];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        return (int) Database::value($sql, $params) > 0;
    }

    public static function nextOrder(string $categorie): int
    {
        return (int) Database::value('SELECT COALESCE(MAX(ordre), 0) + 1 FROM partenaires WHERE categorie = ?', [$categorie]);
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

    /** @param list<int> $ids */
    public static function setPublished(array $ids, bool $published): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("UPDATE partenaires SET publie = ? WHERE id IN ({$in})", [$published ? 1 : 0, ...$ids])->rowCount();
    }

    /** @param list<int> $ids */
    public static function deleteMany(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("DELETE FROM partenaires WHERE id IN ({$in})", $ids)->rowCount();
    }

    public static function withoutLogo(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM partenaires WHERE logo_id IS NULL');
    }
}

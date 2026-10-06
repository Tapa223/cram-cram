<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Paginator;

final class Projet extends Model
{
    protected const TABLE = 'projets';

    public const STATUTS = ['planifie' => 'Planifié', 'en_cours' => 'En cours', 'termine' => 'Terminé'];

    /**
     * Liste d'administration filtrée et paginée.
     * @param array{q?: string, statut?: string, domaine?: int|null, partenaire?: int|null, publication?: string} $f
     * @return array{0: list<array<string, mixed>>, 1: Paginator}
     */
    public static function paginateAdmin(array $f, int $page, int $perPage = 15): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (($f['q'] ?? '') !== '') {
            $where[] = '(p.titre LIKE ? OR p.resume LIKE ? OR EXISTS (SELECT 1 FROM projet_partenaire x JOIN partenaires y ON y.id = x.partenaire_id WHERE x.projet_id = p.id AND (y.nom LIKE ? OR y.sigle LIKE ?)))';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $statut = $f['statut'] ?? '';
        if ($statut === 'non_precise') {
            $where[] = 'p.statut IS NULL';
        } elseif (isset(self::STATUTS[$statut])) {
            $where[] = 'p.statut = ?';
            $params[] = $statut;
        }
        if (!empty($f['domaine'])) {
            $where[] = 'p.domaine_id = ?';
            $params[] = $f['domaine'];
        }
        if (!empty($f['partenaire'])) {
            $where[] = 'EXISTS (SELECT 1 FROM projet_partenaire z WHERE z.projet_id = p.id AND z.partenaire_id = ?)';
            $params[] = $f['partenaire'];
        }
        if (($f['publication'] ?? '') === 'publie') {
            $where[] = 'p.publie = 1';
        } elseif (($f['publication'] ?? '') === 'brouillon') {
            $where[] = 'p.publie = 0';
        }
        $sqlWhere = implode(' AND ', $where);

        $total = (int) Database::value("SELECT COUNT(*) FROM projets p WHERE {$sqlWhere}", $params);
        $pager = new Paginator($total, $page, $perPage);
        $rows = Database::all(
            "SELECT p.*, d.titre AS domaine_titre, m.fichier AS image_fichier,
                (SELECT GROUP_CONCAT(COALESCE(pa.sigle, pa.nom) ORDER BY pa.nom SEPARATOR ', ')
                   FROM projet_partenaire pp JOIN partenaires pa ON pa.id = pp.partenaire_id WHERE pp.projet_id = p.id) AS bailleurs
             FROM projets p
             LEFT JOIN domaines_action d ON d.id = p.domaine_id
             LEFT JOIN medias m ON m.id = p.image_id
             WHERE {$sqlWhere}
             ORDER BY p.modifie_le DESC, p.id DESC
             LIMIT {$pager->perPage} OFFSET {$pager->offset}",
            $params
        );
        return [$rows, $pager];
    }

    /** @return array<string, int> */
    public static function countsByStatut(): array
    {
        $counts = ['tous' => 0, 'en_cours' => 0, 'planifie' => 0, 'termine' => 0, 'non_precise' => 0];
        foreach (Database::all('SELECT statut, COUNT(*) AS n FROM projets GROUP BY statut') as $row) {
            $key = $row['statut'] ?? 'non_precise';
            $counts[$key] = (int) $row['n'];
            $counts['tous'] += (int) $row['n'];
        }
        return $counts;
    }

    /** @return list<array<string, mixed>> */
    public static function published(string $statut = '', ?int $domaineId = null, int $limit = 0): array
    {
        $where = ['p.publie = 1'];
        $params = [];
        if (isset(self::STATUTS[$statut])) {
            $where[] = 'p.statut = ?';
            $params[] = $statut;
        }
        if ($domaineId !== null) {
            $where[] = 'p.domaine_id = ?';
            $params[] = $domaineId;
        }
        $limitSql = $limit > 0 ? ' LIMIT ' . $limit : '';
        return Database::all(
            'SELECT p.*, d.titre AS domaine_titre, d.slug AS domaine_slug, m.fichier AS image_fichier, m.texte_alt AS image_alt,
                (SELECT GROUP_CONCAT(COALESCE(pa.sigle, pa.nom) ORDER BY pa.nom SEPARATOR \', \')
                   FROM projet_partenaire pp JOIN partenaires pa ON pa.id = pp.partenaire_id WHERE pp.projet_id = p.id) AS bailleurs
             FROM projets p
             LEFT JOIN domaines_action d ON d.id = p.domaine_id
             LEFT JOIN medias m ON m.id = p.image_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY COALESCE(p.date_debut, DATE(p.cree_le)) DESC, p.id DESC' . $limitSql,
            $params
        );
    }

    /** @return array<string, mixed>|null */
    public static function findPublishedBySlug(string $slug): ?array
    {
        return Database::first(
            'SELECT p.*, d.titre AS domaine_titre, d.slug AS domaine_slug, m.fichier AS image_fichier, m.texte_alt AS image_alt
             FROM projets p
             LEFT JOIN domaines_action d ON d.id = p.domaine_id
             LEFT JOIN medias m ON m.id = p.image_id
             WHERE p.slug = ? AND p.publie = 1',
            [$slug]
        );
    }

    /** @return array<string, mixed>|null */
    public static function findWithMedia(int $id): ?array
    {
        return Database::first(
            'SELECT p.*, m.fichier AS image_fichier, m.texte_alt AS image_alt, a.nom AS auteur_nom
             FROM projets p LEFT JOIN medias m ON m.id = p.image_id LEFT JOIN admin_users a ON a.id = p.auteur_id
             WHERE p.id = ?',
            [$id]
        );
    }

    /** @return list<array<string, mixed>> */
    public static function partners(int $id): array
    {
        return Database::all(
            'SELECT pa.* FROM projet_partenaire pp JOIN partenaires pa ON pa.id = pp.partenaire_id WHERE pp.projet_id = ? ORDER BY pa.nom',
            [$id]
        );
    }

    /** @return list<int> */
    public static function partnerIds(int $id): array
    {
        return array_map('intval', array_column(Database::all('SELECT partenaire_id FROM projet_partenaire WHERE projet_id = ?', [$id]), 'partenaire_id'));
    }

    /** @param list<int> $partnerIds */
    public static function syncPartners(int $id, array $partnerIds): void
    {
        Database::query('DELETE FROM projet_partenaire WHERE projet_id = ?', [$id]);
        foreach ($partnerIds as $partnerId) {
            Database::query(
                'INSERT IGNORE INTO projet_partenaire (projet_id, partenaire_id) SELECT ?, id FROM partenaires WHERE id = ?',
                [$id, $partnerId]
            );
        }
    }

    /** @return list<array<string, mixed>> */
    public static function others(int $id, int $limit = 3): array
    {
        return Database::all(
            'SELECT p.titre, p.slug, p.resume, m.fichier AS image_fichier, m.texte_alt AS image_alt
             FROM projets p LEFT JOIN medias m ON m.id = p.image_id
             WHERE p.publie = 1 AND p.id <> ? ORDER BY p.modifie_le DESC LIMIT ' . max(1, $limit),
            [$id]
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

    /** @param list<int> $ids */
    public static function setPublished(array $ids, bool $published): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("UPDATE projets SET publie = ? WHERE id IN ({$in})", [$published ? 1 : 0, ...$ids])->rowCount();
    }

    /** @param list<int> $ids */
    public static function deleteMany(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("DELETE FROM projets WHERE id IN ({$in})", $ids)->rowCount();
    }

    public static function withoutImage(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM projets WHERE image_id IS NULL');
    }
}

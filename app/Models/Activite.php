<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Paginator;

final class Activite extends Model
{
    protected const TABLE = 'activites';

    /** Catégories proposées ; une autre valeur peut être saisie librement. */
    public const CATEGORIES = ['Formation', 'Atelier', 'Rencontre communautaire', 'Campagne', 'Événement', 'Publication', 'Communiqué'];

    /**
     * @param array{q?: string, statut?: string, categorie?: string} $f
     * @return array{0: list<array<string, mixed>>, 1: Paginator}
     */
    public static function paginateAdmin(array $f, int $page, int $perPage = 15): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (($f['q'] ?? '') !== '') {
            $where[] = '(a.titre LIKE ? OR a.lieu LIKE ? OR a.categorie LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (in_array($f['statut'] ?? '', ['brouillon', 'publie'], true)) {
            $where[] = 'a.statut = ?';
            $params[] = $f['statut'];
        }
        if (($f['categorie'] ?? '') !== '') {
            $where[] = 'a.categorie = ?';
            $params[] = $f['categorie'];
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::value("SELECT COUNT(*) FROM activites a WHERE {$sqlWhere}", $params);
        $pager = new Paginator($total, $page, $perPage);
        $rows = Database::all(
            "SELECT a.*, d.titre AS domaine_titre, m.fichier AS image_fichier
             FROM activites a
             LEFT JOIN domaines_action d ON d.id = a.domaine_id
             LEFT JOIN medias m ON m.id = a.image_id
             WHERE {$sqlWhere}
             ORDER BY a.date_activite DESC, a.id DESC
             LIMIT {$pager->perPage} OFFSET {$pager->offset}",
            $params
        );
        return [$rows, $pager];
    }

    /** @return array<string, int> */
    public static function countsByStatut(): array
    {
        $counts = ['tous' => 0, 'publie' => 0, 'brouillon' => 0];
        foreach (Database::all('SELECT statut, COUNT(*) AS n FROM activites GROUP BY statut') as $row) {
            $counts[(string) $row['statut']] = (int) $row['n'];
            $counts['tous'] += (int) $row['n'];
        }
        return $counts;
    }

    /** @return list<string> */
    public static function usedCategories(bool $publishedOnly = false): array
    {
        $sql = 'SELECT DISTINCT categorie FROM activites' . ($publishedOnly ? " WHERE statut = 'publie'" : '') . ' ORDER BY categorie';
        return array_map('strval', array_column(Database::all($sql), 'categorie'));
    }

    /** @return array{0: list<array<string, mixed>>, 1: Paginator} */
    public static function paginatePublished(string $categorie, int $page, int $perPage = 9): array
    {
        $where = "a.statut = 'publie'";
        $params = [];
        if ($categorie !== '') {
            $where .= ' AND a.categorie = ?';
            $params[] = $categorie;
        }
        $total = (int) Database::value("SELECT COUNT(*) FROM activites a WHERE {$where}", $params);
        $pager = new Paginator($total, $page, $perPage);
        $rows = Database::all(
            "SELECT a.*, m.fichier AS image_fichier, m.texte_alt AS image_alt
             FROM activites a LEFT JOIN medias m ON m.id = a.image_id
             WHERE {$where} ORDER BY a.date_activite DESC, a.id DESC
             LIMIT {$pager->perPage} OFFSET {$pager->offset}",
            $params
        );
        return [$rows, $pager];
    }

    /** @return list<array<string, mixed>> */
    public static function latestPublished(int $limit = 3, ?int $domaineId = null, ?int $exceptId = null): array
    {
        $where = "a.statut = 'publie'";
        $params = [];
        if ($domaineId !== null) {
            $where .= ' AND a.domaine_id = ?';
            $params[] = $domaineId;
        }
        if ($exceptId !== null) {
            $where .= ' AND a.id <> ?';
            $params[] = $exceptId;
        }
        return Database::all(
            "SELECT a.*, m.fichier AS image_fichier, m.texte_alt AS image_alt
             FROM activites a LEFT JOIN medias m ON m.id = a.image_id
             WHERE {$where} ORDER BY a.date_activite DESC, a.id DESC LIMIT " . max(1, $limit),
            $params
        );
    }

    /** @return array<string, mixed>|null */
    public static function findPublishedBySlug(string $slug): ?array
    {
        return Database::first(
            "SELECT a.*, d.titre AS domaine_titre, d.slug AS domaine_slug, m.fichier AS image_fichier, m.texte_alt AS image_alt
             FROM activites a
             LEFT JOIN domaines_action d ON d.id = a.domaine_id
             LEFT JOIN medias m ON m.id = a.image_id
             WHERE a.slug = ? AND a.statut = 'publie'",
            [$slug]
        );
    }

    /** @return array<string, mixed>|null */
    public static function findWithMedia(int $id): ?array
    {
        return Database::first(
            'SELECT a.*, m.fichier AS image_fichier, m.texte_alt AS image_alt, u.nom AS auteur_nom
             FROM activites a LEFT JOIN medias m ON m.id = a.image_id LEFT JOIN admin_users u ON u.id = a.auteur_id
             WHERE a.id = ?',
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
    public static function setStatut(array $ids, string $statut): int
    {
        if ($ids === [] || !in_array($statut, ['brouillon', 'publie'], true)) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("UPDATE activites SET statut = ? WHERE id IN ({$in})", [$statut, ...$ids])->rowCount();
    }

    /** @param list<int> $ids */
    public static function deleteMany(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("DELETE FROM activites WHERE id IN ({$in})", $ids)->rowCount();
    }

    /** Nombre d'activités par mois sur les 12 derniers mois. @return array<string, int> clé AAAA-MM */
    public static function perMonth(int $months = 12): array
    {
        $rows = Database::all(
            "SELECT DATE_FORMAT(date_activite, '%Y-%m') AS mois, COUNT(*) AS n FROM activites
             WHERE date_activite >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL ? MONTH)
             GROUP BY mois",
            [$months - 1]
        );
        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['mois']] = (int) $row['n'];
        }
        return $out;
    }
}

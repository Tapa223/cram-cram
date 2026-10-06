<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Paginator;

final class Media extends Model
{
    protected const TABLE = 'medias';

    /** @return array{0: list<array<string, mixed>>, 1: Paginator} */
    public static function paginate(string $type, string $search, int $page, int $perPage = 24): array
    {
        [$where, $params] = self::filters($type, $search);
        $total = (int) Database::value("SELECT COUNT(*) FROM medias WHERE {$where}", $params);
        $paginator = new Paginator($total, $page, $perPage);
        $items = Database::all(
            "SELECT m.*, (
                (SELECT COUNT(*) FROM projets WHERE image_id = m.id) +
                (SELECT COUNT(*) FROM activites WHERE image_id = m.id) +
                (SELECT COUNT(*) FROM domaines_action WHERE image_id = m.id) +
                (SELECT COUNT(*) FROM partenaires WHERE logo_id = m.id) +
                (SELECT COUNT(*) FROM projet_medias WHERE media_id = m.id) +
                (SELECT COUNT(*) FROM domaine_medias WHERE media_id = m.id) +
                (SELECT COUNT(*) FROM activite_medias WHERE media_id = m.id)
             ) AS utilisations
             FROM medias m WHERE {$where} ORDER BY m.cree_le DESC, m.id DESC LIMIT {$paginator->perPage} OFFSET {$paginator->offset}",
            $params
        );
        return [$items, $paginator];
    }

    /** @return array{0: string, 1: list<string>} */
    private static function filters(string $type, string $search): array
    {
        $where = ['1 = 1'];
        $params = [];
        if ($type === 'images') {
            $where[] = "type_mime LIKE 'image/%'";
        } elseif ($type === 'documents') {
            $where[] = "type_mime = 'application/pdf'";
        }
        if ($search !== '') {
            $where[] = '(nom_original LIKE ? OR titre LIKE ? OR texte_alt LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like);
        }
        return [implode(' AND ', $where), $params];
    }

    /** Images récentes proposées dans le sélecteur des formulaires. @return list<array<string, mixed>> */
    public static function recentImages(int $limit = 48): array
    {
        return Database::all("SELECT id, fichier, nom_original, texte_alt FROM medias WHERE type_mime LIKE 'image/%' ORDER BY cree_le DESC, id DESC LIMIT " . max(1, $limit));
    }

    /** @return list<array<string, mixed>> */
    public static function publications(): array
    {
        return Database::all("SELECT * FROM medias WHERE est_publication = 1 AND type_mime = 'application/pdf' ORDER BY cree_le DESC");
    }

    public static function isImage(int $id): bool
    {
        return (int) Database::value("SELECT COUNT(*) FROM medias WHERE id = ? AND type_mime LIKE 'image/%'", [$id]) === 1;
    }

    public static function updateMeta(int $id, string $alt, string $title, bool $publication): void
    {
        Database::query(
            "UPDATE medias SET texte_alt = ?, titre = ?, est_publication = IF(type_mime = 'application/pdf', ?, 0) WHERE id = ?",
            [$alt !== '' ? $alt : null, $title !== '' ? $title : null, $publication ? 1 : 0, $id]
        );
    }

    public static function totalSize(): int
    {
        return (int) Database::value('SELECT COALESCE(SUM(taille), 0) FROM medias');
    }
}

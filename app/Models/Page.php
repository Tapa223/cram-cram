<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Pages éditoriales : la liste est fixe (créée par les contenus initiaux), seul leur contenu est modifiable.
 */
final class Page extends Model
{
    protected const TABLE = 'pages';

    public const ADRESSES = [
        'qui-sommes-nous'  => '/qui-sommes-nous',
        'recherche'        => '/recherche',
        'valeur-ajoutee'   => '/valeur-ajoutee',
        'mentions-legales' => '/mentions-legales',
    ];

    /** @return list<array<string, mixed>> */
    public static function allForAdmin(): array
    {
        return Database::all(
            "SELECT p.*, a.nom AS modifie_par_nom FROM pages p LEFT JOIN admin_users a ON a.id = p.modifie_par
             ORDER BY FIELD(p.slug, 'qui-sommes-nous', 'recherche', 'valeur-ajoutee', 'mentions-legales'), p.titre"
        );
    }

    /** @return array<string, mixed>|null */
    public static function findBySlug(string $slug): ?array
    {
        return Database::first('SELECT * FROM pages WHERE slug = ?', [$slug]);
    }

    public static function update(int $id, string $titre, ?string $chapo, string $contenu, ?string $meta, ?int $adminId): void
    {
        Database::query(
            'UPDATE pages SET titre = ?, chapo = ?, contenu = ?, meta_description = ?, modifie_par = ? WHERE id = ?',
            [$titre, $chapo, $contenu, $meta, $adminId, $id]
        );
    }
}

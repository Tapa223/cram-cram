<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use App\Core\Session;

/**
 * Journal des actions d'administration (alimente la zone « Activité récente »).
 */
final class ActivityLog
{
    public const LIBELLES_TYPES = [
        'activite'   => 'Activité',
        'projet'     => 'Projet',
        'domaine'    => 'Domaine',
        'partenaire' => 'Partenaire',
        'media'      => 'Média',
        'page'       => 'Page',
        'message'    => 'Message',
        'parametres' => 'Paramètres',
        'compte'     => 'Compte',
    ];

    public static function record(string $action, string $type, ?int $objectId, string $label): void
    {
        try {
            $adminId = Session::get('admin_id');
            Database::query(
                'INSERT INTO journal_activite (admin_id, action, type, objet_id, libelle) VALUES (?, ?, ?, ?, ?)',
                [is_int($adminId) ? $adminId : null, $action, $type, $objectId, mb_substr($label, 0, 255)]
            );
        } catch (\Throwable $e) {
            // Le journal ne doit jamais bloquer l'action principale.
            ErrorHandler::log($e);
        }
    }

    /** @return list<array<string, mixed>> */
    public static function latest(int $limit = 6): array
    {
        return Database::all(
            "SELECT j.*, a.nom AS admin_nom FROM journal_activite j
             LEFT JOIN admin_users a ON a.id = j.admin_id
             WHERE j.action <> 'connexion'
             ORDER BY j.cree_le DESC, j.id DESC LIMIT " . max(1, min(50, $limit))
        );
    }
}

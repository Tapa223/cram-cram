<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Vérifie le jeton CSRF de toute requête POST.
 * Cas particulier : un envoi plus lourd que post_max_size arrive vide ; on l'explique clairement.
 */
final class CsrfMiddleware
{
    public function handle(): void
    {
        if (!Request::isPost()) {
            return;
        }

        $length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($length > 0 && $_POST === [] && $_FILES === []) {
            Session::flash('erreur', 'Le fichier envoyé est trop volumineux pour le serveur. Réduisez sa taille puis réessayez.');
            Response::back('/admin');
        }

        if (!Csrf::valid(is_string($_POST['_csrf'] ?? null) ? $_POST['_csrf'] : null)) {
            Session::flash('erreur', 'Votre session a expiré ou le formulaire n\'était plus valide. Merci de réessayer.');
            Response::back('/');
        }
    }
}

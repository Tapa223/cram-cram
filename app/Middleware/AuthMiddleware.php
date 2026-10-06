<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Auth;

/**
 * Protège toutes les routes /admin (hors connexion).
 */
final class AuthMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            header('Cache-Control: no-store, private');
            $this->requirePasswordChange();
            return;
        }
        if (Request::method() === 'GET') {
            Session::set('_intended', Request::path());
        }
        Session::flash('info', 'Merci de vous connecter pour accéder à l\'administration.');
        Response::redirect('/admin/connexion');
    }

    /**
     * Compte avec mot de passe provisoire : seules la page « Mon compte »
     * et la déconnexion restent accessibles jusqu'au changement.
     */
    private function requirePasswordChange(): void
    {
        $user = Auth::user();
        if ((int) ($user['doit_changer_mdp'] ?? 0) !== 1) {
            return;
        }
        $path = Request::path();
        if (in_array($path, ['/admin/compte', '/admin/compte/mot-de-passe', '/admin/deconnexion'], true)) {
            return;
        }
        Session::flash('info', 'Pour sécuriser le compte, remplacez d\'abord le mot de passe provisoire.');
        Response::redirect('/admin/compte');
    }
}

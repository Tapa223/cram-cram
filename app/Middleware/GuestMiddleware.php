<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Services\Auth;

/**
 * Redirige un administrateur déjà connecté hors de la page de connexion.
 */
final class GuestMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            Response::redirect('/admin');
        }
    }
}

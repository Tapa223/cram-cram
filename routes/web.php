<?php

declare(strict_types=1);

/**
 * Routes du site public.
 * @var App\Core\Router $router
 */

use App\Controllers\ContactController;
use App\Controllers\HomeController;
use App\Controllers\SiteController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\TrackVisitMiddleware;

$router->group('', [TrackVisitMiddleware::class], static function ($r): void {
    $r->get('/', [HomeController::class, 'index']);
    $r->get('/qui-sommes-nous', [SiteController::class, 'quiSommesNous']);
    $r->get('/domaines-action', [SiteController::class, 'domaines']);
    $r->get('/domaines-action/{slug}', [SiteController::class, 'domaine']);
    $r->get('/projets', [SiteController::class, 'projets']);
    $r->get('/projets/{slug}', [SiteController::class, 'projet']);
    $r->get('/recherche', [SiteController::class, 'recherche']);
    $r->get('/valeur-ajoutee', [SiteController::class, 'valeurAjoutee']);
    $r->get('/actualites', [SiteController::class, 'actualites']);
    $r->get('/actualites/{slug}', [SiteController::class, 'actualite']);
    $r->get('/partenaires', [SiteController::class, 'partenaires']);
    $r->get('/mentions-legales', [SiteController::class, 'mentionsLegales']);
    $r->get('/contact', [ContactController::class, 'show']);
});

$router->post('/contact', [ContactController::class, 'send'], [CsrfMiddleware::class]);

<?php

declare(strict_types=1);

/**
 * Routes de l'administration. Toutes les requêtes POST sont protégées par CSRF,
 * toutes les routes (hors connexion) exigent un compte actif.
 * @var App\Core\Router $router
 */

use App\Controllers\Admin\ActiviteController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CompteController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DomaineController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\MessageController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\ParametreController;
use App\Controllers\Admin\PartenaireController;
use App\Controllers\Admin\ProjetController;
use App\Controllers\Admin\StatistiqueController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

$router->group('/admin', [CsrfMiddleware::class], static function ($r): void {
    $r->get('/connexion', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
    $r->post('/connexion', [AuthController::class, 'login'], [GuestMiddleware::class]);
});

$router->group('/admin', [AuthMiddleware::class, CsrfMiddleware::class], static function ($r): void {
    $r->post('/deconnexion', [AuthController::class, 'logout']);
    $r->get('', [DashboardController::class, 'index']);

    // Modules de contenu : liste, création, modification, suppression, actions groupées.
    $modules = [
        'activites'   => [ActiviteController::class, 'nouvelle'],
        'projets'     => [ProjetController::class, 'nouveau'],
        'domaines'    => [DomaineController::class, 'nouveau'],
        'partenaires' => [PartenaireController::class, 'nouveau'],
    ];
    foreach ($modules as $module => [$controller, $newSegment]) {
        $r->get('/' . $module, [$controller, 'index']);
        $r->get('/' . $module . '/' . $newSegment, [$controller, 'create']);
        $r->post('/' . $module, [$controller, 'store']);
        $r->get('/' . $module . '/{id}/modifier', [$controller, 'edit']);
        $r->post('/' . $module . '/{id}', [$controller, 'update']);
        $r->post('/' . $module . '/{id}/supprimer', [$controller, 'destroy']);
        $r->post('/' . $module . '/actions', [$controller, 'bulk']);
    }
    $r->post('/domaines/ordre', [DomaineController::class, 'reorder']);

    $r->get('/medias', [MediaController::class, 'index']);
    $r->post('/medias', [MediaController::class, 'store']);
    $r->post('/medias/{id}', [MediaController::class, 'update']);
    $r->post('/medias/{id}/supprimer', [MediaController::class, 'destroy']);

    $r->get('/pages', [PageController::class, 'index']);
    $r->get('/pages/{id}/modifier', [PageController::class, 'edit']);
    $r->post('/pages/{id}', [PageController::class, 'update']);

    $r->get('/messages', [MessageController::class, 'index']);
    $r->get('/messages/{id}', [MessageController::class, 'show']);
    $r->post('/messages/{id}/lu', [MessageController::class, 'toggleRead']);
    $r->post('/messages/{id}/supprimer', [MessageController::class, 'destroy']);
    $r->post('/messages/actions', [MessageController::class, 'bulk']);

    $r->get('/statistiques', [StatistiqueController::class, 'index']);

    $r->get('/parametres', [ParametreController::class, 'edit']);
    $r->post('/parametres', [ParametreController::class, 'update']);

    $r->get('/compte', [CompteController::class, 'edit']);
    $r->post('/compte', [CompteController::class, 'updateProfile']);
    $r->post('/compte/mot-de-passe', [CompteController::class, 'updatePassword']);
});

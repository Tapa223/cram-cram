<?php

declare(strict_types=1);

/**
 * Contrôleur frontal : unique point d'entrée web de l'application.
 */

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;

Response::securityHeaders();
Session::start();

$router = new Router();
require APP_ROOT . '/routes/web.php';
require APP_ROOT . '/routes/admin.php';

$router->dispatch(Request::method(), Request::path());

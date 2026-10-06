<?php

declare(strict_types=1);

/**
 * Initialisation commune (web et ligne de commande).
 */

define('APP_ROOT', dirname(__DIR__));

// Autoload : Composer s'il est installé, sinon chargeur PSR-4 intégré (aucune dépendance requise).
if (is_file(APP_ROOT . '/vendor/autoload.php')) {
    require APP_ROOT . '/vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class): void {
        if (!str_starts_with($class, 'App\\')) {
            return;
        }
        $file = APP_ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

require APP_ROOT . '/app/helpers.php';

date_default_timezone_set('Africa/Bamako');
mb_internal_encoding('UTF-8');

if (!is_file(APP_ROOT . '/.env')) {
    // Première installation : message explicite plutôt qu'une page blanche.
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Fichier .env introuvable : copiez .env.example en .env puis renseignez-le.\n");
        exit(1);
    }
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><meta charset="utf-8"><title>Configuration requise</title>'
        . '<p style="font-family:sans-serif;max-width:560px;margin:80px auto;line-height:1.6">'
        . '<strong>Configuration requise.</strong><br>Copiez le fichier <code>.env.example</code> en <code>.env</code> '
        . 'à la racine du projet, puis renseignez les accès à la base de données (voir README.md).</p>';
    exit;
}

App\Core\Config::load(APP_ROOT . '/.env');
App\Core\ErrorHandler::register();

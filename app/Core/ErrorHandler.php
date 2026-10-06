<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Journalise toutes les erreurs dans storage/logs et n'affiche jamais de détail technique
 * (SQLSTATE, chemins, traces) à l'utilisateur, sauf si APP_DEBUG=true en local.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', APP_ROOT . '/storage/logs/php-' . date('Y-m') . '.log');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);
    }

    public static function log(\Throwable $e): void
    {
        $line = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        @file_put_contents(APP_ROOT . '/storage/logs/app-' . date('Y-m') . '.log', $line, FILE_APPEND | LOCK_EX);
    }

    public static function handle(\Throwable $e): void
    {
        $status = $e instanceof HttpException ? $e->status : 500;
        if ($status >= 500) {
            self::log($e);
        }

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $e->getMessage() . PHP_EOL);
            exit(1);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: text/html; charset=UTF-8');
        }

        $isAdmin = str_starts_with(Request::path(), '/admin');
        $debug = Config::bool('APP_DEBUG') && !Config::isProduction();

        try {
            echo View::render('errors/' . ($status === 404 ? '404' : ($status === 403 ? '403' : '500')), [
                'titrePage' => $status === 404 ? 'Page introuvable' : 'Une erreur est survenue',
                'debug'     => $debug ? $e : null,
                'isAdmin'   => $isAdmin,
            ], 'layouts/error');
        } catch (\Throwable $renderError) {
            self::log($renderError);
            echo '<!doctype html><meta charset="utf-8"><title>Erreur</title><p>Une erreur est survenue. Merci de réessayer plus tard.</p>';
        }
    }
}

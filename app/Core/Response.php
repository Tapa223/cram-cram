<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /**
     * En-têtes de sécurité envoyés par PHP : ils s'appliquent aussi sous XAMPP,
     * même si mod_headers n'est pas actif.
     */
    public static function securityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header(
            "Content-Security-Policy: default-src 'self'; img-src 'self' data:; "
            . "style-src 'self'; font-src 'self'; "
            . "script-src 'self'; form-action 'self'; frame-ancestors 'self'; base-uri 'self'; object-src 'none'"
        );
        if (Request::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000');
        }
    }

    public static function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . url($path), true, $status);
        exit;
    }

    public static function back(string $fallback = '/'): never
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if ($referer !== '' && $host !== '' && parse_url($referer, PHP_URL_HOST) === parse_url('http://' . $host, PHP_URL_HOST)) {
            header('Location: ' . $referer, true, 302);
            exit;
        }
        self::redirect($fallback);
    }

    /** @param array<string, mixed> $data */
    public static function view(string $view, array $data = [], ?string $layout = null, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        echo View::render($view, $data, $layout);
    }
}

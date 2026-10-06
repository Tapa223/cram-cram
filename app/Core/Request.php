<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Requête HTTP courante : méthode, chemin (sans le dossier d'installation) et contexte client.
 */
final class Request
{
    private static ?string $basePath = null;

    /**
     * Dossier d'installation : '' à la racine (LWS), '/cramcram' sous XAMPP.
     * Fonctionne que l'on passe par /cramcram/public/ ou par la réécriture de la racine.
     */
    public static function basePath(): string
    {
        if (self::$basePath !== null) {
            return self::$basePath;
        }

        $configured = trim((string) Config::get('APP_BASE_PATH', ''));
        if ($configured !== '') {
            return self::$basePath = '/' . trim($configured, '/');
        }

        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $uri = self::rawPath();
        $candidates = [$scriptDir];
        if (str_ends_with($scriptDir, '/public')) {
            $candidates[] = substr($scriptDir, 0, -strlen('/public'));
        }
        foreach ($candidates as $candidate) {
            if ($candidate === '' || $uri === $candidate || str_starts_with($uri, $candidate . '/')) {
                return self::$basePath = $candidate;
            }
        }
        return self::$basePath = end($candidates) ?: '';
    }

    private static function rawPath(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return rawurldecode(is_string($path) && $path !== '' ? $path : '/');
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /** Chemin applicatif normalisé, ex. « /projets/mon-projet ». */
    public static function path(): string
    {
        $path = self::rawPath();
        $base = self::basePath();
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
        }
        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php'));
        }
        $path = '/' . trim($path, '/');
        return $path;
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
            || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public static function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public static function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session durcie : cookie HttpOnly/SameSite, mode strict, expiration par inactivité,
 * messages flash et conservation des saisies après une erreur de validation.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        session_name('cramcram_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => Request::basePath() === '' ? '/' : Request::basePath() . '/',
            'secure'   => Request::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        $timeout = Config::int('SESSION_IDLE_MINUTES', 120) * 60;
        $last = $_SESSION['_last_activity'] ?? null;
        if (is_int($last) && (time() - $last) > $timeout) {
            session_unset();
            session_regenerate_id(true);
        }
        $_SESSION['_last_activity'] = time();

        // Les données "flash" et "old" ne vivent que pour la requête suivante.
        $_SESSION['_flash_now'] = $_SESSION['_flash_next'] ?? [];
        $_SESSION['_old_now'] = $_SESSION['_old_next'] ?? [];
        $_SESSION['_errors_now'] = $_SESSION['_errors_next'] ?? [];
        unset($_SESSION['_flash_next'], $_SESSION['_old_next'], $_SESSION['_errors_next']);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'secure'   => $params['secure'],
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
    }

    /** Message affiché une seule fois (type : succes, erreur, info). */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash_next'][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type: string, message: string}> */
    public static function flashes(): array
    {
        return $_SESSION['_flash_now'] ?? [];
    }

    /** @param array<string, mixed> $input @param array<string, string> $errors */
    public static function withInput(array $input, array $errors = []): void
    {
        unset($input['_csrf'], $input['mot_de_passe'], $input['mot_de_passe_actuel'], $input['nouveau_mot_de_passe'], $input['confirmation']);
        $_SESSION['_old_next'] = $input;
        $_SESSION['_errors_next'] = $errors;
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old_now'][$key] ?? $default;
    }

    public static function hasOld(): bool
    {
        return !empty($_SESSION['_old_now']);
    }

    /** @return array<string, string> */
    public static function errors(): array
    {
        return $_SESSION['_errors_now'] ?? [];
    }
}

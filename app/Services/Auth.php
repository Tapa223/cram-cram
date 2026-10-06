<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

/**
 * Authentification de l'administration.
 * - message d'erreur identique que le compte existe ou non ;
 * - temps de réponse homogène (vérification factice si le compte n'existe pas) ;
 * - verrouillage temporaire du compte après plusieurs échecs ;
 * - limitation par adresse réseau (empreinte HMAC, jamais l'IP en clair) ;
 * - compte revérifié en base à chaque requête (un compte désactivé est déconnecté).
 */
final class Auth
{
    private const IP_MAX_ATTEMPTS = 20;
    private const IP_WINDOW_MINUTES = 15;
    private const DUMMY_HASH = '$2y$12$MEnD8kx2J99TmfWBf4pPmecIGfa4hXyiSA5BTSBZElXJ6YBzbq..a';

    /** @var array<string, mixed>|null|false */
    private static array|null|false $user = false;

    /** @return array{ok: bool, message: string} */
    public static function attempt(string $email, string $password): array
    {
        $generic = ['ok' => false, 'message' => 'Adresse e-mail ou mot de passe incorrect.'];
        $ipKey = hash_hmac('sha256', Request::ip(), (string) Config::get('APP_KEY', 'cramcram'));

        Database::query('DELETE FROM tentatives_connexion WHERE cree_le < (UTC_TIMESTAMP() - INTERVAL 1 DAY)');
        $recent = (int) Database::value(
            'SELECT COUNT(*) FROM tentatives_connexion WHERE cle = ? AND cree_le > (UTC_TIMESTAMP() - INTERVAL ? MINUTE)',
            [$ipKey, self::IP_WINDOW_MINUTES]
        );
        if ($recent >= self::IP_MAX_ATTEMPTS) {
            return ['ok' => false, 'message' => 'Trop de tentatives de connexion. Merci de patienter quelques minutes avant de réessayer.'];
        }

        $user = Database::first('SELECT * FROM admin_users WHERE email = ? LIMIT 1', [strtolower($email)]);

        if (!$user) {
            password_verify($password, self::DUMMY_HASH);
            Database::query('INSERT INTO tentatives_connexion (cle) VALUES (?)', [$ipKey]);
            return $generic;
        }

        if (!empty($user['verrouille_jusqu_a']) && strtotime((string) $user['verrouille_jusqu_a'] . ' UTC') > time()) {
            password_verify($password, self::DUMMY_HASH);
            Database::query('INSERT INTO tentatives_connexion (cle) VALUES (?)', [$ipKey]);
            return ['ok' => false, 'message' => 'Trop de tentatives de connexion. Merci de patienter quelques minutes avant de réessayer.'];
        }

        if (!password_verify($password, (string) $user['mot_de_passe_hash']) || (int) $user['actif'] !== 1) {
            $failures = (int) $user['tentatives_echouees'] + 1;
            $max = Config::int('LOGIN_MAX_ATTEMPTS', 5);
            if ($failures >= $max) {
                Database::query(
                    'UPDATE admin_users SET tentatives_echouees = 0, verrouille_jusqu_a = (UTC_TIMESTAMP() + INTERVAL ? MINUTE) WHERE id = ?',
                    [Config::int('LOGIN_LOCK_MINUTES', 15), $user['id']]
                );
            } else {
                Database::query('UPDATE admin_users SET tentatives_echouees = ? WHERE id = ?', [$failures, $user['id']]);
            }
            Database::query('INSERT INTO tentatives_connexion (cle) VALUES (?)', [$ipKey]);
            return $generic;
        }

        if (password_needs_rehash((string) $user['mot_de_passe_hash'], PASSWORD_DEFAULT)) {
            Database::query('UPDATE admin_users SET mot_de_passe_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }

        Database::query(
            'UPDATE admin_users SET tentatives_echouees = 0, verrouille_jusqu_a = NULL, derniere_connexion = UTC_TIMESTAMP() WHERE id = ?',
            [$user['id']]
        );

        Session::regenerate();
        Session::set('admin_id', (int) $user['id']);
        self::$user = false;
        ActivityLog::record('connexion', 'compte', (int) $user['id'], 'Connexion à l\'administration');

        return ['ok' => true, 'message' => ''];
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        if (self::$user !== false) {
            return self::$user;
        }
        $id = Session::get('admin_id');
        if (!is_int($id)) {
            return self::$user = null;
        }
        $user = Database::first('SELECT id, nom, email, role, actif, doit_changer_mdp, derniere_connexion FROM admin_users WHERE id = ?', [$id]);
        if (!$user || (int) $user['actif'] !== 1) {
            Session::forget('admin_id');
            return self::$user = null;
        }
        return self::$user = $user;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function logout(): void
    {
        self::$user = false;
        Session::destroy();
    }

    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials !== '' ? $initials : 'AD';
    }
}

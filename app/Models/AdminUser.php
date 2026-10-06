<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AdminUser extends Model
{
    protected const TABLE = 'admin_users';

    public static function emailTaken(string $email, int $ignoreId): bool
    {
        return (int) Database::value('SELECT COUNT(*) FROM admin_users WHERE email = ? AND id <> ?', [$email, $ignoreId]) > 0;
    }

    public static function updateProfile(int $id, string $nom, string $email): void
    {
        Database::query('UPDATE admin_users SET nom = ?, email = ? WHERE id = ?', [$nom, $email, $id]);
    }

    public static function passwordMatches(int $id, string $password): bool
    {
        $hash = Database::value('SELECT mot_de_passe_hash FROM admin_users WHERE id = ?', [$id]);
        return is_string($hash) && password_verify($password, $hash);
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::query('UPDATE admin_users SET mot_de_passe_hash = ?, doit_changer_mdp = 0 WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
    }
}

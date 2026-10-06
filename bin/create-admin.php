<?php

declare(strict_types=1);

/**
 * Crée (ou réinitialise) le compte administrateur, en ligne de commande uniquement.
 * Le mot de passe est saisi au clavier : il n'apparaît ni dans le code ni dans l'historique.
 *
 *   php bin/create-admin.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

function ask(string $question, bool $hidden = false): string
{
    echo $question;
    if ($hidden && DIRECTORY_SEPARATOR === '/' && function_exists('shell_exec')) {
        shell_exec('stty -echo 2>/dev/null');
        $value = trim((string) fgets(STDIN));
        shell_exec('stty echo 2>/dev/null');
        echo PHP_EOL;
        return $value;
    }
    return trim((string) fgets(STDIN));
}

$nom = ask('Nom affiché : ');
$email = strtolower(ask('Adresse e-mail de connexion : '));
$password = ask('Mot de passe (12 caractères minimum) : ', true);
$confirm = ask('Confirmez le mot de passe : ', true);

if ($nom === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "Nom ou adresse e-mail invalide." . PHP_EOL);
    exit(1);
}
if (mb_strlen($password) < 12) {
    fwrite(STDERR, "Le mot de passe doit contenir au moins 12 caractères." . PHP_EOL);
    exit(1);
}
if ($password !== $confirm) {
    fwrite(STDERR, "Les deux mots de passe ne correspondent pas." . PHP_EOL);
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$existing = Database::first('SELECT id FROM admin_users WHERE email = ?', [$email]);

if ($existing) {
    Database::query(
        'UPDATE admin_users SET nom = ?, mot_de_passe_hash = ?, actif = 1, tentatives_echouees = 0, verrouille_jusqu_a = NULL WHERE id = ?',
        [$nom, $hash, $existing['id']]
    );
    echo "Compte mis à jour : {$email}" . PHP_EOL;
} else {
    Database::query(
        "INSERT INTO admin_users (nom, email, mot_de_passe_hash, role) VALUES (?, ?, ?, 'admin')",
        [$nom, $email, $hash]
    );
    echo "Compte administrateur créé : {$email}" . PHP_EOL;
}

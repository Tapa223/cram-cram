<?php

declare(strict_types=1);

/**
 * Applique les migrations SQL numérotées qui ne l'ont pas encore été.
 *
 *   php bin/migrate.php            migrations seules
 *   php bin/migrate.php --seed     migrations + contenus initiaux (idempotent)
 *   php bin/migrate.php --status   liste des migrations appliquées / en attente
 *
 * Une migration déjà appliquée n'est jamais rejouée ni modifiée.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

$pdo = Database::connection();
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        migration VARCHAR(190) NOT NULL PRIMARY KEY,
        appliquee_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$applied = array_column(Database::all('SELECT migration FROM schema_migrations'), 'migration');
$files = glob(APP_ROOT . '/database/migrations/*.sql') ?: [];
sort($files, SORT_STRING);

if (in_array('--status', $argv, true)) {
    foreach ($files as $file) {
        $name = basename($file);
        echo (in_array($name, $applied, true) ? '[appliquée] ' : '[en attente] ') . $name . PHP_EOL;
    }
    exit(0);
}

$count = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        continue;
    }
    echo "Application de {$name}… ";
    run_sql_file($pdo, $file);
    Database::query('INSERT INTO schema_migrations (migration) VALUES (?)', [$name]);
    echo "OK" . PHP_EOL;
    $count++;
}
echo $count === 0 ? "Base à jour, aucune migration en attente." . PHP_EOL : "{$count} migration(s) appliquée(s)." . PHP_EOL;

if (in_array('--seed', $argv, true)) {
    foreach (glob(APP_ROOT . '/database/seeds/*.sql') ?: [] as $seed) {
        echo 'Contenus initiaux : ' . basename($seed) . '… ';
        run_sql_file($pdo, $seed);
        echo 'OK' . PHP_EOL;
    }
}

/**
 * Exécute un fichier SQL instruction par instruction (séparateur « ; » en fin de ligne).
 */
function run_sql_file(PDO $pdo, string $file): void
{
    $sql = (string) file_get_contents($file);
    $lines = preg_split('/\R/', $sql) ?: [];
    $buffer = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }
        $buffer .= $line . "\n";
        if (str_ends_with($trimmed, ';')) {
            $pdo->exec($buffer);
            $buffer = '';
        }
    }
    if (trim($buffer) !== '') {
        $pdo->exec($buffer);
    }
}

<?php

declare(strict_types=1);

/**
 * Regroupe migrations + contenus initiaux dans database/installation_complete.sql,
 * pour une première installation par import dans phpMyAdmin.
 * Le fichier enregistre aussi les migrations comme appliquées : bin/migrate.php
 * reste utilisable ensuite pour les évolutions.
 *
 *   php bin/build-install-sql.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$out = [];
$out[] = '-- ============================================================';
$out[] = '-- CRAM-CRAM Mali — installation initiale (généré par bin/build-install-sql.php)';
$out[] = '-- À importer dans une base VIDE, créée au préalable dans phpMyAdmin';
$out[] = '-- (interclassement utf8mb4_unicode_ci). Ne pas importer sur une base existante :';
$out[] = '-- pour les évolutions, utiliser les fichiers database_update fournis séparément.';
$out[] = '-- ============================================================';
$out[] = 'SET NAMES utf8mb4;';
$out[] = '';
$out[] = 'CREATE TABLE IF NOT EXISTS schema_migrations (';
$out[] = '    migration VARCHAR(190) NOT NULL PRIMARY KEY,';
$out[] = '    appliquee_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';
$out[] = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
$out[] = '';

$migrations = glob($root . '/database/migrations/*.sql') ?: [];
sort($migrations, SORT_STRING);
foreach ($migrations as $file) {
    $out[] = trim((string) file_get_contents($file));
    $out[] = "INSERT IGNORE INTO schema_migrations (migration) VALUES ('" . basename($file) . "');";
    $out[] = '';
}
foreach (glob($root . '/database/seeds/*.sql') ?: [] as $file) {
    $out[] = trim((string) file_get_contents($file));
    $out[] = '';
}

$contenu = implode("\n", $out);
file_put_contents($root . '/database/installation_complete.sql', $contenu);

// Variante autonome : crée aussi la base « cramcram » (import sans sélectionner de base).
$entete = <<<SQL
-- ============================================================
-- CRAM-CRAM Mali — installation complète en un seul fichier
-- Crée la base « cramcram », les tables, les contenus initiaux
-- et le compte administrateur initial (mot de passe provisoire haché,
-- à changer obligatoirement à la première connexion).
--
-- phpMyAdmin : NE sélectionnez aucune base, cliquez directement sur
-- l'onglet « Importer » en haut, choisissez ce fichier, puis « Importer ».
--
-- Si une base « cramcram » existe déjà avec des tables, ne pas importer
-- ce fichier : supprimez-la d'abord, ou changez le nom ci-dessous
-- (et DB_NAME dans le fichier .env).
-- ============================================================

CREATE DATABASE IF NOT EXISTS cramcram
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE cramcram;


SQL;
file_put_contents($root . '/database/cramcram_installation.sql', $entete . $contenu);
echo "database/installation_complete.sql et database/cramcram_installation.sql générés." . PHP_EOL;

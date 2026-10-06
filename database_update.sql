-- ============================================================
-- CRAM-CRAM Mali — mise à jour d'une base existante (idempotent)
-- À importer dans phpMyAdmin sur la base « cramcram » déjà installée.
-- Peut être exécuté plusieurs fois sans effet indésirable.
-- Contenu : migration 014 (mot de passe provisoire), compte administrateur initial,
-- migration 015 (galeries photos).
-- Aucune table ni donnée existante n'est supprimée.
-- ============================================================
SET NAMES utf8mb4;

-- 014 : colonne doit_changer_mdp (changement de mot de passe obligatoire).
-- Ajoutée seulement si elle n'existe pas encore (compatible MySQL et MariaDB).
SET @col_existe := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users' AND COLUMN_NAME = 'doit_changer_mdp'
);
SET @sql := IF(@col_existe = 0,
    'ALTER TABLE admin_users ADD COLUMN doit_changer_mdp TINYINT(1) NOT NULL DEFAULT 0 AFTER actif',
    'SELECT ''colonne doit_changer_mdp déjà présente''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('014_add_doit_changer_mdp_to_admin_users.sql');

-- Compte administrateur initial (mot de passe provisoire haché, à changer à la
-- première connexion). Ignoré si un compte utilise déjà cette adresse.
INSERT IGNORE INTO admin_users (nom, email, mot_de_passe_hash, role, actif, doit_changer_mdp)
VALUES ('Administrateur CRAM-CRAM', 'admin@cramcram.org', '$2y$12$mVoEHnkAmrIxEysSA.7Gr.4eUYppkD0/YUsvGXtjDnYahMBMPEsJu', 'admin', 1, 1);

-- 015 : galeries photos (projets, domaines d'action, activités).
-- CREATE TABLE IF NOT EXISTS : sans effet si les tables existent déjà.
CREATE TABLE IF NOT EXISTS projet_medias (
    projet_id INT UNSIGNED NOT NULL,
    media_id  INT UNSIGNED NOT NULL,
    ordre     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ajoute_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (projet_id, media_id),
    KEY idx_projet_medias_media (media_id),
    CONSTRAINT fk_projet_medias_projet FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    CONSTRAINT fk_projet_medias_media FOREIGN KEY (media_id) REFERENCES medias (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS domaine_medias (
    domaine_id INT UNSIGNED NOT NULL,
    media_id   INT UNSIGNED NOT NULL,
    ordre      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ajoute_le  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (domaine_id, media_id),
    KEY idx_domaine_medias_media (media_id),
    CONSTRAINT fk_domaine_medias_domaine FOREIGN KEY (domaine_id) REFERENCES domaines_action (id) ON DELETE CASCADE,
    CONSTRAINT fk_domaine_medias_media FOREIGN KEY (media_id) REFERENCES medias (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activite_medias (
    activite_id INT UNSIGNED NOT NULL,
    media_id    INT UNSIGNED NOT NULL,
    ordre       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ajoute_le   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (activite_id, media_id),
    KEY idx_activite_medias_media (media_id),
    CONSTRAINT fk_activite_medias_activite FOREIGN KEY (activite_id) REFERENCES activites (id) ON DELETE CASCADE,
    CONSTRAINT fk_activite_medias_media FOREIGN KEY (media_id) REFERENCES medias (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('015_create_galeries.sql');

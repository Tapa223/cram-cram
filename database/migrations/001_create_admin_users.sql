-- 001 : comptes d'administration
-- Un seul compte en v1, colonne "role" prévue pour une extension ultérieure.
CREATE TABLE IF NOT EXISTS admin_users (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom                 VARCHAR(120) NOT NULL,
    email               VARCHAR(190) NOT NULL,
    mot_de_passe_hash   VARCHAR(255) NOT NULL,
    role                ENUM('admin', 'editeur') NOT NULL DEFAULT 'admin',
    actif               TINYINT(1) NOT NULL DEFAULT 1,
    derniere_connexion  DATETIME NULL,
    tentatives_echouees SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    verrouille_jusqu_a  DATETIME NULL,
    cree_le             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

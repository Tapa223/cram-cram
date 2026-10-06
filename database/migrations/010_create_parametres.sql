-- 010 : paramètres du site (clé / valeur)
CREATE TABLE IF NOT EXISTS parametres (
    cle        VARCHAR(80) NOT NULL,
    valeur     TEXT NULL,
    modifie_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

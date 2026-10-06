-- 003 : domaines d'action
CREATE TABLE IF NOT EXISTS domaines_action (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titre         VARCHAR(180) NOT NULL,
    slug          VARCHAR(190) NOT NULL,
    resume        VARCHAR(400) NOT NULL,
    description   MEDIUMTEXT NULL,
    image_id      INT UNSIGNED NULL,
    ordre         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    mis_en_avant  TINYINT(1) NOT NULL DEFAULT 0,
    publie        TINYINT(1) NOT NULL DEFAULT 1,
    cree_le       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_domaines_slug (slug),
    KEY idx_domaines_publie_ordre (publie, ordre),
    CONSTRAINT fk_domaines_image FOREIGN KEY (image_id) REFERENCES medias (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

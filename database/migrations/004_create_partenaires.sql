-- 004 : partenaires et bailleurs (4 catégories du document de contenus)
CREATE TABLE IF NOT EXISTS partenaires (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom          VARCHAR(200) NOT NULL,
    sigle        VARCHAR(60) NULL,
    categorie    ENUM('financier', 'technique', 'national', 'international') NOT NULL,
    description  VARCHAR(400) NULL,
    pays         VARCHAR(80) NULL,
    site_web     VARCHAR(255) NULL,
    logo_id      INT UNSIGNED NULL,
    ordre        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    publie       TINYINT(1) NOT NULL DEFAULT 1,
    cree_le      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_partenaires_nom (nom),
    KEY idx_partenaires_categorie (categorie, publie, ordre),
    CONSTRAINT fk_partenaires_logo FOREIGN KEY (logo_id) REFERENCES medias (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

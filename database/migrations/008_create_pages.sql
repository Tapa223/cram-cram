-- 008 : pages éditoriales éditables (qui sommes-nous, recherche, valeur ajoutée, mentions légales)
CREATE TABLE IF NOT EXISTS pages (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug             VARCHAR(80) NOT NULL,
    titre            VARCHAR(200) NOT NULL,
    chapo            VARCHAR(400) NULL COMMENT 'Texte d''introduction',
    contenu          MEDIUMTEXT NULL,
    meta_description VARCHAR(300) NULL,
    modifie_par      INT UNSIGNED NULL,
    modifie_le       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug),
    CONSTRAINT fk_pages_admin FOREIGN KEY (modifie_par) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

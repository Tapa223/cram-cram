-- 005 : projets
-- statut NULL = « non précisé » (information non encore fournie).
CREATE TABLE IF NOT EXISTS projets (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titre        VARCHAR(220) NOT NULL,
    slug         VARCHAR(230) NOT NULL,
    resume       VARCHAR(400) NOT NULL,
    description  MEDIUMTEXT NULL,
    domaine_id   INT UNSIGNED NULL,
    statut       ENUM('planifie', 'en_cours', 'termine') NULL,
    date_debut   DATE NULL,
    date_fin     DATE NULL,
    zone         VARCHAR(200) NULL,
    image_id     INT UNSIGNED NULL,
    publie       TINYINT(1) NOT NULL DEFAULT 0,
    auteur_id    INT UNSIGNED NULL,
    cree_le      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projets_slug (slug),
    KEY idx_projets_publie_statut (publie, statut),
    KEY idx_projets_domaine (domaine_id),
    CONSTRAINT fk_projets_domaine FOREIGN KEY (domaine_id) REFERENCES domaines_action (id) ON DELETE SET NULL,
    CONSTRAINT fk_projets_image FOREIGN KEY (image_id) REFERENCES medias (id) ON DELETE SET NULL,
    CONSTRAINT fk_projets_auteur FOREIGN KEY (auteur_id) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

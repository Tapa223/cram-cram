-- 007 : activités / actualités
CREATE TABLE IF NOT EXISTS activites (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titre          VARCHAR(220) NOT NULL,
    slug           VARCHAR(230) NOT NULL,
    categorie      VARCHAR(60) NOT NULL,
    resume         VARCHAR(400) NULL,
    contenu        MEDIUMTEXT NULL,
    date_activite  DATE NOT NULL,
    lieu           VARCHAR(200) NULL,
    domaine_id     INT UNSIGNED NULL,
    image_id       INT UNSIGNED NULL,
    statut         ENUM('brouillon', 'publie') NOT NULL DEFAULT 'brouillon',
    auteur_id      INT UNSIGNED NULL,
    cree_le        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_activites_slug (slug),
    KEY idx_activites_statut_date (statut, date_activite),
    KEY idx_activites_domaine (domaine_id),
    CONSTRAINT fk_activites_domaine FOREIGN KEY (domaine_id) REFERENCES domaines_action (id) ON DELETE SET NULL,
    CONSTRAINT fk_activites_image FOREIGN KEY (image_id) REFERENCES medias (id) ON DELETE SET NULL,
    CONSTRAINT fk_activites_auteur FOREIGN KEY (auteur_id) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

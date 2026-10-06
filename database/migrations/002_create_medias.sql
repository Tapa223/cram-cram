-- 002 : médiathèque (images et documents PDF)
-- Les contenus référencent les médias par clé étrangère : supprimer un média
-- retire proprement l'image des contenus (ON DELETE SET NULL), sans lien mort.
CREATE TABLE IF NOT EXISTS medias (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fichier         VARCHAR(255) NOT NULL COMMENT 'Chemin relatif à public/uploads',
    nom_original    VARCHAR(255) NOT NULL,
    type_mime       VARCHAR(100) NOT NULL,
    taille          INT UNSIGNED NOT NULL DEFAULT 0,
    largeur         SMALLINT UNSIGNED NULL,
    hauteur         SMALLINT UNSIGNED NULL,
    titre           VARCHAR(200) NULL,
    texte_alt       VARCHAR(255) NULL,
    est_publication TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Document affiché dans Recherche',
    televerse_par   INT UNSIGNED NULL,
    cree_le         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_medias_fichier (fichier),
    KEY idx_medias_type (type_mime),
    KEY idx_medias_publication (est_publication),
    CONSTRAINT fk_medias_admin FOREIGN KEY (televerse_par) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

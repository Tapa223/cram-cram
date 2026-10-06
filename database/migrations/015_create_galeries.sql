-- 015 : galeries photos des projets, domaines d'action et activités
-- Une table de liaison par type de contenu, pour garder de vraies clés étrangères :
-- supprimer un contenu ou un média retire automatiquement la photo de la galerie.
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

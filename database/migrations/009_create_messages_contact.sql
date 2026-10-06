-- 009 : messages du formulaire de contact
-- Aucune adresse IP n'est conservée (minimisation des données).
CREATE TABLE IF NOT EXISTS messages_contact (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom          VARCHAR(150) NOT NULL,
    email        VARCHAR(190) NOT NULL,
    objet        VARCHAR(200) NOT NULL,
    message      TEXT NOT NULL,
    consentement TINYINT(1) NOT NULL,
    lu           TINYINT(1) NOT NULL DEFAULT 0,
    lu_le        DATETIME NULL,
    cree_le      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_messages_lu_date (lu, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 013 : limitation des tentatives de connexion par adresse réseau
-- L'adresse IP n'est jamais stockée en clair : seule une empreinte HMAC est conservée.
CREATE TABLE IF NOT EXISTS tentatives_connexion (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cle      CHAR(64) NOT NULL,
    cree_le  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tentatives_cle_date (cle, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

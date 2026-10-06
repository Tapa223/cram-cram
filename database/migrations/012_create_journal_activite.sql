-- 012 : journal des actions d'administration (alimente « Activité récente »)
CREATE TABLE IF NOT EXISTS journal_activite (
    id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id  INT UNSIGNED NULL,
    action    VARCHAR(20) NOT NULL,
    type      VARCHAR(30) NOT NULL,
    objet_id  INT UNSIGNED NULL,
    libelle   VARCHAR(255) NOT NULL,
    cree_le   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_journal_date (cree_le),
    CONSTRAINT fk_journal_admin FOREIGN KEY (admin_id) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

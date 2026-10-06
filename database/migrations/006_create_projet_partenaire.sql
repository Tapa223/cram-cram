-- 006 : liaison projets <-> partenaires (bailleurs d'un projet)
CREATE TABLE IF NOT EXISTS projet_partenaire (
    projet_id     INT UNSIGNED NOT NULL,
    partenaire_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (projet_id, partenaire_id),
    KEY idx_pp_partenaire (partenaire_id),
    CONSTRAINT fk_pp_projet FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    CONSTRAINT fk_pp_partenaire FOREIGN KEY (partenaire_id) REFERENCES partenaires (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

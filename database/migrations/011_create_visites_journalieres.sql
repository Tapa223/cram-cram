-- 011 : fréquentation agrégée par jour (aucune donnée personnelle, aucun traceur tiers)
-- visites = sessions distinctes du jour ; pages_vues = pages publiques affichées.
CREATE TABLE IF NOT EXISTS visites_journalieres (
    jour       DATE NOT NULL,
    visites    INT UNSIGNED NOT NULL DEFAULT 0,
    pages_vues INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (jour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

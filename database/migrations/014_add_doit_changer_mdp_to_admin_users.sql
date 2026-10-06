-- 014 : changement de mot de passe obligatoire
-- Un compte créé avec un mot de passe provisoire (compte initial fourni avec
-- l'installation) est redirigé vers « Mon compte » tant que ce mot de passe
-- n'a pas été remplacé.
ALTER TABLE admin_users
    ADD COLUMN doit_changer_mdp TINYINT(1) NOT NULL DEFAULT 0 AFTER actif;

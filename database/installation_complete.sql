-- ============================================================
-- CRAM-CRAM Mali — installation initiale (généré par bin/build-install-sql.php)
-- À importer dans une base VIDE, créée au préalable dans phpMyAdmin
-- (interclassement utf8mb4_unicode_ci). Ne pas importer sur une base existante :
-- pour les évolutions, utiliser les fichiers database_update fournis séparément.
-- ============================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS schema_migrations (
    migration VARCHAR(190) NOT NULL PRIMARY KEY,
    appliquee_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 001 : comptes d'administration
-- Un seul compte en v1, colonne "role" prévue pour une extension ultérieure.
CREATE TABLE IF NOT EXISTS admin_users (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom                 VARCHAR(120) NOT NULL,
    email               VARCHAR(190) NOT NULL,
    mot_de_passe_hash   VARCHAR(255) NOT NULL,
    role                ENUM('admin', 'editeur') NOT NULL DEFAULT 'admin',
    actif               TINYINT(1) NOT NULL DEFAULT 1,
    derniere_connexion  DATETIME NULL,
    tentatives_echouees SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    verrouille_jusqu_a  DATETIME NULL,
    cree_le             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('001_create_admin_users.sql');

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
INSERT IGNORE INTO schema_migrations (migration) VALUES ('002_create_medias.sql');

-- 003 : domaines d'action
CREATE TABLE IF NOT EXISTS domaines_action (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titre         VARCHAR(180) NOT NULL,
    slug          VARCHAR(190) NOT NULL,
    resume        VARCHAR(400) NOT NULL,
    description   MEDIUMTEXT NULL,
    image_id      INT UNSIGNED NULL,
    ordre         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    mis_en_avant  TINYINT(1) NOT NULL DEFAULT 0,
    publie        TINYINT(1) NOT NULL DEFAULT 1,
    cree_le       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_domaines_slug (slug),
    KEY idx_domaines_publie_ordre (publie, ordre),
    CONSTRAINT fk_domaines_image FOREIGN KEY (image_id) REFERENCES medias (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('003_create_domaines_action.sql');

-- 004 : partenaires et bailleurs (4 catégories du document de contenus)
CREATE TABLE IF NOT EXISTS partenaires (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom          VARCHAR(200) NOT NULL,
    sigle        VARCHAR(60) NULL,
    categorie    ENUM('financier', 'technique', 'national', 'international') NOT NULL,
    description  VARCHAR(400) NULL,
    pays         VARCHAR(80) NULL,
    site_web     VARCHAR(255) NULL,
    logo_id      INT UNSIGNED NULL,
    ordre        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    publie       TINYINT(1) NOT NULL DEFAULT 1,
    cree_le      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modifie_le   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_partenaires_nom (nom),
    KEY idx_partenaires_categorie (categorie, publie, ordre),
    CONSTRAINT fk_partenaires_logo FOREIGN KEY (logo_id) REFERENCES medias (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('004_create_partenaires.sql');

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
INSERT IGNORE INTO schema_migrations (migration) VALUES ('005_create_projets.sql');

-- 006 : liaison projets <-> partenaires (bailleurs d'un projet)
CREATE TABLE IF NOT EXISTS projet_partenaire (
    projet_id     INT UNSIGNED NOT NULL,
    partenaire_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (projet_id, partenaire_id),
    KEY idx_pp_partenaire (partenaire_id),
    CONSTRAINT fk_pp_projet FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    CONSTRAINT fk_pp_partenaire FOREIGN KEY (partenaire_id) REFERENCES partenaires (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('006_create_projet_partenaire.sql');

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
INSERT IGNORE INTO schema_migrations (migration) VALUES ('007_create_activites.sql');

-- 008 : pages éditoriales éditables (qui sommes-nous, recherche, valeur ajoutée, mentions légales)
CREATE TABLE IF NOT EXISTS pages (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug             VARCHAR(80) NOT NULL,
    titre            VARCHAR(200) NOT NULL,
    chapo            VARCHAR(400) NULL COMMENT 'Texte d''introduction',
    contenu          MEDIUMTEXT NULL,
    meta_description VARCHAR(300) NULL,
    modifie_par      INT UNSIGNED NULL,
    modifie_le       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug),
    CONSTRAINT fk_pages_admin FOREIGN KEY (modifie_par) REFERENCES admin_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('008_create_pages.sql');

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
INSERT IGNORE INTO schema_migrations (migration) VALUES ('009_create_messages_contact.sql');

-- 010 : paramètres du site (clé / valeur)
CREATE TABLE IF NOT EXISTS parametres (
    cle        VARCHAR(80) NOT NULL,
    valeur     TEXT NULL,
    modifie_le DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('010_create_parametres.sql');

-- 011 : fréquentation agrégée par jour (aucune donnée personnelle, aucun traceur tiers)
-- visites = sessions distinctes du jour ; pages_vues = pages publiques affichées.
CREATE TABLE IF NOT EXISTS visites_journalieres (
    jour       DATE NOT NULL,
    visites    INT UNSIGNED NOT NULL DEFAULT 0,
    pages_vues INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (jour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('011_create_visites_journalieres.sql');

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
INSERT IGNORE INTO schema_migrations (migration) VALUES ('012_create_journal_activite.sql');

-- 013 : limitation des tentatives de connexion par adresse réseau
-- L'adresse IP n'est jamais stockée en clair : seule une empreinte HMAC est conservée.
CREATE TABLE IF NOT EXISTS tentatives_connexion (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cle      CHAR(64) NOT NULL,
    cree_le  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tentatives_cle_date (cle, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('013_create_tentatives_connexion.sql');

-- 014 : changement de mot de passe obligatoire
-- Un compte créé avec un mot de passe provisoire (compte initial fourni avec
-- l'installation) est redirigé vers « Mon compte » tant que ce mot de passe
-- n'a pas été remplacé.
ALTER TABLE admin_users
    ADD COLUMN doit_changer_mdp TINYINT(1) NOT NULL DEFAULT 0 AFTER actif;
INSERT IGNORE INTO schema_migrations (migration) VALUES ('014_add_doit_changer_mdp_to_admin_users.sql');

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
INSERT IGNORE INTO schema_migrations (migration) VALUES ('015_create_galeries.sql');

-- ============================================================
-- Contenus initiaux issus du document « Contenus du site web CRAM-CRAM Mali ».
-- Idempotent : INSERT IGNORE, peut être rejoué sans créer de doublon.
-- Aucune donnée chiffrée inventée : statuts et dates des projets sont laissés
-- vides (« non précisé ») tant qu'ils ne sont pas confirmés par l'équipe.
-- ============================================================
SET NAMES utf8mb4;

-- Domaines d'action
INSERT IGNORE INTO domaines_action (titre, slug, resume, description, ordre, mis_en_avant, publie) VALUES ('Éducation à la paix et leadership des jeunes', 'education-paix-leadership-jeunes', 'Nous renforçons chez les jeunes générations les compétences de dialogue, de leadership et de résolution des conflits, en ligne et hors ligne.', '<p>Nous renforçons chez les jeunes générations les compétences de dialogue, de leadership et de résolution des conflits, en ligne et hors ligne. Ces parcours préparent les jeunes à un rôle actif dans la cohésion sociale de leur communauté.</p>', 1, 0, 1);
INSERT IGNORE INTO domaines_action (titre, slug, resume, description, ordre, mis_en_avant, publie) VALUES ('Médias sensibles aux conflits et intégrité informationnelle', 'medias-sensibles-conflits-integrite-informationnelle', 'Nous soutenons une information responsable et vérifiée, et renforçons la résilience des populations face aux contenus polarisants.', '<p>Nous soutenons une information responsable et vérifiée. Nos comités de surveillance de l''information communautaire renforcent la résilience des populations face aux contenus polarisants et aux manipulations en ligne.</p>', 2, 0, 1);
INSERT IGNORE INTO domaines_action (titre, slug, resume, description, ordre, mis_en_avant, publie) VALUES ('Lutte contre la désinformation, les discours de haine et la polarisation', 'lutte-desinformation-discours-haine-polarisation', 'Nous agissons contre la désinformation, la mésinformation et les discours de haine qui alimentent la polarisation sociale.', '<p>Nous agissons contre la désinformation (intentionnelle), la mésinformation (involontaire) et les discours de haine qui alimentent la polarisation sociale.</p><p>Notre approche associe l''alphabétisation médiatique, la vérification des faits, la veille communautaire et des narratifs numériques responsables. Elle vise à réduire les dynamiques de polarisation et à renforcer l''intégrité informationnelle des espaces publics et numériques.</p>', 3, 1, 1);
INSERT IGNORE INTO domaines_action (titre, slug, resume, description, ordre, mis_en_avant, publie) VALUES ('Diversité culturelle et dialogue interculturel', 'diversite-culturelle-dialogue-interculturel', 'Nous valorisons la diversité culturelle comme levier de cohésion entre les communautés.', '<p>Nous valorisons la diversité culturelle comme levier de cohésion. Musique, expression artistique et espaces de rencontre facilitent le dialogue entre communautés et renforcent le terrain d''entente.</p>', 4, 0, 1);
INSERT IGNORE INTO domaines_action (titre, slug, resume, description, ordre, mis_en_avant, publie) VALUES ('Genre, autonomisation des femmes et lutte contre les VBG', 'genre-autonomisation-femmes-lutte-vbg', 'Nous soutenons le leadership des femmes dans les processus de paix et prévenons les violences basées sur le genre.', '<p>Nous soutenons le leadership des femmes dans les processus de paix et prévenons les violences basées sur le genre et les abus et exploitations sexuels.</p><p>Un dispositif de sauvegarde dédié protège les personnes et renforce les normes sociales favorables au respect et à l''égalité.</p>', 5, 0, 1);
INSERT IGNORE INTO domaines_action (titre, slug, resume, description, ordre, mis_en_avant, publie) VALUES ('Gouvernance durable des ressources naturelles', 'gouvernance-durable-ressources-naturelles', 'Nous œuvrons à une gestion partagée du foncier, de l''eau et des ressources agropastorales, articulée à la cohésion sociale.', '<p>Nous œuvrons à une gestion partagée du foncier, de l''eau et des ressources agropastorales, articulée à la cohésion sociale. La gouvernance concertée des ressources contribue à réduire les facteurs structurels de tension au niveau local.</p>', 6, 0, 1);
INSERT IGNORE INTO domaines_action (titre, slug, resume, description, ordre, mis_en_avant, publie) VALUES ('Recherche-action pour la paix', 'recherche-action-paix', 'Nous produisons des connaissances utiles à la décision et à l''action.', '<p>Nous produisons des connaissances utiles à la décision et à l''action. Nos travaux documentent les dynamiques de conflit, testent des innovations numériques et alimentent un plaidoyer fondé sur des preuves, aux côtés d''universités et de partenaires régionaux.</p>', 7, 0, 1);

-- Partenaires et bailleurs
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Délégation de l''Union européenne au Mali', 'UE', 'financier', NULL, 'Mali', 1, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Mission multidimensionnelle intégrée des Nations Unies pour la stabilisation au Mali', 'MINUSMA', 'financier', NULL, 'Mali', 2, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Organisation internationale de la Francophonie', 'OIF', 'financier', NULL, NULL, 3, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('OXFAM Mali', 'OXFAM', 'financier', NULL, 'Mali', 4, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Conférence des ministres de la Jeunesse et des Sports de la Francophonie', 'CONFEJES', 'financier', NULL, NULL, 5, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Search for Common Ground', NULL, 'financier', NULL, NULL, 6, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Global Development Network', 'GDN', 'technique', NULL, NULL, 1, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('LaSEn, Université de Parakou', 'LaSEn', 'technique', 'Partenaire du consortium de recherche Mali – Bénin.', 'Bénin', 2, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Université Yambo Ouologuem de Bamako', 'UYOB', 'technique', 'Partenaire du consortium de recherche Mali – Bénin.', 'Mali', 3, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Association Femmes du Mali Mobilisées pour le leadership', 'FEMML', 'national', NULL, 'Mali', 1, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Association pour la promotion des jeunes et enfants communicateurs du Mali', 'APJEC', 'national', NULL, 'Mali', 2, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Forum de Bamako sur le numérique et la cohésion sociale', NULL, 'national', NULL, 'Mali', 3, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Association Sira Musow', 'ASM', 'national', NULL, 'Mali', 4, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Association des Professionnelles Africaines de la Communication', NULL, 'international', NULL, 'Niger', 1, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Association Heinrich Klose', NULL, 'international', NULL, 'Togo', 2, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Compagnie théâtrale et cinématographique Mbagoutolom', NULL, 'international', NULL, 'Cameroun', 3, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Centre pour la qualité du droit et la justice', 'CQDJ', 'international', NULL, 'Burkina Faso', 4, 1);
INSERT IGNORE INTO partenaires (nom, sigle, categorie, description, pays, ordre, publie) VALUES ('Club RFI Nouadhibou', NULL, 'international', NULL, 'Mauritanie', 5, 1);

-- Projets (statut et dates non précisés dans le document : laissés vides)
INSERT IGNORE INTO projets (titre, slug, resume, description, domaine_id, statut, zone, publie) SELECT 'Lutte contre la désinformation et les discours de haine', 'lutte-desinformation-discours-haine', 'Mise en place des comités de surveillance de l''information communautaire, veille et vérification de l''information locale.', '<p>Mise en place des comités de surveillance de l''information communautaire, veille et vérification de l''information locale, renforcement de la résilience face aux discours de haine et aux manipulations en ligne.</p>', (SELECT id FROM domaines_action WHERE slug = 'lutte-desinformation-discours-haine-polarisation'), NULL, NULL, 1;
INSERT IGNORE INTO projets (titre, slug, resume, description, domaine_id, statut, zone, publie) SELECT 'Cohésion sociale par la diversité culturelle', 'cohesion-sociale-diversite-culturelle', 'Restauration et modernisation d''instruments et de chants traditionnels pour renforcer les liens entre les communautés du cercle de Diré.', '<p>Restauration et modernisation d''instruments et de chants traditionnels pour renforcer les liens entre les communautés du cercle de Diré et raviver la diversité culturelle de la région de Tombouctou.</p>', (SELECT id FROM domaines_action WHERE slug = 'diversite-culturelle-dialogue-interculturel'), NULL, 'Cercle de Diré, région de Tombouctou', 1;
INSERT IGNORE INTO projets (titre, slug, resume, description, domaine_id, statut, zone, publie) SELECT 'Culture et paix par les jeunes (Podium de la paix)', 'culture-paix-jeunes-podium-paix', 'Compétition musicale inclusive réunissant des artistes d''horizons différents, au service de la cohésion sociale à Tombouctou.', '<p>Compétition musicale inclusive réunissant des artistes d''horizons différents, au service de la cohésion sociale et des liens intercommunautaires à Tombouctou.</p>', (SELECT id FROM domaines_action WHERE slug = 'diversite-culturelle-dialogue-interculturel'), NULL, 'Tombouctou', 1;
INSERT IGNORE INTO projets (titre, slug, resume, description, domaine_id, statut, zone, publie) SELECT 'Jeunes ruraux et urbains pour la paix et la sécurité', 'jeunes-ruraux-urbains-paix-securite', 'Engagement civique des jeunes par l''éducation, la formation et la participation, au service de la stabilité des communautés.', '<p>Engagement civique des jeunes par l''éducation, la formation et la participation, au service de la stabilité des communautés de Tombouctou.</p>', (SELECT id FROM domaines_action WHERE slug = 'education-paix-leadership-jeunes'), NULL, 'Tombouctou', 1;
INSERT IGNORE INTO projets (titre, slug, resume, description, domaine_id, statut, zone, publie) SELECT 'Campagne médiatique #JECOMPTE', 'campagne-mediatique-jecompte', 'Valorisation du rôle des femmes dans la prévention des conflits, la médiation et la reconstruction post-conflit.', '<p>Valorisation du rôle des femmes dans la prévention des conflits, la médiation et la reconstruction post-conflit, avec un renforcement de leur visibilité et de leur influence.</p>', (SELECT id FROM domaines_action WHERE slug = 'genre-autonomisation-femmes-lutte-vbg'), NULL, NULL, 1;
INSERT IGNORE INTO projets (titre, slug, resume, description, domaine_id, statut, zone, publie) SELECT 'Santé de la femme rurale et cohésion sociale par le sport', 'sante-femme-rurale-cohesion-sociale-sport', 'Participation des femmes rurales à des activités sportives, au service de la solidarité, de l''estime de soi et de la résilience.', '<p>Participation des femmes rurales à des activités sportives, au service de la solidarité, de l''estime de soi, de la résilience et de la cohésion sociale.</p>', (SELECT id FROM domaines_action WHERE slug = 'genre-autonomisation-femmes-lutte-vbg'), NULL, NULL, 1;
INSERT IGNORE INTO projet_partenaire (projet_id, partenaire_id) SELECT p.id, pa.id FROM projets p JOIN partenaires pa ON pa.nom = 'Search for Common Ground' WHERE p.slug = 'lutte-desinformation-discours-haine';
INSERT IGNORE INTO projet_partenaire (projet_id, partenaire_id) SELECT p.id, pa.id FROM projets p JOIN partenaires pa ON pa.nom = 'Délégation de l''Union européenne au Mali' WHERE p.slug = 'cohesion-sociale-diversite-culturelle';
INSERT IGNORE INTO projet_partenaire (projet_id, partenaire_id) SELECT p.id, pa.id FROM projets p JOIN partenaires pa ON pa.nom = 'Mission multidimensionnelle intégrée des Nations Unies pour la stabilisation au Mali' WHERE p.slug = 'culture-paix-jeunes-podium-paix';
INSERT IGNORE INTO projet_partenaire (projet_id, partenaire_id) SELECT p.id, pa.id FROM projets p JOIN partenaires pa ON pa.nom = 'Organisation internationale de la Francophonie' WHERE p.slug = 'jeunes-ruraux-urbains-paix-securite';
INSERT IGNORE INTO projet_partenaire (projet_id, partenaire_id) SELECT p.id, pa.id FROM projets p JOIN partenaires pa ON pa.nom = 'OXFAM Mali' WHERE p.slug = 'campagne-mediatique-jecompte';
INSERT IGNORE INTO projet_partenaire (projet_id, partenaire_id) SELECT p.id, pa.id FROM projets p JOIN partenaires pa ON pa.nom = 'Conférence des ministres de la Jeunesse et des Sports de la Francophonie' WHERE p.slug = 'sante-femme-rurale-cohesion-sociale-sport';

-- Pages éditoriales
INSERT IGNORE INTO pages (slug, titre, chapo, contenu, meta_description) VALUES ('qui-sommes-nous', 'Qui sommes-nous', 'Une organisation non gouvernementale nationale engagée pour la consolidation de la paix et la cohésion sociale au Mali.', '<h2>Historique</h2><p>Le Comité de Recherches et d''Actions Multidimensionnelles pour la paix (CRAM-CRAM Mali) est une organisation non gouvernementale nationale, créée le 17 décembre 2016 à Tombouctou. Elle a acquis une dimension nationale avec l''obtention d''un accord-cadre avec l''État malien le 7 octobre 2021.</p><h2>Mission</h2><p>Notre mission est de contribuer à la consolidation de la paix, de prévenir l''extrémisme violent et de renforcer la cohésion sociale. Nous agissons sur les micro-crises locales avant leur escalade, dans les domaines du foncier, des ressources naturelles, du genre et de l''information.</p><h2>Vision</h2><p>CRAM-CRAM ambitionne de jouer un rôle de premier plan dans l''échange de connaissances entre les jeunes et les femmes, avec un accent particulier sur l''inclusion et l''égalité de genre, au service de la stabilité et d''une paix durable.</p><h2>Notre ancrage</h2><p>L''organisation dispose d''un ancrage communautaire au Nord, au Centre et au Sud du Mali. Cet ancrage est complété par une capacité de recherche-action consolidée par un consortium scientifique Mali et Bénin.</p><h2>Gouvernance</h2><p>La gouvernance repose sur une Assemblée Générale, un Conseil d''Administration (CA) et un Bureau Exécutif National (BEN), dirigé par la coordination nationale. Les décisions majeures relèvent de l''Assemblée Générale, qui donne mandat au Bureau Exécutif pour la conduite des activités.</p>', 'CRAM-CRAM Mali, ONG nationale créée en 2016 à Tombouctou : historique, mission, vision, ancrage et gouvernance.');
INSERT IGNORE INTO pages (slug, titre, chapo, contenu, meta_description) VALUES ('recherche', 'Recherche et consortiums', 'CRAM-CRAM inscrit son action dans une démarche de recherche-action. Nous associons production scientifique, expérimentation de terrain et plaidoyer fondé sur des preuves, aux côtés d''universités et de partenaires régionaux.', '<h2>Production de connaissances</h2><p>Au-delà de la mise en œuvre, CRAM-CRAM produit des connaissances actionnables :</p><ul><li>notes d''analyse sur les dynamiques de conflit amplifiées par la technologie ;</li><li>bulletins périodiques sur les dynamiques de conflit et les opportunités de paix ;</li><li>études scientifiques menées dans le cadre du consortium de recherche.</li></ul><h2>Consortium CRAM-CRAM Mali – LaSEn – UYOB</h2><p>Consortium réunissant CRAM-CRAM Mali (Mali), le LaSEn de l''Université de Parakou (Bénin) et l''UYOB, Université Yambo Ouologuem de Bamako (Mali).</p><p>Recherche-action en Afrique de l''Ouest sur les innovations numériques, la gestion durable des ressources naturelles partagées et la cohésion sociale, dans un contexte de fragilité sécuritaire. Le consortium porte notamment une étude comparative Mali et Bénin sur ces trois dimensions.</p>', 'Recherche-action de CRAM-CRAM Mali : notes d''analyse, bulletins, études et consortium de recherche Mali – Bénin.');
INSERT IGNORE INTO pages (slug, titre, chapo, contenu, meta_description) VALUES ('valeur-ajoutee', 'Notre valeur ajoutée', 'Pourquoi travailler avec CRAM-CRAM.', '', 'Ce qui distingue CRAM-CRAM Mali : lecture fine des dynamiques de conflit, analyse des médias sociaux, consortium de recherche régional.');
INSERT IGNORE INTO pages (slug, titre, chapo, contenu, meta_description) VALUES ('mentions-legales', 'Mentions légales et confidentialité', NULL, '<h2>Éditeur du site</h2><p>Ce site est édité par CRAM-CRAM Mali (Comité de Recherches et d''Actions Multidimensionnelles pour la paix), organisation non gouvernementale nationale, dont le siège est à Bamako, République du Mali.</p><h2>Hébergement</h2><p>[Nom et adresse de l''hébergeur à compléter avant la mise en ligne.]</p><h2>Données personnelles</h2><p>Les informations transmises par le formulaire de contact (nom, adresse e-mail, objet, message) sont utilisées uniquement pour répondre à votre demande. Elles ne sont ni vendues ni cédées à des tiers. Aucune adresse IP n''est enregistrée avec votre message.</p><p>Vous pouvez demander l''accès, la rectification ou la suppression de vos données en écrivant à l''adresse de contact indiquée sur ce site.</p><h2>Mesure d''audience</h2><p>Le site comptabilise uniquement le nombre de visites et de pages vues par jour, de manière agrégée. Aucun outil de suivi tiers ni cookie publicitaire n''est utilisé.</p><h2>Cookies</h2><p>Un seul cookie technique, strictement nécessaire, est déposé pour sécuriser les formulaires. Il est supprimé à la fermeture du navigateur.</p>', 'Mentions légales et politique de confidentialité du site de CRAM-CRAM Mali.');

-- Paramètres du site
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('site_nom', 'CRAM-CRAM Mali');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('site_sous_titre', 'Recherches et actions pour la paix');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('site_description', 'CRAM-CRAM Mali agit pour la consolidation de la paix, la protection de l''espace numérique et la cohésion sociale au Mali.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('contact_adresse', 'Bamako, République du Mali');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('contact_telephone', '+223 71 94 37 88');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('contact_telephone_2', '+223 60 07 81 56');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('contact_email', 'mali@cramcram.org');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('contact_email_2', 'cramcram.mali@gmail.com');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('reseau_facebook', '');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('reseau_x', '');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('reseau_linkedin', '');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('reseau_tiktok', '');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_badge', 'ONG nationale malienne · créée en 2016 à Tombouctou');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_titre', 'Construire la paix, protéger l''espace numérique, renforcer la cohésion sociale.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_intro', 'CRAM-CRAM Mali agit sur les micro-crises locales (foncier, ressources, information) avant leur escalade, là où la prévention offre le meilleur rendement.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_encart_valeur', '2016');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_encart_texte', 'Création à Tombouctou, le 17 décembre. Dimension nationale depuis l''accord-cadre de 2021.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_qsn_titre', 'Agir sur les micro-crises locales avant leur escalade.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_qsn_texte', 'Notre action mobilise l''éducation à la paix, les médias sensibles aux conflits, la lutte contre la désinformation et la gouvernance durable des ressources naturelles. Notre ancrage communautaire couvre le Nord, le Centre et le Sud du Mali.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_mission', 'Contribuer à la consolidation de la paix, prévenir l''extrémisme violent et renforcer la cohésion sociale.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_vision', 'Jouer un rôle de premier plan dans l''échange de connaissances entre les jeunes et les femmes, avec un accent particulier sur l''inclusion et l''égalité de genre.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('accueil_gouvernance', 'Une Assemblée Générale, un Conseil d''Administration et un Bureau Exécutif National, dirigé par la coordination nationale.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_1_valeur', '2016');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_1_libelle', 'Création à Tombouctou');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_2_valeur', '2021');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_2_libelle', 'Accord-cadre avec l''État malien');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_3_valeur', '3');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_3_libelle', 'zones d''ancrage : Nord, Centre et Sud');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_4_valeur', '7');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_4_libelle', 'domaines d''action complémentaires');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_5_valeur', '2');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('repere_5_libelle', 'pays dans le consortium de recherche');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('valeur_1_titre', 'Une lecture fine des dynamiques de conflit, y compris numériques.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('valeur_1_texte', 'Nos notes d''analyse et nos bulletins périodiques offrent une capacité d''alerte précoce et de lecture de terrain, à un moment où la désinformation et les discours de haine en ligne constituent un facteur de conflit documenté, au même titre que les tensions foncières ou pastorales.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('valeur_2_titre', 'Une capacité d''analyse des conflits sur les médias sociaux.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('valeur_2_texte', 'Nous détectons, qualifions et suivons la propagation des tensions communautaires en ligne, avant leur bascule hors ligne. Investir dans la prévention plutôt que dans la réponse permet d''agir tant que les crises restent réversibles.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('valeur_3_titre', 'Une mise à l''échelle régionale déjà engagée.');
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('valeur_3_texte', 'Un consortium de recherche binational avec l''UYOB (Bamako) et le LaSEn (Université de Parakou, Bénin), articulé à des réseaux tels que le CRDI et la GIZ (ZFD). Financer CRAM-CRAM, c''est soutenir un acteur qui produit des connaissances transférables au-delà du seul contexte malien.');

-- Compte administrateur initial.
-- Le mot de passe provisoire est stocké uniquement sous forme hachée (bcrypt) et
-- communiqué séparément. Le flag doit_changer_mdp impose de le remplacer dès la
-- première connexion. INSERT IGNORE : un compte existant avec cette adresse n'est
-- jamais modifié.
INSERT IGNORE INTO admin_users (nom, email, mot_de_passe_hash, role, actif, doit_changer_mdp)
VALUES ('Administrateur CRAM-CRAM', 'admin@cramcram.org', '$2y$12$mVoEHnkAmrIxEysSA.7Gr.4eUYppkD0/YUsvGXtjDnYahMBMPEsJu', 'admin', 1, 1);

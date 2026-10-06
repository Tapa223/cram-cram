# CRAM-CRAM Mali — site institutionnel et administration

Site public de l'ONG CRAM-CRAM Mali et espace d'administration des contenus.

- PHP natif 8.2 ou plus, architecture MVC maison, aucun framework ni dépendance externe
- MySQL / MariaDB via PDO (requêtes préparées uniquement)
- HTML, CSS et JavaScript sans bibliothèque ; polices hébergées localement
- Compatible XAMPP (en sous-dossier) et hébergement mutualisé type LWS

---

## 1. Prérequis

| Élément | Version / extension |
|---|---|
| PHP | 8.2 minimum (testé en 8.3 et 8.4) |
| Extensions PHP | `pdo_mysql`, `mbstring`, `fileinfo`, `dom` (obligatoires) ; `gd` (recommandée : redimensionnement et nettoyage des images), `intl` (facultative) |
| Base de données | MySQL 5.7+ ou MariaDB 10.4+ |
| Serveur web | Apache avec `mod_rewrite` (fourni par XAMPP et LWS) |

XAMPP récent fournit tout cela par défaut.

---

## 2. Installation en local avec XAMPP

1. **Copier le projet** dans `C:\xampp\htdocs\cramcram` (le nom du dossier devient l'adresse : `http://localhost/cramcram`).
2. **Démarrer Apache et MySQL** depuis le panneau XAMPP.
3. **Créer la base, les tables et le compte administrateur** : dans phpMyAdmin (`http://localhost/phpmyadmin`), sans sélectionner de base, onglet *Importer*, fichier `database/cramcram_installation.sql`, puis *Importer*.
   Ce fichier crée la base `cramcram`, les 17 tables, les contenus du document de référence (domaines, projets, partenaires, pages, coordonnées) et le compte administrateur initial. Il ne s'importe qu'une seule fois.
4. *(Variante)* Si la base a déjà été créée à la main, la sélectionner puis importer `database/installation_complete.sql` (même contenu, sans création de base).
5. **Configurer** : copier `.env.example` en `.env` à la racine du projet. Avec XAMPP, les valeurs par défaut conviennent (utilisateur `root`, mot de passe vide).
   Renseigner `APP_KEY` avec une clé aléatoire. Dans une invite de commandes :
   ```
   C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32));"
   ```
6. **Première connexion** : se connecter avec l'adresse et le mot de passe provisoire communiqués séparément (ils ne figurent dans aucun fichier du dépôt ; seule leur empreinte chiffrée est dans le SQL). L'administration impose alors de choisir un mot de passe personnel avant tout autre accès ; remplacer aussi l'adresse e-mail de connexion dans *Mon compte*.
   Pour créer un compte supplémentaire : `C:\xampp\php\php.exe bin\create-admin.php`.
7. **Ouvrir le site** : `http://localhost/cramcram/`
   **Administration** : `http://localhost/cramcram/admin`

> Autre méthode possible pour l'étape 4 : `php bin/migrate.php --seed` applique les migrations puis les contenus initiaux.

---

## 3. Mise en ligne sur LWS (hébergement mutualisé)

1. Créer la base MySQL dans l'espace client LWS et noter l'hôte, le nom, l'utilisateur et le mot de passe.
2. Importer `database/installation_complete.sql` dans cette base via le phpMyAdmin de LWS (la base existe déjà chez LWS : ne pas utiliser `cramcram_installation.sql`).
3. Transférer les fichiers par FTP/SFTP. Idéalement, faire pointer le domaine vers le dossier `public/`. Sinon, déposer le projet à la racine : le fichier `.htaccess` racine redirige vers `public/` et bloque les dossiers internes.
4. Créer le fichier `.env` sur le serveur avec :
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<nouvelle clé aléatoire>
   DB_HOST=… DB_NAME=… DB_USER=… DB_PASS=…
   ```
5. Vérifier que `public/uploads/` et `storage/logs/` sont accessibles en écriture (droits 755 ou 775 selon LWS).
6. Activer le certificat SSL (gratuit chez LWS). Les cookies passent automatiquement en mode sécurisé en HTTPS.
7. Se connecter avec le compte initial et changer immédiatement le mot de passe (imposé par l'application) et l'adresse e-mail.
8. Compléter la page *Mentions légales* (nom et adresse de l'hébergeur) depuis *Administration › Pages*.

---

## 4. Évolutions de la base de données

- Chaque changement de structure fait l'objet d'une **nouvelle** migration numérotée dans `database/migrations/` (`014_…`, `015_…`). Une migration déjà appliquée n'est jamais modifiée.
- `php bin/migrate.php --status` liste les migrations appliquées et en attente ; `php bin/migrate.php` applique celles en attente.
- Pour une base gérée uniquement par phpMyAdmin, chaque évolution est aussi fournie dans `database_update.sql` (racine du projet), idempotent et commenté : il peut être importé sur une base existante sans perte de données. Il contient actuellement la migration 014 (changement de mot de passe obligatoire), le compte administrateur initial et la migration 015 (galeries photos).
- `php bin/build-install-sql.php` régénère `database/installation_complete.sql` et `database/cramcram_installation.sql` après l'ajout de migrations.

---

## 5. Structure du projet

```
app/
├── Controllers/          contrôleurs du site public
│   └── Admin/            contrôleurs de l'administration (un par module)
├── Core/                 socle : configuration, base de données, routeur, requête, réponse,
│                         vues, session, CSRF, validation, pagination, gestion des erreurs
├── Middleware/           authentification, invité, CSRF, mesure d'audience
├── Models/               accès aux données (une classe par table métier)
├── Services/             authentification, téléversement, nettoyage du contenu riche,
│                         paramètres, statistiques, journal d'activité
├── Views/                gabarits (layouts), vues publiques, vues d'administration, erreurs
└── helpers.php           fonctions d'affichage (échappement, adresses, dates, icônes)
bootstrap/app.php         initialisation commune
bin/                      outils en ligne de commande (migrations, compte admin, SQL d'installation)
database/
├── migrations/           001 à 015
├── seeds/                contenus initiaux issus du document de référence
├── installation_complete.sql   installation dans une base existante
└── cramcram_installation.sql   installation complète (crée la base)
public/                   seul dossier exposé au web
├── index.php             point d'entrée unique
├── assets/               css, js, polices, images
└── uploads/              fichiers téléversés (exécution de scripts interdite)
routes/
├── web.php               routes du site public
└── admin.php             routes de l'administration
storage/logs/             journaux d'erreurs (jamais affichés aux visiteurs)
```

---

## 6. Fonctionnalités

**Site public** : Accueil, Qui sommes-nous, Domaines d'action (liste et détail), Projets (filtres, détail avec bailleurs), Recherche et consortiums (publications PDF), Valeur ajoutée, Actualités (catégories, pagination, détail), Partenaires (4 catégories), Contact (formulaire avec consentement), Mentions légales, page 404.

**Administration** :

| Module | Possibilités |
|---|---|
| Tableau de bord | indicateurs réels, fréquentation (7 j / 30 j / 12 mois), éléments à traiter, projets par domaine, qualité des contenus, activité récente |
| Activités | créer, modifier, supprimer, brouillon / publié, photo principale et galerie photos, éditeur de texte, actions groupées |
| Projets | idem + domaine, statut, dates, zone, bailleurs liés, recherche et filtres |
| Galeries photos | sur les projets, domaines et activités : ajout de plusieurs photos à la fois ou depuis la médiathèque, retrait ; visionneuse sur le site public |
| Domaines d'action | créer, modifier, ordonner, domaine phare, visibilité |
| Partenaires | 4 catégories, logo, site web, visibilité, actions groupées |
| Médias | téléversement multiple (images, PDF), texte alternatif, publication dans Recherche, suppression |
| Pages | 4 pages éditoriales modifiables |
| Messages | lecture, lu / non lu, réponse par e-mail, suppression, actions groupées |
| Statistiques | visites et pages vues agrégées, activités par mois, répartitions |
| Paramètres | identité, coordonnées, réseaux sociaux, image et textes de l'accueil, repères, valeur ajoutée |
| Mon compte | profil, changement de mot de passe (obligatoire à la première connexion) |

Toutes les interfaces s'adaptent au mobile ; les formulaires fonctionnent aussi sans JavaScript.

---

## 7. Sécurité mise en œuvre

- Requêtes préparées PDO partout, émulation désactivée
- Jeton CSRF sur tous les formulaires envoyés en POST ; suppressions uniquement en POST, avec confirmation
- Échappement systématique à l'affichage ; contenu riche nettoyé à l'enregistrement par liste blanche (scripts, attributs d'événement et liens `javascript:` supprimés)
- Politique de sécurité du contenu (CSP) stricte envoyée par PHP : aucun script ni style en ligne, aucune ressource externe
- Mots de passe hachés (`password_hash`), verrouillage du compte après 5 échecs, limitation par adresse réseau (empreinte HMAC, jamais l'IP en clair), message d'erreur identique que le compte existe ou non
- Session durcie (HttpOnly, SameSite, mode strict, régénération à la connexion, expiration après 2 h d'inactivité) ; compte revérifié en base à chaque requête
- Téléversements : type réel vérifié par le contenu, images réencodées et redimensionnées (2000 px max), PDF contrôlés par signature, noms aléatoires, exécution de scripts interdite dans `uploads/`
- Formulaire de contact : validation serveur, consentement obligatoire, piège à robots, 5 envois par heure maximum ; aucune adresse IP enregistrée
- Erreurs journalisées dans `storage/logs/`, jamais affichées aux visiteurs en production
- Dossiers internes, `.env`, fichiers SQL et journaux inaccessibles depuis le web

---

## 8. Contenus à compléter avant la mise en ligne

Les contenus chargés proviennent du document de référence. Aucune donnée chiffrée n'a été inventée.

- **Logo officiel** : remplacer `public/assets/img/logo.svg` (version couleur), `logo-blanc.svg` (version sur fond bleu) et `favicon.svg` par les fichiers officiels, en gardant les mêmes noms. Le logo est utilisé partout à partir de ces fichiers.
- **Image de l'accueil** : à choisir dans *Paramètres › Page d'accueil* (une composition graphique s'affiche en attendant).
- **Photos de terrain** : à téléverser dans les galeries des projets, domaines et activités, ou dans *Médias* (photos réelles et consenties).
- **Logos des partenaires** : à ajouter dans *Partenaires* (les initiales s'affichent en attendant).
- **Statuts et dates des projets** : laissés « non précisés », à renseigner dans *Projets*.
- **Actualités** : aucune n'est publiée ; la section de l'accueil apparaît dès la première publication.
- **Page Valeur ajoutée** : les trois arguments sont en place ; un texte complémentaire peut être ajouté.
- **Mentions légales** : nom et adresse de l'hébergeur.
- **Réseaux sociaux** : adresses à saisir dans *Paramètres › Réseaux sociaux*.
- **Points signalés dans le document** : intitulés exacts du CRDI et de la GIZ (ZFD), composition définitive du consortium, valeurs de couleur à caler sur le logo officiel.

---

## 9. Mise sur GitHub

```
git init
git add .
git commit -m "Version initiale du site CRAM-CRAM Mali"
git branch -M main
git remote add origin https://github.com/<compte>/<depot>.git
git push -u origin main
```

Le fichier `.env`, les fichiers téléversés et les journaux sont exclus par `.gitignore`.

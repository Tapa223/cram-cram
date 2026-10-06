<?php
/**
 * Gabarit du site public.
 * @var string $content
 * @var string|null $titrePage
 * @var string|null $metaDescription
 */

$nomSite = setting('site_nom', 'CRAM-CRAM Mali');
$titre = !empty($titrePage) ? $titrePage . ' · ' . $nomSite : $nomSite . ' · ' . setting('site_sous_titre', 'Recherches et actions pour la paix');
$description = !empty($metaDescription) ? $metaDescription : setting('site_description');
$navigation = [
    ['/', 'Accueil', true],
    ['/qui-sommes-nous', 'Qui sommes-nous', false],
    ['/domaines-action', "Domaines d'action", false],
    ['/projets', 'Projets', false],
    ['/recherche', 'Recherche', false],
    ['/valeur-ajoutee', 'Valeur ajoutée', false],
    ['/actualites', 'Actualités', false],
    ['/partenaires', 'Partenaires', false],
];
$reseaux = array_filter([
    'Facebook' => setting('reseau_facebook'),
    'X'        => setting('reseau_x'),
    'LinkedIn' => setting('reseau_linkedin'),
    'TikTok'   => setting('reseau_tiktok'),
], static fn (string $u): bool => $u !== '' && is_safe_url($u));
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titre) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta property="og:title" content="<?= e($titre) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:type" content="website">
<meta name="theme-color" content="#15315F">
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#contenu">Aller au contenu</a>

<header class="site-header" data-header>
    <div class="site-header__inner wrap">
        <a href="<?= e(url('/')) ?>" class="brand" aria-label="<?= e($nomSite) ?>, retour à l'accueil">
            <?= logo(56) ?>
        </a>
        <nav class="nav" id="navigation" aria-label="Navigation principale" data-nav>
            <ul class="nav__list">
                <?php foreach ($navigation as [$href, $libelle, $exact]): ?>
                    <li><a class="nav__link<?= nav_active($href, $exact) ?>" href="<?= e(url($href)) ?>"<?= aria_current($href, $exact) ?>><?= e($libelle) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn--primary nav__cta<?= nav_active('/contact', true) ?>" href="<?= e(url('/contact')) ?>">Nous contacter</a>
        </nav>
        <button type="button" class="burger" data-nav-toggle aria-controls="navigation" aria-expanded="false">
            <span class="burger__lines" aria-hidden="true"></span>
            <span class="sr-only">Menu</span>
        </button>
    </div>
</header>

<main id="contenu" tabindex="-1">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="wrap site-footer__grid">
        <div class="site-footer__about">
            <a href="<?= e(url('/')) ?>" class="brand brand--light" aria-label="<?= e($nomSite) ?>, accueil">
                <?= logo(56, true) ?>
                <span class="brand__text"><strong><?= e($nomSite) ?></strong></span>
            </a>
            <p>Comité de Recherches et d'Actions Multidimensionnelles pour la paix. Organisation non gouvernementale nationale, créée à Tombouctou en 2016.</p>
            <?php if ($reseaux !== []): ?>
                <ul class="socials" aria-label="Réseaux sociaux">
                    <?php foreach ($reseaux as $nom => $lien): ?>
                        <li><a href="<?= e($lien) ?>" target="_blank" rel="noopener noreferrer"><?= e($nom) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <nav aria-label="L'organisation">
            <p class="site-footer__title">L'organisation</p>
            <ul>
                <li><a href="<?= e(url('/qui-sommes-nous')) ?>">Qui sommes-nous</a></li>
                <li><a href="<?= e(url('/domaines-action')) ?>">Domaines d'action</a></li>
                <li><a href="<?= e(url('/projets')) ?>">Projets et réalisations</a></li>
                <li><a href="<?= e(url('/valeur-ajoutee')) ?>">Valeur ajoutée</a></li>
            </ul>
        </nav>
        <nav aria-label="Ressources">
            <p class="site-footer__title">Ressources</p>
            <ul>
                <li><a href="<?= e(url('/recherche')) ?>">Recherche et consortiums</a></li>
                <li><a href="<?= e(url('/actualites')) ?>">Actualités</a></li>
                <li><a href="<?= e(url('/partenaires')) ?>">Partenaires et bailleurs</a></li>
                <li><a href="<?= e(url('/contact')) ?>">Contact</a></li>
            </ul>
        </nav>
        <div>
            <p class="site-footer__title">Contact</p>
            <ul class="site-footer__contact">
                <?php if (setting('contact_adresse') !== ''): ?><li><?= icon('pin', 18) ?> <span><?= e(setting('contact_adresse')) ?></span></li><?php endif; ?>
                <?php if (setting('contact_telephone') !== ''): ?><li><?= icon('phone', 18) ?> <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('contact_telephone'))) ?>"><?= e(setting('contact_telephone')) ?></a></li><?php endif; ?>
                <?php if (setting('contact_email') !== ''): ?><li><?= icon('mail', 18) ?> <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></li><?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="wrap site-footer__bottom">
        <span>© <?= date('Y') ?> <?= e($nomSite) ?>. Tous droits réservés.</span>
        <a href="<?= e(url('/mentions-legales')) ?>">Mentions légales et confidentialité</a>
    </div>
</footer>
</body>
</html>

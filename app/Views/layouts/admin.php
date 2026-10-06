<?php
/**
 * Gabarit de l'administration : barre latérale, barre supérieure, messages, contenu.
 * @var string $content
 * @var string $titrePage
 * @var list<string> $fil
 */

use App\Core\Request;
use App\Core\Session;
use App\Models\Message;
use App\Services\Auth;

$admin = Auth::user() ?? ['nom' => 'Administrateur', 'email' => ''];
$nonLusGlobal = Message::unreadCount();
$chemin = Request::path();

// Modules dont la liste accepte une recherche : la barre supérieure cherche dans le module courant.
$recherches = [
    '/admin/projets'     => 'Rechercher un projet ou un bailleur…',
    '/admin/activites'   => 'Rechercher une activité, un lieu…',
    '/admin/partenaires' => 'Rechercher un partenaire…',
    '/admin/domaines'    => 'Rechercher un domaine…',
    '/admin/messages'    => 'Rechercher un message, un expéditeur…',
    '/admin/medias'      => 'Rechercher un fichier…',
];
$rechercheCible = null;
foreach ($recherches as $prefixe => $placeholder) {
    if ($chemin === $prefixe || str_starts_with($chemin, $prefixe . '/')) {
        $rechercheCible = [$prefixe, $placeholder];
    }
}

$navigation = [
    'Pilotage' => [
        ['/admin', 'Tableau de bord', 'dashboard', true],
        ['/admin/statistiques', 'Statistiques', 'chart', false],
    ],
    'Contenus' => [
        ['/admin/activites', 'Activités', 'calendar', false],
        ['/admin/projets', 'Projets', 'folder', false],
        ['/admin/domaines', "Domaines d'action", 'target', false],
        ['/admin/partenaires', 'Partenaires', 'users', false],
        ['/admin/pages', 'Pages', 'file', false],
        ['/admin/medias', 'Médias', 'image', false],
    ],
    'Échanges' => [
        ['/admin/messages', 'Messages', 'mail', false],
    ],
    'Administration' => [
        ['/admin/parametres', 'Paramètres', 'sliders', false],
        ['/admin/compte', 'Mon compte', 'user', false],
    ],
];
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titrePage ?? 'Administration') ?> · Administration CRAM-CRAM</title>
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
</head>
<body class="admin-body">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<div class="admin" data-admin>

<aside class="sidebar" id="sidebar" aria-label="Menu d'administration">
    <div class="sidebar__brand">
        <?= logo(40, true) ?>
        <span class="sidebar__brand-text">
            <strong>CRAM-CRAM</strong>
            <span>Espace d'administration</span>
        </span>
        <button type="button" class="sidebar__close icon-btn icon-btn--dark" data-sidebar-close aria-label="Fermer le menu"><?= icon('x') ?></button>
    </div>
    <nav class="sidebar__nav">
        <?php foreach ($navigation as $groupe => $liens): ?>
            <p class="sidebar__label"><?= e($groupe) ?></p>
            <?php foreach ($liens as [$href, $libelle, $icone, $exact]): ?>
                <a href="<?= e(url($href)) ?>" class="sidebar__link<?= nav_active($href, $exact) ?>"<?= aria_current($href, $exact) ?>>
                    <?= icon($icone) ?>
                    <span class="sidebar__text"><?= e($libelle) ?></span>
                    <?php if ($href === '/admin/messages' && $nonLusGlobal > 0): ?>
                        <span class="sidebar__badge" aria-label="<?= e(pluriel($nonLusGlobal, 'message non lu', 'messages non lus')) ?>"><?= $nonLusGlobal ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <a href="<?= e(url('/')) ?>" class="sidebar__site" target="_blank" rel="noopener"><?= icon('external', 18) ?> Voir le site public</a>
    <div class="sidebar__user">
        <span class="avatar avatar--light" aria-hidden="true"><?= e(Auth::initials((string) $admin['nom'])) ?></span>
        <span class="sidebar__user-text">
            <strong><?= e($admin['nom']) ?></strong>
            <span><?= e($admin['email']) ?></span>
        </span>
        <form method="post" action="<?= e(url('/admin/deconnexion')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="icon-btn icon-btn--dark" aria-label="Se déconnecter" title="Se déconnecter"><?= icon('logout', 18) ?></button>
        </form>
    </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close hidden></div>

<div class="admin__main">
    <header class="topbar">
        <button type="button" class="icon-btn topbar__menu" data-sidebar-open aria-controls="sidebar" aria-expanded="false" aria-label="Ouvrir le menu"><?= icon('menu') ?></button>
        <nav class="topbar__crumbs" aria-label="Fil d'Ariane">
            <?php foreach (($fil ?? ['Administration']) as $i => $etape): ?>
                <?php if ($i > 0): ?><?= icon('chevron', 14) ?><?php endif; ?>
                <span<?= $i === count($fil ?? []) - 1 ? ' aria-current="page"' : '' ?>><?= e($etape) ?></span>
            <?php endforeach; ?>
        </nav>
        <?php if ($rechercheCible): ?>
            <form class="topbar__search" method="get" action="<?= e(url($rechercheCible[0])) ?>" role="search">
                <?= icon('search', 18) ?>
                <label class="sr-only" for="recherche-globale">Rechercher</label>
                <input id="recherche-globale" type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e($rechercheCible[1]) ?>" autocomplete="off">
            </form>
        <?php else: ?>
            <span class="topbar__spacer"></span>
        <?php endif; ?>
        <div class="topbar__actions">
            <a href="<?= e(url('/admin/messages?filtre=non_lus')) ?>" class="icon-btn icon-btn--outline topbar__bell" aria-label="<?= $nonLusGlobal > 0 ? e(pluriel($nonLusGlobal, 'message non lu', 'messages non lus')) : 'Aucun nouveau message' ?>">
                <?= icon('bell') ?>
                <?php if ($nonLusGlobal > 0): ?><span class="topbar__dot"></span><?php endif; ?>
            </a>
            <a href="<?= e(url('/admin/compte')) ?>" class="topbar__user">
                <span class="avatar" aria-hidden="true"><?= e(Auth::initials((string) $admin['nom'])) ?></span>
                <span class="topbar__user-name"><?= e($admin['nom']) ?></span>
            </a>
        </div>
    </header>

    <main class="page" id="contenu" tabindex="-1">
        <?= $content ?>
    </main>
</div>
</div>

<div class="toasts" aria-live="polite">
    <?php foreach (Session::flashes() as $flash): ?>
        <div class="toast toast--<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'erreur' ? 'alert' : 'status' ?>" data-toast>
            <span class="toast__icon"><?= icon($flash['type'] === 'succes' ? 'check' : ($flash['type'] === 'erreur' ? 'alert' : 'info'), 18) ?></span>
            <p class="toast__text"><?= e($flash['message']) ?></p>
            <button type="button" class="toast__close" aria-label="Fermer la notification" data-toast-close><?= icon('x', 16) ?></button>
        </div>
    <?php endforeach; ?>
</div>

<dialog class="modal" id="confirm-dialog" aria-labelledby="confirm-title" aria-describedby="confirm-text">
    <form method="dialog" class="modal__box">
        <span class="modal__icon"><?= icon('trash', 24) ?></span>
        <h2 class="modal__title" id="confirm-title">Confirmer la suppression</h2>
        <p class="modal__text" id="confirm-text"></p>
        <div class="modal__actions">
            <button type="submit" value="annuler" class="btn btn--secondary" autofocus>Annuler</button>
            <button type="submit" value="confirmer" class="btn btn--danger-solid" data-confirm-ok>Supprimer définitivement</button>
        </div>
    </form>
</dialog>

<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>

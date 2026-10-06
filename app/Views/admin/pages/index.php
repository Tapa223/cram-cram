<?php

use App\Models\Page;

$descriptions = [
    'qui-sommes-nous'  => 'Historique, mission, vision, ancrage et gouvernance.',
    'recherche'        => 'Démarche de recherche-action et consortium. Les PDF publiés depuis la médiathèque s\'affichent sous ce texte.',
    'valeur-ajoutee'   => 'Texte complémentaire. Les trois arguments se modifient dans Paramètres › Valeur ajoutée.',
    'mentions-legales' => 'Éditeur, hébergeur, données personnelles et cookies. À faire relire avant la mise en ligne.',
];
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Pages</h1>
        <p class="page-sub">Les pages de présentation du site. Leur contenu se modifie ici ; leur adresse reste fixe.</p>
    </div>
</div>

<div class="page-list">
    <?php foreach ($pages as $page): ?>
        <?php $vide = trim(strip_tags((string) $page['contenu'])) === ''; ?>
        <article class="page-card">
            <div class="page-card__top">
                <span class="page-card__icon"><?= icon('file', 22) ?></span>
                <?= $vide ? '<span class="badge badge--navy">Contenu à rédiger</span>' : '<span class="state state--on">Publiée</span>' ?>
            </div>
            <h2 class="page-card__title"><?= e($page['titre']) ?></h2>
            <p class="card__text"><?= e($descriptions[$page['slug']] ?? '') ?></p>
            <p class="page-card__meta">Modifiée le <?= e(date_fr((string) $page['modifie_le'])) ?><?= $page['modifie_par_nom'] ? ' par ' . e($page['modifie_par_nom']) : '' ?></p>
            <div class="page-card__actions">
                <a class="btn btn--primary btn--sm" href="<?= e(url('/admin/pages/' . (int) $page['id'] . '/modifier')) ?>"><?= icon('edit', 16) ?> Modifier</a>
                <?php if (isset(Page::ADRESSES[$page['slug']])): ?>
                    <a class="btn btn--secondary btn--sm" href="<?= e(url(Page::ADRESSES[$page['slug']])) ?>" target="_blank" rel="noopener"><?= icon('external', 16) ?> Voir la page</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<?php

use App\Core\View;

/** @var array<string, mixed> $domaine */
?>
<section class="detail-hero">
    <div class="wrap detail-hero__grid">
        <div class="reveal">
            <?= View::partial('partials/public/retour', ['href' => '/domaines-action']) ?>
            <p class="detail-hero__tags"><span class="tag tag--static tag--domaine"><?= icon(domaine_icone($domaine['slug']), 16) ?> Domaine <?= str_pad((string) $numero, 2, '0', STR_PAD_LEFT) ?></span></p>
            <h1 class="page-hero__title"><?= e($domaine['titre']) ?></h1>
            <p class="page-hero__lead"><?= e($domaine['resume']) ?></p>
        </div>
        <div class="detail-hero__media reveal">
            <?= View::partial('partials/public/media', ['fichier' => $domaine['image_fichier'], 'alt' => $domaine['image_alt'] ?: $domaine['titre'], 'ton' => 'clair', 'icone' => domaine_icone($domaine['slug'])]) ?>
        </div>
    </div>
</section>

<section class="section section--first">
    <div class="wrap layout-aside">
        <div class="stack-lg">
            <?php if (trim(strip_tags((string) $domaine['description'])) !== ''): ?>
                <div class="prose reveal"><?= $domaine['description'] ?></div>
            <?php endif; ?>

            <?= View::partial('partials/public/gallery', ['galerie' => $galerie, 'titre' => 'En images']) ?>

            <?php if ($projets !== []): ?>
                <div class="reveal">
                    <h2 class="block-title">Projets dans ce domaine</h2>
                    <div class="grid-2">
                        <?php foreach ($projets as $i => $p): ?>
                            <?= View::partial('partials/public/projet-card', ['p' => $p, 'i' => $i]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($activites !== []): ?>
                <div class="reveal">
                    <h2 class="block-title">Activités récentes</h2>
                    <div class="grid-3">
                        <?php foreach ($activites as $a): ?>
                            <?= View::partial('partials/public/actu-card', ['a' => $a]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <aside class="stack">
            <div class="aside-card reveal">
                <p class="eyebrow">Autres domaines</p>
                <ul class="aside-links">
                    <?php foreach ($autres as $a): ?>
                        <li><a href="<?= e(url('/domaines-action/' . $a['slug'])) ?>"><?= e($a['titre']) ?> <?= icon('chevron', 16) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="aside-card aside-card--navy reveal">
                <p class="aside-card__title">Envie de collaborer dans ce domaine ?</p>
                <p class="aside-card__text">Partenariat, recherche ou échange d'expériences : parlons-en.</p>
                <a class="btn btn--light" href="<?= e(url('/contact')) ?>">Nous contacter</a>
            </div>
        </aside>
    </div>
</section>

<?php

use App\Core\View;

/** @var array<string, mixed> $activite */
?>
<article>
    <section class="page-hero page-hero--article">
        <div class="wrap wrap--narrow">
            <?= View::partial('partials/public/retour', ['href' => '/actualites']) ?>
            <p class="detail-hero__tags"><span class="tag tag--static tag--domaine"><?= e($activite['categorie']) ?></span></p>
            <h1 class="page-hero__title"><?= e($activite['titre']) ?></h1>
            <p class="article-meta">
                <span><?= icon('calendar', 18) ?> <?= e(date_fr((string) $activite['date_activite'])) ?></span>
                <?php if ($activite['lieu']): ?><span><?= icon('pin', 18) ?> <?= e($activite['lieu']) ?></span><?php endif; ?>
                <?php if ($activite['domaine_titre']): ?><span><?= icon('target', 18) ?> <a href="<?= e(url('/domaines-action/' . $activite['domaine_slug'])) ?>"><?= e($activite['domaine_titre']) ?></a></span><?php endif; ?>
            </p>
        </div>
    </section>

    <section class="section section--first">
        <div class="wrap wrap--narrow stack-lg">
            <?php if ($activite['image_fichier']): ?>
                <figure class="article-figure reveal">
                    <?= View::partial('partials/public/media', ['fichier' => $activite['image_fichier'], 'alt' => $activite['image_alt'] ?: $activite['titre']]) ?>
                </figure>
            <?php endif; ?>
            <?php if ($activite['resume']): ?><p class="lead lead--strong"><?= e($activite['resume']) ?></p><?php endif; ?>
            <div class="prose"><?= $activite['contenu'] ?></div>
            <?= View::partial('partials/public/gallery', ['galerie' => $galerie, 'titre' => 'En images']) ?>
        </div>
    </section>
</article>

<?php if ($autres !== []): ?>
<section class="section section--tint">
    <div class="wrap">
        <div class="section-head reveal">
            <h2 class="section-title">Autres actualités</h2>
            <a class="link-arrow" href="<?= e(url('/actualites')) ?>">Toutes les actualités <?= icon('arrow', 16) ?></a>
        </div>
        <div class="grid-3">
            <?php foreach ($autres as $a): ?>
                <div class="reveal"><?= View::partial('partials/public/actu-card', ['a' => $a]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

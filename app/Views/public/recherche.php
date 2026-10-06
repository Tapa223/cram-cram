<?php

use App\Core\View;
use App\Services\MediaUploader;

/** @var array<string, mixed> $page @var list<array<string, mixed>> $publications */
?>
<?= View::partial('partials/public/page-hero', ['titre' => $page['titre'], 'chapo' => $page['chapo']]) ?>

<section class="section section--first">
    <div class="wrap layout-aside">
        <div class="prose reveal"><?= $page['contenu'] ?></div>
        <aside class="aside-card reveal">
            <p class="eyebrow">Publications</p>
            <?php if ($publications === []): ?>
                <p class="aside-card__title">Les premières publications seront bientôt disponibles.</p>
                <p class="aside-card__text">Notes d'analyse, bulletins et études sont publiés ici au fil de leur parution. Pour une demande spécifique, écrivez-nous.</p>
                <a class="btn btn--outline" href="<?= e(url('/contact')) ?>">Nous contacter</a>
            <?php else: ?>
                <p class="aside-card__title"><?= pluriel(count($publications), 'document disponible', 'documents disponibles') ?></p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php if ($publications !== []): ?>
<section class="section section--tint">
    <div class="wrap">
        <div class="section-head reveal"><h2 class="section-title">Publications</h2></div>
        <ul class="docs">
            <?php foreach ($publications as $doc): ?>
                <li class="doc reveal">
                    <span class="doc__icon"><?= icon('file', 24) ?></span>
                    <span class="doc__text">
                        <strong><?= e($doc['titre'] ?: $doc['nom_original']) ?></strong>
                        <span>PDF · <?= e(MediaUploader::humanSize((int) $doc['taille'])) ?> · <?= e(date_fr((string) $doc['cree_le'])) ?></span>
                    </span>
                    <a class="btn btn--outline btn--sm" href="<?= e(upload_url((string) $doc['fichier'])) ?>" download><?= icon('download', 16) ?> Télécharger</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

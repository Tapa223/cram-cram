<?php

use App\Core\View;

/** @var list<array<string, mixed>> $domaines */
?>
<?= View::partial('partials/public/page-hero', [
    'titre'   => "Domaines d'action",
]) ?>

<section class="section section--first">
    <div class="wrap">
        <?php if ($domaines === []): ?>
            <p class="lead">Les domaines d'action seront bientôt présentés ici.</p>
        <?php endif; ?>
        <div class="domaine-rows">
            <?php foreach ($domaines as $i => $d): ?>
                <article class="domaine-row<?= $i % 2 ? ' domaine-row--reverse' : '' ?> reveal">
                    <div class="domaine-row__media">
                        <?= View::partial('partials/public/media', ['fichier' => $d['image_fichier'], 'alt' => $d['image_alt'] ?: $d['titre'], 'ton' => $i % 2 ? 'nuit' : 'bleu', 'icone' => domaine_icone($d['slug'])]) ?>
                    </div>
                    <div class="domaine-row__body">
                        <div class="domaine-card__top domaine-card__top--start">
                            <span class="domaine-card__icon"><?= icon(domaine_icone($d['slug']), 26) ?></span>
                            <span class="domaine-card__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?> / <?= str_pad((string) count($domaines), 2, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <h2 class="domaine-row__title"><a class="stretched" href="<?= e(url('/domaines-action/' . $d['slug'])) ?>"><?= e($d['titre']) ?></a></h2>
                        <p class="domaine-row__text"><?= e($d['resume']) ?></p>
                        <p class="domaine-row__meta">
                            <span class="link-arrow" aria-hidden="true">Découvrir ce domaine <?= icon('arrow', 16) ?></span>
                            <?php if ((int) $d['nb_projets'] > 0): ?><span class="chip chip--soft"><?= pluriel((int) $d['nb_projets'], 'projet') ?></span><?php endif; ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= View::partial('partials/public/cta') ?>

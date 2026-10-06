<?php

use App\Core\View;

/** @var array<string, mixed> $page */
$valeurs = [];
for ($n = 1; $n <= 3; $n++) {
    if (setting('valeur_' . $n . '_titre') !== '') {
        $valeurs[] = ['titre' => setting('valeur_' . $n . '_titre'), 'texte' => setting('valeur_' . $n . '_texte')];
    }
}
?>
<?= View::partial('partials/public/page-hero', ['titre' => $page['titre']]) ?>

<section class="section section--first">
    <div class="wrap">
        <div class="valeurs-list">
            <?php foreach ($valeurs as $i => $v): ?>
                <article class="valeur-row reveal">
                    <div class="valeur-row__side">
                        <span class="valeur-row__icon"><?= icon(['eye', 'network', 'globe'][$i] ?? 'check', 30) ?></span>
                        <span class="valeur-row__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <h2 class="valeur-row__title"><?= e($v['titre']) ?></h2>
                    <p class="valeur-row__text"><?= e($v['texte']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (trim(strip_tags((string) $page['contenu'])) !== ''): ?>
            <div class="prose prose--narrow reveal"><?= $page['contenu'] ?></div>
        <?php endif; ?>
    </div>
</section>

<?= View::partial('partials/public/cta') ?>

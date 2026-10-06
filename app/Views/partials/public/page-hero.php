<?php
/**
 * En-tête compact de page intérieure.
 * @var string $titre
 * @var string|null $chapo
 * @var string|null $retour  adresse de la page parente (accueil par défaut)
 */
?>
<section class="page-hero">
    <div class="wrap page-hero__inner">
        <div class="page-hero__row">
            <?= \App\Core\View::partial('partials/public/retour', ['href' => $retour ?? '/']) ?>
            <h1 class="page-hero__title"><?= e($titre) ?></h1>
        </div>
        <?php if (!empty($chapo)): ?><p class="page-hero__lead"><?= e($chapo) ?></p><?php endif; ?>
    </div>
</section>

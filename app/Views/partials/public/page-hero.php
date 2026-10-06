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
        <?= \App\Core\View::partial('partials/public/retour', ['href' => $retour ?? '/']) ?>
        <div class="page-hero__text">
            <h1 class="page-hero__title"><?= e($titre) ?></h1>
            <?php if (!empty($chapo)): ?><p class="page-hero__lead"><?= e($chapo) ?></p><?php endif; ?>
        </div>
    </div>
</section>

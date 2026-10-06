<?php
/**
 * État vide réutilisable.
 * @var string $icone
 * @var string $titre
 * @var string $texte
 * @var string|null $actionHref
 * @var string|null $actionLibelle
 */
?>
<div class="empty">
    <span class="empty__icon"><?= icon($icone ?? 'inbox', 26) ?></span>
    <p class="empty__title"><?= e($titre) ?></p>
    <p class="empty__text"><?= e($texte) ?></p>
    <?php if (!empty($actionHref)): ?>
        <a class="btn btn--secondary" href="<?= e(url($actionHref)) ?>"><?= e($actionLibelle ?? 'Continuer') ?></a>
    <?php endif; ?>
</div>

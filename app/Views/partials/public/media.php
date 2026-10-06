<?php
/**
 * Image d'un contenu ou, tant qu'aucune photo réelle n'est fournie,
 * une illustration provisoire aux couleurs de la charte, choisie selon le domaine.
 * @var string|null $fichier
 * @var string|null $alt
 * @var string $classe
 * @var string $icone  pictogramme du domaine (sert à choisir l'illustration)
 */
$classe = $classe ?? '';
?>
<?php if (!empty($fichier)): ?>
    <img class="media <?= e($classe) ?>" src="<?= e(upload_url($fichier)) ?>" alt="<?= e($alt ?? '') ?>" loading="lazy" decoding="async">
<?php else: ?>
    <img class="media media--illus <?= e($classe) ?>" src="<?= e(illustration($icone ?? null)) ?>" alt="" loading="lazy" decoding="async">
<?php endif; ?>

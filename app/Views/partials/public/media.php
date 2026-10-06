<?php
/**
 * Image d'un contenu ou, tant qu'aucune photo réelle n'est fournie,
 * une couverture graphique aux couleurs de la charte avec un pictogramme.
 * @var string|null $fichier
 * @var string|null $alt
 * @var string $classe
 * @var string $ton    « bleu », « nuit » ou « clair »
 * @var string $icone  pictogramme affiché sur la couverture
 */
$classe = $classe ?? '';
$ton = in_array($ton ?? '', ['bleu', 'nuit', 'clair'], true) ? $ton : 'bleu';
?>
<?php if (!empty($fichier)): ?>
    <img class="media <?= e($classe) ?>" src="<?= e(upload_url($fichier)) ?>" alt="<?= e($alt ?? '') ?>" loading="lazy" decoding="async">
<?php else: ?>
    <div class="media media--cover media--<?= e($ton) ?> <?= e($classe) ?>" aria-hidden="true">
        <svg class="media__pattern" viewBox="0 0 400 250" preserveAspectRatio="xMidYMid slice" focusable="false">
            <circle cx="340" cy="40" r="120" fill="none" stroke="currentColor" stroke-opacity=".16" stroke-width="1.5"/>
            <circle cx="340" cy="40" r="80" fill="none" stroke="currentColor" stroke-opacity=".12" stroke-width="1.5"/>
            <circle cx="340" cy="40" r="40" fill="currentColor" fill-opacity=".08"/>
            <circle cx="40" cy="240" r="90" fill="none" stroke="currentColor" stroke-opacity=".10" stroke-width="1.5"/>
        </svg>
        <span class="media__icon"><?= icon($icone ?? 'peace', 40) ?></span>
    </div>
<?php endif; ?>

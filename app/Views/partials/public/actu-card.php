<?php

use App\Core\View;

/** @var array<string, mixed> $a @var bool $grand */
?>
<article class="card-actu<?= !empty($grand) ? ' card-actu--grand' : '' ?>">
    <div class="card-actu__media">
        <?= View::partial('partials/public/media', ['fichier' => $a['image_fichier'] ?? null, 'alt' => $a['image_alt'] ?? $a['titre'], 'ton' => !empty($grand) ? 'nuit' : 'bleu', 'icone' => 'megaphone']) ?>
    </div>
    <p class="kicker"><?= e(implode(' · ', array_filter([$a['categorie'], date_fr((string) $a['date_activite']), $a['lieu'] ?? '']))) ?></p>
    <h3 class="card-actu__title"><a class="stretched" href="<?= e(url('/actualites/' . $a['slug'])) ?>"><?= e($a['titre']) ?></a></h3>
    <?php if (!empty($a['resume'])): ?><p class="card-actu__text"><?= e($a['resume']) ?></p><?php endif; ?>
</article>

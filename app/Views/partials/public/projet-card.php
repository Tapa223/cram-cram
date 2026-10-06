<?php

use App\Core\View;
use App\Models\Projet;

/** @var array<string, mixed> $p @var int $i */
?>
<article class="card-projet">
    <div class="card-projet__media">
        <?= View::partial('partials/public/media', ['fichier' => $p['image_fichier'] ?? null, 'alt' => $p['image_alt'] ?? $p['titre'], 'ton' => ['bleu', 'nuit', 'bleu'][($i ?? 0) % 3], 'icone' => domaine_icone($p['domaine_slug'] ?? null)]) ?>
        <?php if (!empty($p['statut'])): ?>
            <span class="tag tag--<?= e($p['statut']) ?>"><?= e(Projet::STATUTS[$p['statut']]) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-projet__body">
        <?php if (!empty($p['domaine_titre'])): ?><p class="kicker"><?= e($p['domaine_titre']) ?></p><?php endif; ?>
        <h3 class="card-projet__title"><a class="stretched" href="<?= e(url('/projets/' . $p['slug'])) ?>"><?= e($p['titre']) ?></a></h3>
        <p class="card-projet__text"><?= e($p['resume']) ?></p>
        <?php $periode = periode_projet($p['date_debut'] ?? null, $p['date_fin'] ?? null); ?>
        <div class="card-projet__foot">
            <?php if (!empty($p['bailleurs'])): ?>
                <span class="card-projet__bailleurs"><?= icon('handshake', 16) ?> <?= e($p['bailleurs']) ?></span>
            <?php endif; ?>
            <?php if ($periode !== '' || !empty($p['zone'])): ?>
                <span class="card-projet__meta"><?= e(implode(' · ', array_filter([$periode, $p['zone'] ?? '']))) ?></span>
            <?php endif; ?>
            <span class="link-arrow link-arrow--sm" aria-hidden="true">Voir le projet <?= icon('arrow', 16) ?></span>
        </div>
    </div>
</article>

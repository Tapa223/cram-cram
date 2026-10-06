<?php

use App\Core\View;
use App\Models\Partenaire;
use App\Models\Projet;

/** @var array<string, mixed> $projet */
$periode = periode_projet($projet['date_debut'], $projet['date_fin']);
?>
<section class="detail-hero">
    <div class="wrap detail-hero__grid">
        <div class="reveal">
            <?= View::partial('partials/public/retour', ['href' => '/projets']) ?>
            <p class="detail-hero__tags">
                <?php if ($projet['statut']): ?><span class="tag tag--<?= e($projet['statut']) ?> tag--static"><?= e(Projet::STATUTS[$projet['statut']]) ?></span><?php endif; ?>
                <?php if ($projet['domaine_titre']): ?><a class="tag tag--static tag--domaine" href="<?= e(url('/domaines-action/' . $projet['domaine_slug'])) ?>"><?= e($projet['domaine_titre']) ?></a><?php endif; ?>
            </p>
            <h1 class="page-hero__title"><?= e($projet['titre']) ?></h1>
            <p class="page-hero__lead"><?= e($projet['resume']) ?></p>
        </div>
        <div class="detail-hero__media reveal">
            <?= View::partial('partials/public/media', ['fichier' => $projet['image_fichier'], 'alt' => $projet['image_alt'] ?: $projet['titre'], 'ton' => 'clair', 'icone' => domaine_icone($projet['domaine_slug'] ?? null)]) ?>
        </div>
    </div>
</section>

<section class="section section--first">
    <div class="wrap layout-aside">
        <div class="stack-lg">
            <?php if (trim(strip_tags((string) $projet['description'])) !== ''): ?>
                <div class="prose reveal"><?= $projet['description'] ?></div>
            <?php endif; ?>
            <?= View::partial('partials/public/gallery', ['galerie' => $galerie, 'titre' => 'Le projet en images']) ?>
        </div>
        <aside class="stack">
            <div class="aside-card reveal">
                <p class="eyebrow">Le projet en bref</p>
                <dl class="facts facts--compact">
                    <?php if ($partenaires !== []): ?>
                        <div class="facts__row"><dt><?= count($partenaires) > 1 ? 'Partenaires financiers' : 'Partenaire financier' ?></dt><dd><?php foreach ($partenaires as $i => $pa): ?><?= $i > 0 ? ', ' : '' ?><?= e($pa['nom']) ?><?php endforeach; ?></dd></div>
                    <?php endif; ?>
                    <?php if ($projet['domaine_titre']): ?><div class="facts__row"><dt>Domaine</dt><dd><?= e($projet['domaine_titre']) ?></dd></div><?php endif; ?>
                    <?php if ($projet['zone']): ?><div class="facts__row"><dt>Zone</dt><dd><?= e($projet['zone']) ?></dd></div><?php endif; ?>
                    <?php if ($periode !== ''): ?><div class="facts__row"><dt>Période</dt><dd><?= e($periode) ?></dd></div><?php endif; ?>
                </dl>
            </div>
            <div class="aside-card aside-card--navy reveal">
                <p class="aside-card__title">Soutenir ou rejoindre ce type de projet</p>
                <p class="aside-card__text">Bailleurs, partenaires techniques et organisations locales : contactez notre équipe.</p>
                <a class="btn btn--light" href="<?= e(url('/contact')) ?>">Nous contacter</a>
            </div>
        </aside>
    </div>
</section>

<?php if ($autres !== []): ?>
<section class="section section--tint">
    <div class="wrap">
        <div class="section-head reveal">
            <h2 class="section-title">Autres projets</h2>
            <a class="link-arrow" href="<?= e(url('/projets')) ?>">Tous les projets <?= icon('arrow', 16) ?></a>
        </div>
        <div class="grid-3">
            <?php foreach ($autres as $i => $p): ?>
                <div class="reveal"><?= View::partial('partials/public/projet-card', ['p' => $p, 'i' => $i + 1]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

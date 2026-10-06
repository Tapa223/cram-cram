<?php

use App\Core\View;
use App\Models\Partenaire;

/** @var array<string, list<array<string, mixed>>> $groupes */
$nonVides = array_filter($groupes);
?>
<?= View::partial('partials/public/page-hero', [
    'titre'   => 'Partenaires et bailleurs',
]) ?>

<section class="section section--first">
    <div class="wrap">
        <?php if ($nonVides === []): ?>
            <p class="lead">La liste de nos partenaires sera bientôt publiée.</p>
        <?php else: ?>
            <div class="tabs-public" data-tabs>
                <div class="tabs-public__list" role="tablist" aria-label="Catégories de partenaires">
                    <?php $first = true; foreach ($nonVides as $cle => $liste): ?>
                        <button type="button" class="tabs-public__tab<?= $first ? ' is-active' : '' ?>" role="tab" id="tab-<?= e($cle) ?>" aria-controls="panel-<?= e($cle) ?>" aria-selected="<?= $first ? 'true' : 'false' ?>"<?= $first ? '' : ' tabindex="-1"' ?>>
                            <?= e(Partenaire::CATEGORIES[$cle]) ?> <span class="tabs-public__count"><?= count($liste) ?></span>
                        </button>
                    <?php $first = false; endforeach; ?>
                </div>
                <?php $first = true; foreach ($nonVides as $cle => $liste): ?>
                    <section class="tabs-public__panel" role="tabpanel" id="panel-<?= e($cle) ?>" aria-labelledby="tab-<?= e($cle) ?>" tabindex="0">
                        <h2 class="block-title tabs-public__heading"><?= e(Partenaire::CATEGORIES[$cle]) ?></h2>
                        <ul class="partner-grid">
                            <?php foreach ($liste as $pa): ?>
                                <li class="partner">
                                    <div class="partner__head">
                                        <span class="partner__logo">
                                            <?php if (!empty($pa['logo_fichier'])): ?>
                                                <img src="<?= e(upload_url((string) $pa['logo_fichier'])) ?>" alt="Logo <?= e($pa['nom']) ?>" loading="lazy">
                                            <?php else: ?>
                                                <span class="logo-tile__mono" aria-hidden="true"><?= e(monogramme($pa['sigle'], (string) $pa['nom'])) ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <p class="partner__name"><?= e($pa['sigle'] ?: $pa['nom']) ?></p>
                                    </div>
                                    <?php if ($pa['sigle']): ?><p class="partner__detail"><?= e($pa['nom']) ?></p><?php endif; ?>
                                    <?php if ($pa['description']): ?><p class="partner__detail"><?= e($pa['description']) ?></p><?php endif; ?>
                                    <p class="partner__foot">
                                        <?php if ($pa['pays']): ?><span class="chip chip--soft"><?= e($pa['pays']) ?></span><?php endif; ?>
                                        <?php if ($pa['site_web']): ?><a class="link-arrow link-arrow--sm" href="<?= e($pa['site_web']) ?>" target="_blank" rel="noopener noreferrer">Site web <?= icon('external', 14) ?></a><?php endif; ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php $first = false; endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= View::partial('partials/public/cta') ?>

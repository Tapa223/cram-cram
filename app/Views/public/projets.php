<?php

use App\Core\View;
use App\Models\Projet;

$filtre = static function (string $statut, string $domaine): string {
    $q = array_filter(['statut' => $statut, 'domaine' => $domaine], static fn (string $v): bool => $v !== '');
    return url('/projets') . ($q ? '?' . http_build_query($q) : '');
};
?>
<?= View::partial('partials/public/page-hero', [
    'titre'   => 'Nos projets',
]) ?>

<section class="section section--first">
    <div class="wrap">
        <div class="filters reveal">
            <?php if ($statutsUtilises !== []): ?>
                <nav class="filters__group" aria-label="Filtrer par statut">
                    <a class="filter<?= $statut === '' ? ' is-active' : '' ?>" href="<?= e($filtre('', $domaineSlug)) ?>"<?= $statut === '' ? ' aria-current="true"' : '' ?>>Tous</a>
                    <?php foreach ($statutsUtilises as $cle): ?>
                        <a class="filter<?= $statut === $cle ? ' is-active' : '' ?>" href="<?= e($filtre($cle, $domaineSlug)) ?>"<?= $statut === $cle ? ' aria-current="true"' : '' ?>><?= e(Projet::STATUTS[$cle]) ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <form class="filters__select" method="get" action="<?= e(url('/projets')) ?>" data-autosubmit>
                <?php if ($statut !== ''): ?><input type="hidden" name="statut" value="<?= e($statut) ?>"><?php endif; ?>
                <label for="filtre-domaine" class="sr-only">Domaine d'action</label>
                <select id="filtre-domaine" name="domaine" class="select">
                    <option value="">Tous les domaines d'action</option>
                    <?php foreach ($domaines as $d): ?>
                        <option value="<?= e($d['slug']) ?>"<?= $domaineSlug === $d['slug'] ? ' selected' : '' ?>><?= e($d['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn--outline btn--sm js-hide">Filtrer</button>
            </form>
        </div>

        <?php if ($projets === []): ?>
            <div class="empty-state reveal">
                <span class="empty-state__icon"><?= icon('folder', 28) ?></span>
                <p class="empty-state__title">Aucun projet ne correspond à ce filtre.</p>
                <a class="btn btn--outline" href="<?= e(url('/projets')) ?>">Voir tous les projets</a>
            </div>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($projets as $i => $p): ?>
                    <div class="reveal"><?= View::partial('partials/public/projet-card', ['p' => $p, 'i' => $i]) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= View::partial('partials/public/cta') ?>

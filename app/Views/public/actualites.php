<?php

use App\Core\View;

$lien = static function (string $categorie, int $page = 1): string {
    $q = array_filter(['categorie' => $categorie, 'page' => $page > 1 ? (string) $page : ''], static fn (string $v): bool => $v !== '');
    return url('/actualites') . ($q ? '?' . http_build_query($q) : '');
};
?>
<?= View::partial('partials/public/page-hero', [
    'titre'   => 'Actualités',
]) ?>

<section class="section section--first">
    <div class="wrap">
        <?php if ($categories !== []): ?>
            <nav class="filters__group reveal" aria-label="Filtrer par catégorie">
                <a class="filter<?= $categorie === '' ? ' is-active' : '' ?>" href="<?= e($lien('')) ?>">Toutes</a>
                <?php foreach ($categories as $c): ?>
                    <a class="filter<?= $categorie === $c ? ' is-active' : '' ?>" href="<?= e($lien($c)) ?>"<?= $categorie === $c ? ' aria-current="true"' : '' ?>><?= e($c) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($activites === []): ?>
            <div class="empty-state reveal">
                <span class="empty-state__icon"><?= icon('calendar', 28) ?></span>
                <p class="empty-state__title"><?= $categorie !== '' ? 'Aucune actualité dans cette catégorie.' : 'Les premières actualités seront publiées très prochainement.' ?></p>
                <p class="empty-state__text">Suivez nos domaines d'action et nos projets en attendant.</p>
                <a class="btn btn--outline" href="<?= e(url('/projets')) ?>">Découvrir nos projets</a>
            </div>
        <?php else: ?>
            <div class="actus actus--page">
                <?php foreach ($activites as $i => $a): ?>
                    <div class="reveal"><?= View::partial('partials/public/actu-card', ['a' => $a, 'grand' => $i === 0 && $pager->current() === 1]) ?></div>
                <?php endforeach; ?>
            </div>
            <?php if ($pager->pages > 1): ?>
                <nav class="pager" aria-label="Pagination">
                    <?php for ($n = 1; $n <= $pager->pages; $n++): ?>
                        <?php if ($n === $pager->current()): ?>
                            <span class="pager__link is-current" aria-current="page"><?= $n ?></span>
                        <?php else: ?>
                            <a class="pager__link" href="<?= e($lien($categorie, $n)) ?>"><?= $n ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

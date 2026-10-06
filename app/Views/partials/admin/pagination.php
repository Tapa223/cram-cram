<?php
/**
 * @var App\Core\Paginator $pager
 * @var string $libelle ex. « projets »
 */
?>
<div class="table-foot">
    <span class="table-foot__info">
        <?php if ($pager->total === 0): ?>
            Aucun résultat
        <?php else: ?>
            <?= $pager->from() ?>–<?= $pager->to() ?> sur <?= $pager->total ?> <?= e($libelle) ?>
        <?php endif; ?>
    </span>
    <?php if ($pager->pages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php if ($pager->current() > 1): ?>
                <a class="pagination__link" href="<?= e($pager->link($pager->current() - 1)) ?>">Précédent</a>
            <?php else: ?>
                <span class="pagination__link is-disabled" aria-disabled="true">Précédent</span>
            <?php endif; ?>
            <?php for ($n = max(1, $pager->current() - 2); $n <= min($pager->pages, $pager->current() + 2); $n++): ?>
                <?php if ($n === $pager->current()): ?>
                    <span class="pagination__link is-current" aria-current="page"><?= $n ?></span>
                <?php else: ?>
                    <a class="pagination__link" href="<?= e($pager->link($n)) ?>"><?= $n ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pager->current() < $pager->pages): ?>
                <a class="pagination__link" href="<?= e($pager->link($pager->current() + 1)) ?>">Suivant</a>
            <?php else: ?>
                <span class="pagination__link is-disabled" aria-disabled="true">Suivant</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>

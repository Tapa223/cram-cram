<?php

use App\Core\View;

$total = count($domaines);
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Domaines d'action <span class="count-pill"><?= $total ?></span></h1>
        <p class="page-sub">Les axes d'intervention présentés sur le site. L'ordre ci-dessous est celui de l'affichage public.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--primary" href="<?= e(url('/admin/domaines/nouveau')) ?>"><?= icon('plus', 18) ?> Nouveau domaine</a>
    </div>
</div>

<section class="card card--flush">
    <form class="toolbar" method="get" action="<?= e(url('/admin/domaines')) ?>">
        <label class="search-field">
            <?= icon('search', 18) ?>
            <span class="sr-only">Rechercher un domaine</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Titre ou résumé">
        </label>
        <?php if ($q !== ''): ?><a class="btn btn--ghost" href="<?= e(url('/admin/domaines')) ?>">Effacer</a><?php endif; ?>
    </form>

    <?php if ($domaines === []): ?>
        <?= View::partial('partials/admin/empty', $q !== '' ? [
            'icone' => 'search', 'titre' => 'Aucun domaine ne correspond', 'texte' => 'Essayez un autre mot-clé.',
            'actionHref' => '/admin/domaines', 'actionLibelle' => 'Afficher tous les domaines',
        ] : [
            'icone' => 'target', 'titre' => "Aucun domaine d'action", 'texte' => 'Créez les axes d\'intervention de l\'organisation.',
            'actionHref' => '/admin/domaines/nouveau', 'actionLibelle' => 'Créer un domaine',
        ]) ?>
    <?php else: ?>
        <form id="bulk" method="post" action="<?= e(url('/admin/domaines/actions')) ?>" class="bulkbar" data-bulk>
            <?= csrf_field() ?>
            <span class="bulkbar__count" data-bulk-count>Sélection</span>
            <label class="sr-only" for="bulk-action">Action groupée</label>
            <select id="bulk-action" name="action" class="select select--dark" required>
                <option value="">Choisir une action</option>
                <option value="publier">Afficher sur le site</option>
                <option value="depublier">Masquer du site</option>
            </select>
            <button type="submit" class="btn btn--light">Appliquer</button>
            <button type="button" class="btn btn--ghost-dark" data-bulk-clear>Annuler la sélection</button>
        </form>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="table__check"><input type="checkbox" aria-label="Tout sélectionner" data-bulk-all></th>
                        <th scope="col">Ordre</th>
                        <th scope="col">Domaine</th>
                        <th scope="col">Projets</th>
                        <th scope="col">Visibilité</th>
                        <th scope="col" class="table__actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($domaines as $i => $d): ?>
                    <tr>
                        <td class="table__check"><input type="checkbox" name="ids[]" value="<?= (int) $d['id'] ?>" form="bulk" aria-label="Sélectionner « <?= e($d['titre']) ?> »" data-bulk-item></td>
                        <td data-label="Ordre">
                            <?php if ($q === ''): ?>
                                <div class="order-btns">
                                    <form method="post" action="<?= e(url('/admin/domaines/ordre')) ?>">
                                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $d['id'] ?>"><input type="hidden" name="direction" value="haut">
                                        <button type="submit" class="icon-btn icon-btn--outline" aria-label="Monter « <?= e($d['titre']) ?> »"<?= $i === 0 ? ' disabled' : '' ?>><svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 15 6-6 6 6"/></svg></button>
                                    </form>
                                    <form method="post" action="<?= e(url('/admin/domaines/ordre')) ?>">
                                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $d['id'] ?>"><input type="hidden" name="direction" value="bas">
                                        <button type="submit" class="icon-btn icon-btn--outline" aria-label="Descendre « <?= e($d['titre']) ?> »"<?= $i === $total - 1 ? ' disabled' : '' ?>><?= icon('down', 16) ?></button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="table__num"><?= (int) $d['ordre'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="cell-main">
                                <?php if ($d['image_fichier']): ?>
                                    <img class="thumb" src="<?= e(upload_url((string) $d['image_fichier'])) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <span class="thumb thumb--initials" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                <?php endif; ?>
                                <span class="cell-main__text">
                                    <a class="cell-main__title" href="<?= e(url('/admin/domaines/' . (int) $d['id'] . '/modifier')) ?>"><?= e($d['titre']) ?></a>
                                    <span class="cell-main__excerpt"><?= e($d['resume']) ?></span>
                                </span>
                                <?php if ((int) $d['mis_en_avant'] === 1): ?><span class="badge badge--navy">Mis en avant</span><?php endif; ?>
                            </div>
                        </td>
                        <td class="table__num" data-label="Projets"><?= (int) $d['nb_projets'] ?></td>
                        <td data-label="Visibilité"><?= (int) $d['publie'] === 1 ? '<span class="state state--on">Visible</span>' : '<span class="state">Masqué</span>' ?></td>
                        <td class="table__actions">
                            <div class="row-actions">
                                <?php if ((int) $d['publie'] === 1): ?>
                                    <a class="icon-btn icon-btn--outline" href="<?= e(url('/domaines-action/' . $d['slug'])) ?>" target="_blank" rel="noopener" aria-label="Voir « <?= e($d['titre']) ?> » sur le site" title="Voir sur le site"><?= icon('eye', 17) ?></a>
                                <?php endif; ?>
                                <a class="icon-btn icon-btn--outline icon-btn--blue" href="<?= e(url('/admin/domaines/' . (int) $d['id'] . '/modifier')) ?>" aria-label="Modifier « <?= e($d['titre']) ?> »" title="Modifier"><?= icon('edit', 17) ?></a>
                                <form method="post" action="<?= e(url('/admin/domaines/' . (int) $d['id'] . '/supprimer')) ?>" data-confirm="« <?= e($d['titre']) ?> » sera supprimé. Les <?= (int) $d['nb_projets'] ?> projet(s) et <?= (int) $d['nb_activites'] ?> activité(s) liés seront conservés, sans domaine." data-confirm-title="Supprimer ce domaine ?">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="icon-btn icon-btn--outline icon-btn--danger" aria-label="Supprimer « <?= e($d['titre']) ?> »" title="Supprimer"><?= icon('trash', 17) ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

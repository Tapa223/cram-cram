<?php

use App\Core\View;

$retour = $_GET !== [] ? '?' . http_build_query($_GET) : '';
$onglets = ['' => ['Tous', $total], 'non_lus' => ['Non lus', $nonLus], 'lus' => ['Lus', $total - $nonLus]];
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Messages <?php if ($nonLus > 0): ?><span class="count-pill"><?= $nonLus ?> non lu<?= $nonLus > 1 ? 's' : '' ?></span><?php endif; ?></h1>
        <p class="page-sub">Demandes reçues par le formulaire de contact du site.</p>
    </div>
</div>

<nav class="tabs" aria-label="Filtrer les messages">
    <?php foreach ($onglets as $cle => [$libelle, $n]): ?>
        <?php $query = array_filter(['filtre' => $cle, 'q' => $q], static fn ($v) => $v !== ''); ?>
        <a class="tab<?= $filtre === $cle ? ' is-active' : '' ?>" href="<?= e(url('/admin/messages') . ($query ? '?' . http_build_query($query) : '')) ?>"<?= $filtre === $cle ? ' aria-current="page"' : '' ?>><?= e($libelle) ?> <span class="tab__count"><?= $n ?></span></a>
    <?php endforeach; ?>
</nav>

<section class="card card--flush">
    <form class="toolbar" method="get" action="<?= e(url('/admin/messages')) ?>">
        <?php if ($filtre !== ''): ?><input type="hidden" name="filtre" value="<?= e($filtre) ?>"><?php endif; ?>
        <label class="search-field">
            <?= icon('search', 18) ?>
            <span class="sr-only">Rechercher un message</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Expéditeur, e-mail, objet ou contenu">
        </label>
        <?php if ($q !== ''): ?><a class="btn btn--ghost" href="<?= e(url('/admin/messages' . ($filtre !== '' ? '?filtre=' . $filtre : ''))) ?>">Effacer</a><?php endif; ?>
    </form>

    <?php if ($messages === []): ?>
        <?= View::partial('partials/admin/empty', [
            'icone' => 'inbox',
            'titre' => $q !== '' ? 'Aucun message ne correspond' : ($filtre === 'non_lus' ? 'Aucun message non lu' : 'Aucun message reçu'),
            'texte' => $q !== '' ? 'Essayez un autre mot-clé.' : 'Les demandes envoyées depuis la page Contact apparaîtront ici.',
        ]) ?>
    <?php else: ?>
        <form id="bulk" method="post" action="<?= e(url('/admin/messages/actions')) ?>" class="bulkbar" data-bulk>
            <?= csrf_field() ?>
            <input type="hidden" name="_retour" value="<?= e($retour) ?>">
            <span class="bulkbar__count" data-bulk-count>Sélection</span>
            <label class="sr-only" for="bulk-action">Action groupée</label>
            <select id="bulk-action" name="action" class="select select--dark" required>
                <option value="">Choisir une action</option>
                <option value="lu">Marquer comme lu</option>
                <option value="non_lu">Marquer comme non lu</option>
                <option value="supprimer" data-danger>Supprimer</option>
            </select>
            <button type="submit" class="btn btn--light" data-confirm-if-danger="Les messages sélectionnés seront supprimés définitivement.">Appliquer</button>
            <button type="button" class="btn btn--ghost-dark" data-bulk-clear>Annuler la sélection</button>
        </form>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="table__check"><input type="checkbox" aria-label="Tout sélectionner" data-bulk-all></th>
                        <th scope="col">Expéditeur</th>
                        <th scope="col">Objet</th>
                        <th scope="col">Reçu</th>
                        <th scope="col" class="table__actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $m): ?>
                    <tr class="<?= (int) $m['lu'] === 0 ? 'is-unread' : '' ?>">
                        <td class="table__check"><input type="checkbox" name="ids[]" value="<?= (int) $m['id'] ?>" form="bulk" aria-label="Sélectionner le message de <?= e($m['nom']) ?>" data-bulk-item></td>
                        <td>
                            <span class="cell-sender">
                                <strong><?php if ((int) $m['lu'] === 0): ?><span class="dot dot--navy dot--inline" aria-hidden="true"></span><span class="sr-only">Non lu : </span><?php endif; ?><?= e($m['nom']) ?></strong>
                                <span><?= e($m['email']) ?></span>
                            </span>
                        </td>
                        <td>
                            <span class="cell-main__text">
                                <a class="cell-main__title" href="<?= e(url('/admin/messages/' . (int) $m['id'])) ?>"><?= e($m['objet']) ?></a>
                                <span class="cell-main__excerpt"><?= e(excerpt((string) $m['message'], 110)) ?></span>
                            </span>
                        </td>
                        <td class="table__muted table__nowrap" data-label="Reçu"><?= e(depuis((string) $m['cree_le'])) ?></td>
                        <td class="table__actions">
                            <div class="row-actions">
                                <a class="icon-btn icon-btn--outline icon-btn--blue" href="<?= e(url('/admin/messages/' . (int) $m['id'])) ?>" aria-label="Lire le message de <?= e($m['nom']) ?>" title="Lire"><?= icon('eye', 17) ?></a>
                                <form method="post" action="<?= e(url('/admin/messages/' . (int) $m['id'] . '/supprimer')) ?>" data-confirm="Le message de <?= e($m['nom']) ?> sera supprimé définitivement." data-confirm-title="Supprimer ce message ?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_retour" value="<?= e($retour) ?>">
                                    <button type="submit" class="icon-btn icon-btn--outline icon-btn--danger" aria-label="Supprimer le message de <?= e($m['nom']) ?>" title="Supprimer"><?= icon('trash', 17) ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= View::partial('partials/admin/pagination', ['pager' => $pager, 'libelle' => 'messages']) ?>
    <?php endif; ?>
</section>

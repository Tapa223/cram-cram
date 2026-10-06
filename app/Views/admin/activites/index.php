<?php

use App\Core\View;

$retour = $_GET !== [] ? '?' . http_build_query($_GET) : '';
$onglets = ['' => ['Toutes', 'tous'], 'publie' => ['Publiées', 'publie'], 'brouillon' => ['Brouillons', 'brouillon']];
$filtresActifs = $filtres['q'] !== '' || $filtres['categorie'] !== '';
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Activités <span class="count-pill"><?= $compteurs['tous'] ?></span></h1>
        <p class="page-sub">Actualités, formations, rencontres et campagnes publiées dans la rubrique Actualités.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--primary" href="<?= e(url('/admin/activites/nouvelle')) ?>"><?= icon('plus', 18) ?> Nouvelle activité</a>
    </div>
</div>

<nav class="tabs" aria-label="Filtrer par statut">
    <?php foreach ($onglets as $valeur => [$libelle, $cle]): ?>
        <?php
        $query = array_filter(['statut' => $valeur, 'q' => $filtres['q'], 'categorie' => $filtres['categorie']], static fn ($v) => $v !== '');
        $actif = $filtres['statut'] === $valeur || ($valeur === '' && !isset($onglets[$filtres['statut']]));
        ?>
        <a class="tab<?= $actif ? ' is-active' : '' ?>" href="<?= e(url('/admin/activites') . ($query ? '?' . http_build_query($query) : '')) ?>"<?= $actif ? ' aria-current="page"' : '' ?>>
            <?= e($libelle) ?> <span class="tab__count"><?= $compteurs[$cle] ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<section class="card card--flush">
    <form class="toolbar" method="get" action="<?= e(url('/admin/activites')) ?>" data-autosubmit>
        <?php if ($filtres['statut'] !== ''): ?><input type="hidden" name="statut" value="<?= e($filtres['statut']) ?>"><?php endif; ?>
        <label class="search-field">
            <?= icon('search', 18) ?>
            <span class="sr-only">Rechercher une activité</span>
            <input type="search" name="q" value="<?= e($filtres['q']) ?>" placeholder="Titre, lieu ou catégorie">
        </label>
        <?php if ($categories !== []): ?>
            <label class="select-field">
                <span class="sr-only">Catégorie</span>
                <select name="categorie" class="select">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c) ?>"<?= $filtres['categorie'] === $c ? ' selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endif; ?>
        <button type="submit" class="btn btn--secondary js-hide">Filtrer</button>
        <?php if ($filtresActifs): ?>
            <a class="btn btn--ghost" href="<?= e(url('/admin/activites' . ($filtres['statut'] !== '' ? '?statut=' . urlencode($filtres['statut']) : ''))) ?>">Effacer les filtres</a>
        <?php endif; ?>
    </form>

    <?php if ($activites === []): ?>
        <?= View::partial('partials/admin/empty', $filtresActifs || $filtres['statut'] !== '' ? [
            'icone' => 'search', 'titre' => 'Aucune activité ne correspond à ces critères', 'texte' => 'Modifiez la recherche ou affichez toutes les activités.',
            'actionHref' => '/admin/activites', 'actionLibelle' => 'Afficher toutes les activités',
        ] : [
            'icone' => 'calendar', 'titre' => 'Aucune activité pour le moment', 'texte' => 'Publiez une première actualité : atelier, formation, rencontre ou campagne.',
            'actionHref' => '/admin/activites/nouvelle', 'actionLibelle' => 'Créer une activité',
        ]) ?>
    <?php else: ?>
        <form id="bulk" method="post" action="<?= e(url('/admin/activites/actions')) ?>" class="bulkbar" data-bulk>
            <?= csrf_field() ?>
            <input type="hidden" name="_retour" value="<?= e($retour) ?>">
            <span class="bulkbar__count" data-bulk-count>Sélection</span>
            <label class="sr-only" for="bulk-action">Action groupée</label>
            <select id="bulk-action" name="action" class="select select--dark" required>
                <option value="">Choisir une action</option>
                <option value="publier">Publier</option>
                <option value="depublier">Repasser en brouillon</option>
                <option value="supprimer" data-danger>Supprimer</option>
            </select>
            <button type="submit" class="btn btn--light" data-confirm-if-danger="Les activités sélectionnées seront supprimées définitivement.">Appliquer</button>
            <button type="button" class="btn btn--ghost-dark" data-bulk-clear>Annuler la sélection</button>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="table__check"><input type="checkbox" aria-label="Tout sélectionner" data-bulk-all></th>
                        <th scope="col">Activité</th>
                        <th scope="col">Catégorie</th>
                        <th scope="col">Date</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="table__actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($activites as $a): ?>
                    <tr>
                        <td class="table__check"><input type="checkbox" name="ids[]" value="<?= (int) $a['id'] ?>" form="bulk" aria-label="Sélectionner « <?= e($a['titre']) ?> »" data-bulk-item></td>
                        <td>
                            <div class="cell-main">
                                <?php if ($a['image_fichier']): ?>
                                    <img class="thumb" src="<?= e(upload_url((string) $a['image_fichier'])) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <span class="thumb thumb--empty" aria-hidden="true"><?= icon('image', 18) ?></span>
                                <?php endif; ?>
                                <span class="cell-main__text">
                                    <a class="cell-main__title" href="<?= e(url('/admin/activites/' . (int) $a['id'] . '/modifier')) ?>"><?= e($a['titre']) ?></a>
                                    <span class="cell-main__meta"><?= e($a['lieu'] ?: ($a['domaine_titre'] ?? 'Lieu non précisé')) ?></span>
                                </span>
                            </div>
                        </td>
                        <td data-label="Catégorie"><span class="badge badge--grey"><?= e($a['categorie']) ?></span></td>
                        <td class="table__muted table__nowrap" data-label="Date"><?= e(date_court_fr((string) $a['date_activite'])) ?></td>
                        <td data-label="Statut"><?= $a['statut'] === 'publie' ? '<span class="state state--on">Publiée</span>' : '<span class="badge badge--navy">Brouillon</span>' ?></td>
                        <td class="table__actions">
                            <div class="row-actions">
                                <?php if ($a['statut'] === 'publie'): ?>
                                    <a class="icon-btn icon-btn--outline" href="<?= e(url('/actualites/' . $a['slug'])) ?>" target="_blank" rel="noopener" aria-label="Voir « <?= e($a['titre']) ?> » sur le site" title="Voir sur le site"><?= icon('eye', 17) ?></a>
                                <?php endif; ?>
                                <a class="icon-btn icon-btn--outline icon-btn--blue" href="<?= e(url('/admin/activites/' . (int) $a['id'] . '/modifier')) ?>" aria-label="Modifier « <?= e($a['titre']) ?> »" title="Modifier"><?= icon('edit', 17) ?></a>
                                <form method="post" action="<?= e(url('/admin/activites/' . (int) $a['id'] . '/supprimer')) ?>" data-confirm="« <?= e($a['titre']) ?> » sera supprimée du site et de l'administration. Cette action est définitive." data-confirm-title="Supprimer cette activité ?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_retour" value="<?= e($retour) ?>">
                                    <button type="submit" class="icon-btn icon-btn--outline icon-btn--danger" aria-label="Supprimer « <?= e($a['titre']) ?> »" title="Supprimer"><?= icon('trash', 17) ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= View::partial('partials/admin/pagination', ['pager' => $pager, 'libelle' => 'activités']) ?>
    <?php endif; ?>
</section>

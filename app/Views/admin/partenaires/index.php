<?php

use App\Core\View;
use App\Models\Partenaire;

$retour = $_GET !== [] ? '?' . http_build_query($_GET) : '';
$filtresActifs = $q !== '' || $publication !== '';
$badgeCategorie = ['financier' => 'blue', 'technique' => 'green', 'national' => 'navy', 'international' => 'grey'];
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Partenaires <span class="count-pill"><?= $compteurs['tous'] ?></span></h1>
        <p class="page-sub">Bailleurs, partenaires techniques, de recherche et de mise en œuvre.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--primary" href="<?= e(url('/admin/partenaires/nouveau')) ?>"><?= icon('plus', 18) ?> Nouveau partenaire</a>
    </div>
</div>

<nav class="tabs" aria-label="Filtrer par catégorie">
    <?php foreach (['' => 'Tous'] + Partenaire::CATEGORIES_COURTES as $cle => $libelle): ?>
        <?php
        $query = array_filter(['categorie' => $cle, 'q' => $q, 'publication' => $publication], static fn ($v) => $v !== '');
        $actif = $categorie === $cle;
        ?>
        <a class="tab<?= $actif ? ' is-active' : '' ?>" href="<?= e(url('/admin/partenaires') . ($query ? '?' . http_build_query($query) : '')) ?>"<?= $actif ? ' aria-current="page"' : '' ?>>
            <?= e($libelle) ?> <span class="tab__count"><?= $compteurs[$cle === '' ? 'tous' : $cle] ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<section class="card card--flush">
    <form class="toolbar" method="get" action="<?= e(url('/admin/partenaires')) ?>" data-autosubmit>
        <?php if ($categorie !== ''): ?><input type="hidden" name="categorie" value="<?= e($categorie) ?>"><?php endif; ?>
        <label class="search-field">
            <?= icon('search', 18) ?>
            <span class="sr-only">Rechercher un partenaire</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, sigle ou pays">
        </label>
        <label class="select-field">
            <span class="sr-only">Visibilité</span>
            <select name="publication" class="select">
                <option value="">Visibles et masqués</option>
                <option value="publie"<?= $publication === 'publie' ? ' selected' : '' ?>>Visibles</option>
                <option value="masque"<?= $publication === 'masque' ? ' selected' : '' ?>>Masqués</option>
            </select>
        </label>
        <button type="submit" class="btn btn--secondary js-hide">Filtrer</button>
        <?php if ($filtresActifs): ?>
            <a class="btn btn--ghost" href="<?= e(url('/admin/partenaires' . ($categorie !== '' ? '?categorie=' . urlencode($categorie) : ''))) ?>">Effacer les filtres</a>
        <?php endif; ?>
    </form>

    <?php if ($partenaires === []): ?>
        <?= View::partial('partials/admin/empty', $filtresActifs || $categorie !== '' ? [
            'icone' => 'search', 'titre' => 'Aucun partenaire ne correspond', 'texte' => 'Modifiez la recherche ou changez de catégorie.',
            'actionHref' => '/admin/partenaires', 'actionLibelle' => 'Afficher tous les partenaires',
        ] : [
            'icone' => 'users', 'titre' => 'Aucun partenaire enregistré', 'texte' => 'Ajoutez les bailleurs et partenaires de l\'organisation.',
            'actionHref' => '/admin/partenaires/nouveau', 'actionLibelle' => 'Ajouter un partenaire',
        ]) ?>
    <?php else: ?>
        <form id="bulk" method="post" action="<?= e(url('/admin/partenaires/actions')) ?>" class="bulkbar" data-bulk>
            <?= csrf_field() ?>
            <input type="hidden" name="_retour" value="<?= e($retour) ?>">
            <span class="bulkbar__count" data-bulk-count>Sélection</span>
            <label class="sr-only" for="bulk-action">Action groupée</label>
            <select id="bulk-action" name="action" class="select select--dark" required>
                <option value="">Choisir une action</option>
                <option value="publier">Afficher sur le site</option>
                <option value="depublier">Masquer du site</option>
                <option value="supprimer" data-danger>Supprimer</option>
            </select>
            <button type="submit" class="btn btn--light" data-confirm-if-danger="Les partenaires sélectionnés seront supprimés et retirés des projets auxquels ils sont liés.">Appliquer</button>
            <button type="button" class="btn btn--ghost-dark" data-bulk-clear>Annuler la sélection</button>
        </form>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="table__check"><input type="checkbox" aria-label="Tout sélectionner" data-bulk-all></th>
                        <th scope="col">Partenaire</th>
                        <th scope="col">Catégorie</th>
                        <th scope="col">Projets liés</th>
                        <th scope="col">Visibilité</th>
                        <th scope="col" class="table__actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($partenaires as $pa): ?>
                    <tr>
                        <td class="table__check"><input type="checkbox" name="ids[]" value="<?= (int) $pa['id'] ?>" form="bulk" aria-label="Sélectionner « <?= e($pa['nom']) ?> »" data-bulk-item></td>
                        <td>
                            <div class="cell-main">
                                <?php if ($pa['logo_fichier']): ?>
                                    <img class="thumb thumb--logo" src="<?= e(upload_url((string) $pa['logo_fichier'])) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <span class="thumb thumb--initials" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) ($pa['sigle'] ?: $pa['nom']), 0, 3))) ?></span>
                                <?php endif; ?>
                                <span class="cell-main__text">
                                    <a class="cell-main__title" href="<?= e(url('/admin/partenaires/' . (int) $pa['id'] . '/modifier')) ?>"><?= e($pa['nom']) ?></a>
                                    <span class="cell-main__meta"><?= e(implode(' · ', array_filter([$pa['sigle'], $pa['pays']]))) ?: 'Sigle et pays non renseignés' ?></span>
                                </span>
                            </div>
                        </td>
                        <td data-label="Catégorie"><span class="badge badge--<?= $badgeCategorie[$pa['categorie']] ?? 'grey' ?>"><?= e(Partenaire::CATEGORIES_COURTES[$pa['categorie']] ?? $pa['categorie']) ?></span></td>
                        <td class="table__num" data-label="Projets liés"><?= (int) $pa['nb_projets'] ?></td>
                        <td data-label="Visibilité"><?= (int) $pa['publie'] === 1 ? '<span class="state state--on">Visible</span>' : '<span class="state">Masqué</span>' ?></td>
                        <td class="table__actions">
                            <div class="row-actions">
                                <?php if ($pa['site_web']): ?>
                                    <a class="icon-btn icon-btn--outline" href="<?= e($pa['site_web']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Site web de « <?= e($pa['nom']) ?> »" title="Site web"><?= icon('globe', 17) ?></a>
                                <?php endif; ?>
                                <a class="icon-btn icon-btn--outline icon-btn--blue" href="<?= e(url('/admin/partenaires/' . (int) $pa['id'] . '/modifier')) ?>" aria-label="Modifier « <?= e($pa['nom']) ?> »" title="Modifier"><?= icon('edit', 17) ?></a>
                                <form method="post" action="<?= e(url('/admin/partenaires/' . (int) $pa['id'] . '/supprimer')) ?>" data-confirm="« <?= e($pa['nom']) ?> » sera supprimé<?= (int) $pa['nb_projets'] > 0 ? ' et retiré des ' . (int) $pa['nb_projets'] . ' projet(s) auxquels il est lié' : '' ?>." data-confirm-title="Supprimer ce partenaire ?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_retour" value="<?= e($retour) ?>">
                                    <button type="submit" class="icon-btn icon-btn--outline icon-btn--danger" aria-label="Supprimer « <?= e($pa['nom']) ?> »" title="Supprimer"><?= icon('trash', 17) ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-foot"><span class="table-foot__info"><?= pluriel(count($partenaires), 'partenaire') ?></span></div>
    <?php endif; ?>
</section>

<?php

use App\Core\View;
use App\Models\Projet;

$retour = $_GET !== [] ? '?' . http_build_query($_GET) : '';
$onglets = ['' => 'Tous', 'en_cours' => 'En cours', 'planifie' => 'Planifiés', 'termine' => 'Terminés', 'non_precise' => 'Non précisé'];
$cles = ['' => 'tous', 'en_cours' => 'en_cours', 'planifie' => 'planifie', 'termine' => 'termine', 'non_precise' => 'non_precise'];
$filtresActifs = $filtres['q'] !== '' || $filtres['domaine'] || $filtres['partenaire'] || $filtres['publication'] !== '';
$badgeStatut = ['en_cours' => 'blue', 'planifie' => 'navy', 'termine' => 'green'];
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Projets <span class="count-pill"><?= $compteurs['tous'] ?></span></h1>
        <p class="page-sub">Gérez les projets présentés sur le site public et leurs bailleurs.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--primary" href="<?= e(url('/admin/projets/nouveau')) ?>"><?= icon('plus', 18) ?> Nouveau projet</a>
    </div>
</div>

<nav class="tabs" aria-label="Filtrer par statut">
    <?php foreach ($onglets as $valeur => $libelle): ?>
        <?php
        $query = array_filter(['statut' => $valeur, 'q' => $filtres['q'], 'domaine' => $filtres['domaine'], 'partenaire' => $filtres['partenaire'], 'publication' => $filtres['publication']], static fn ($v) => $v !== '' && $v !== null);
        $actif = $filtres['statut'] === $valeur || ($valeur === '' && !isset($onglets[$filtres['statut']]));
        ?>
        <?php if ($valeur === 'non_precise' && $compteurs['non_precise'] === 0 && !$actif) { continue; } ?>
        <a class="tab<?= $actif ? ' is-active' : '' ?>" href="<?= e(url('/admin/projets') . ($query ? '?' . http_build_query($query) : '')) ?>"<?= $actif ? ' aria-current="page"' : '' ?>>
            <?= e($libelle) ?> <span class="tab__count"><?= $compteurs[$cles[$valeur]] ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<section class="card card--flush">
    <form class="toolbar" method="get" action="<?= e(url('/admin/projets')) ?>" data-autosubmit>
        <?php if ($filtres['statut'] !== ''): ?><input type="hidden" name="statut" value="<?= e($filtres['statut']) ?>"><?php endif; ?>
        <label class="search-field">
            <?= icon('search', 18) ?>
            <span class="sr-only">Rechercher un projet</span>
            <input type="search" name="q" value="<?= e($filtres['q']) ?>" placeholder="Titre, résumé ou bailleur">
        </label>
        <label class="select-field">
            <span class="sr-only">Domaine d'action</span>
            <select name="domaine" class="select">
                <option value="">Tous les domaines</option>
                <?php foreach ($domaines as $d): ?>
                    <option value="<?= (int) $d['id'] ?>"<?= $filtres['domaine'] === (int) $d['id'] ? ' selected' : '' ?>><?= e($d['titre']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="select-field">
            <span class="sr-only">Bailleur ou partenaire</span>
            <select name="partenaire" class="select">
                <option value="">Tous les partenaires</option>
                <?php foreach ($partenaires as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"<?= $filtres['partenaire'] === (int) $p['id'] ? ' selected' : '' ?>><?= e($p['sigle'] ?: $p['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="select-field">
            <span class="sr-only">Publication</span>
            <select name="publication" class="select">
                <option value="">Publiés et non publiés</option>
                <option value="publie"<?= $filtres['publication'] === 'publie' ? ' selected' : '' ?>>Publiés</option>
                <option value="brouillon"<?= $filtres['publication'] === 'brouillon' ? ' selected' : '' ?>>Non publiés</option>
            </select>
        </label>
        <button type="submit" class="btn btn--secondary js-hide">Filtrer</button>
        <?php if ($filtresActifs): ?>
            <a class="btn btn--ghost" href="<?= e(url('/admin/projets' . ($filtres['statut'] !== '' ? '?statut=' . urlencode($filtres['statut']) : ''))) ?>">Effacer les filtres</a>
        <?php endif; ?>
    </form>

    <?php if ($projets === []): ?>
        <?= View::partial('partials/admin/empty', $filtresActifs || $filtres['statut'] !== '' ? [
            'icone' => 'search', 'titre' => 'Aucun projet ne correspond à ces critères', 'texte' => 'Modifiez la recherche ou affichez tous les projets.',
            'actionHref' => '/admin/projets', 'actionLibelle' => 'Afficher tous les projets',
        ] : [
            'icone' => 'folder', 'titre' => 'Aucun projet pour le moment', 'texte' => 'Créez votre premier projet pour l\'afficher sur le site.',
            'actionHref' => '/admin/projets/nouveau', 'actionLibelle' => 'Créer un projet',
        ]) ?>
    <?php else: ?>
        <form id="bulk" method="post" action="<?= e(url('/admin/projets/actions')) ?>" class="bulkbar" data-bulk>
            <?= csrf_field() ?>
            <input type="hidden" name="_retour" value="<?= e($retour) ?>">
            <span class="bulkbar__count" data-bulk-count>Sélection</span>
            <label class="sr-only" for="bulk-action">Action groupée</label>
            <select id="bulk-action" name="action" class="select select--dark" required>
                <option value="">Choisir une action</option>
                <option value="publier">Publier</option>
                <option value="depublier">Retirer du site</option>
                <option value="supprimer" data-danger>Supprimer</option>
            </select>
            <button type="submit" class="btn btn--light" data-confirm-if-danger="Les projets sélectionnés seront supprimés définitivement, avec leurs liens vers les bailleurs.">Appliquer</button>
            <button type="button" class="btn btn--ghost-dark" data-bulk-clear>Annuler la sélection</button>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="table__check"><input type="checkbox" aria-label="Tout sélectionner" data-bulk-all></th>
                        <th scope="col">Projet</th>
                        <th scope="col">Bailleur(s)</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Publication</th>
                        <th scope="col">Mis à jour</th>
                        <th scope="col" class="table__actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($projets as $p): ?>
                    <tr>
                        <td class="table__check"><input type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>" form="bulk" aria-label="Sélectionner « <?= e($p['titre']) ?> »" data-bulk-item></td>
                        <td>
                            <div class="cell-main">
                                <?php if ($p['image_fichier']): ?>
                                    <img class="thumb" src="<?= e(upload_url((string) $p['image_fichier'])) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <span class="thumb thumb--empty" aria-hidden="true"><?= icon('image', 18) ?></span>
                                <?php endif; ?>
                                <span class="cell-main__text">
                                    <a class="cell-main__title" href="<?= e(url('/admin/projets/' . (int) $p['id'] . '/modifier')) ?>"><?= e($p['titre']) ?></a>
                                    <span class="cell-main__meta"><?= e($p['domaine_titre'] ?? 'Sans domaine') ?></span>
                                </span>
                            </div>
                        </td>
                        <td class="table__muted" data-label="Bailleur(s)"><?= e($p['bailleurs'] ?: '—') ?></td>
                        <td data-label="Statut">
                            <?php if ($p['statut']): ?>
                                <span class="badge badge--<?= $badgeStatut[$p['statut']] ?>"><?= e(Projet::STATUTS[$p['statut']]) ?></span>
                            <?php else: ?>
                                <span class="badge badge--outline">Non précisé</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Publication"><?= (int) $p['publie'] === 1 ? '<span class="state state--on">Publié</span>' : '<span class="state">Non publié</span>' ?></td>
                        <td class="table__muted table__nowrap" data-label="Mis à jour"><?= e(date_court_fr((string) $p['modifie_le'])) ?></td>
                        <td class="table__actions">
                            <div class="row-actions">
                                <?php if ((int) $p['publie'] === 1): ?>
                                    <a class="icon-btn icon-btn--outline" href="<?= e(url('/projets/' . $p['slug'])) ?>" target="_blank" rel="noopener" aria-label="Voir « <?= e($p['titre']) ?> » sur le site" title="Voir sur le site"><?= icon('eye', 17) ?></a>
                                <?php endif; ?>
                                <a class="icon-btn icon-btn--outline icon-btn--blue" href="<?= e(url('/admin/projets/' . (int) $p['id'] . '/modifier')) ?>" aria-label="Modifier « <?= e($p['titre']) ?> »" title="Modifier"><?= icon('edit', 17) ?></a>
                                <form method="post" action="<?= e(url('/admin/projets/' . (int) $p['id'] . '/supprimer')) ?>" data-confirm="« <?= e($p['titre']) ?> » sera retiré du site public et de l'administration. Cette action est définitive." data-confirm-title="Supprimer ce projet ?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_retour" value="<?= e($retour) ?>">
                                    <button type="submit" class="icon-btn icon-btn--outline icon-btn--danger" aria-label="Supprimer « <?= e($p['titre']) ?> »" title="Supprimer"><?= icon('trash', 17) ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= View::partial('partials/admin/pagination', ['pager' => $pager, 'libelle' => 'projets']) ?>
    <?php endif; ?>
</section>

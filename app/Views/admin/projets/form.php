<?php

use App\Core\View;
use App\Models\Partenaire;
use App\Models\Projet;

$p = $projet ?? [];
$isEdit = $projet !== null;
$action = $isEdit ? '/admin/projets/' . (int) $p['id'] : '/admin/projets';
$selection = old_ids('partenaires', $partenairesSel);
$statut = (string) old('statut', $p['statut'] ?? '');
$publie = old_bool('publie', $isEdit ? (int) $p['publie'] === 1 : false);
?>
<form method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" class="form-page" data-submit-once data-dirty-watch novalidate>
    <?= csrf_field() ?>

    <div class="page-head">
        <div>
            <a class="back-link" href="<?= e(url('/admin/projets')) ?>"><?= icon('back', 16) ?> Retour aux projets</a>
            <h1 class="page-title"><?= $isEdit ? e($p['titre']) : 'Nouveau projet' ?></h1>
            <?php if ($isEdit): ?>
                <p class="page-meta">
                    <?= (int) $p['publie'] === 1 ? '<span class="state state--on">Publié</span>' : '<span class="state">Non publié</span>' ?>
                    <span>Modifié le <?= e(date_fr((string) $p['modifie_le'], true)) ?></span>
                </p>
            <?php endif; ?>
        </div>
        <?php if ($isEdit && (int) $p['publie'] === 1): ?>
            <div class="page-head__actions">
                <a class="btn btn--secondary" href="<?= e(url('/projets/' . $p['slug'])) ?>" target="_blank" rel="noopener"><?= icon('external', 17) ?> Voir sur le site</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-layout">
        <div class="form-layout__main">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Informations générales</h2></div>
                <div class="card__body stack">
                    <div class="field<?= field_error('titre') ? ' has-error' : '' ?>">
                        <label class="field__label" for="titre">Titre du projet <span class="req" aria-hidden="true">*</span></label>
                        <input class="input" id="titre" name="titre" type="text" maxlength="220" required value="<?= e(old('titre', $p['titre'] ?? '')) ?>"<?= field_error('titre') ? ' aria-invalid="true" aria-describedby="titre-err"' : '' ?>>
                        <?php if (field_error('titre')): ?><p class="field__error" id="titre-err"><?= icon('alert', 16) ?> <?= e(field_error('titre')) ?></p><?php endif; ?>
                        <?php if ($isEdit): ?>
                            <p class="field__hint">Adresse publique : <?= e(url('/projets/')) ?><strong><?= e($p['slug']) ?></strong></p>
                        <?php endif; ?>
                    </div>
                    <div class="field<?= field_error('resume') ? ' has-error' : '' ?>">
                        <div class="field__row">
                            <label class="field__label" for="resume">Résumé <span class="req" aria-hidden="true">*</span></label>
                            <span class="field__counter" data-counter-for="resume" aria-live="polite"></span>
                        </div>
                        <textarea class="input" id="resume" name="resume" rows="3" maxlength="400" required data-maxlength="400"<?= field_error('resume') ? ' aria-invalid="true" aria-describedby="resume-err"' : '' ?>><?= e(old('resume', $p['resume'] ?? '')) ?></textarea>
                        <?php if (field_error('resume')): ?><p class="field__error" id="resume-err"><?= icon('alert', 16) ?> <?= e(field_error('resume')) ?></p><?php endif; ?>
                        <p class="field__hint">Affiché sur les cartes de projet. Une à deux phrases.</p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="description">Description détaillée</label>
                        <textarea class="input editor-source" id="description" name="description" rows="10" data-editor><?= e(old('description', $p['description'] ?? '')) ?></textarea>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__head"><h2 class="card__title">Détails du projet</h2></div>
                <div class="card__body stack">
                    <div class="grid-fields">
                        <div class="field">
                            <label class="field__label" for="domaine_id">Domaine d'action</label>
                            <select class="select" id="domaine_id" name="domaine_id">
                                <option value="">Aucun domaine</option>
                                <?php foreach ($domaines as $d): ?>
                                    <option value="<?= (int) $d['id'] ?>"<?= (string) old('domaine_id', (string) ($p['domaine_id'] ?? '')) === (string) $d['id'] ? ' selected' : '' ?>><?= e($d['titre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (field_error('domaine_id')): ?><p class="field__error"><?= icon('alert', 16) ?> <?= e(field_error('domaine_id')) ?></p><?php endif; ?>
                        </div>
                        <fieldset class="field">
                            <legend class="field__label">Statut</legend>
                            <div class="segmented segmented--form">
                                <label class="segmented__item"><input type="radio" name="statut" value=""<?= $statut === '' ? ' checked' : '' ?>><span>Non précisé</span></label>
                                <?php foreach (Projet::STATUTS as $cle => $libelle): ?>
                                    <label class="segmented__item"><input type="radio" name="statut" value="<?= e($cle) ?>"<?= $statut === $cle ? ' checked' : '' ?>><span><?= e($libelle) ?></span></label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    </div>

                    <fieldset class="field">
                        <legend class="field__label">Bailleur(s) et partenaires du projet</legend>
                        <div class="multi" data-multi>
                            <label class="search-field search-field--sm">
                                <?= icon('search', 16) ?>
                                <span class="sr-only">Filtrer les partenaires</span>
                                <input type="search" placeholder="Filtrer la liste…" data-multi-filter>
                            </label>
                            <div class="multi__list">
                                <?php $categorieCourante = null; ?>
                                <?php foreach ($partenaires as $pa): ?>
                                    <?php if ($pa['categorie'] !== $categorieCourante): $categorieCourante = $pa['categorie']; ?>
                                        <p class="multi__group"><?= e(Partenaire::CATEGORIES_COURTES[$categorieCourante] ?? '') ?></p>
                                    <?php endif; ?>
                                    <label class="check multi__item" data-multi-item>
                                        <input type="checkbox" name="partenaires[]" value="<?= (int) $pa['id'] ?>"<?= in_array((int) $pa['id'], $selection, true) ? ' checked' : '' ?>>
                                        <span><?= e($pa['nom']) ?><?= $pa['sigle'] ? ' <em class="multi__sigle">' . e($pa['sigle']) . '</em>' : '' ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="field__hint">La liste provient du module Partenaires. <a href="<?= e(url('/admin/partenaires/nouveau')) ?>">Ajouter un partenaire</a></p>
                    </fieldset>

                    <div class="grid-fields grid-fields--3">
                        <div class="field<?= field_error('date_debut') ? ' has-error' : '' ?>">
                            <label class="field__label" for="date_debut">Date de début</label>
                            <input class="input" id="date_debut" name="date_debut" type="date" value="<?= e(old('date_debut', $p['date_debut'] ?? '')) ?>">
                            <?php if (field_error('date_debut')): ?><p class="field__error"><?= icon('alert', 16) ?> <?= e(field_error('date_debut')) ?></p><?php endif; ?>
                        </div>
                        <div class="field<?= field_error('date_fin') ? ' has-error' : '' ?>">
                            <label class="field__label" for="date_fin">Date de fin</label>
                            <input class="input" id="date_fin" name="date_fin" type="date" value="<?= e(old('date_fin', $p['date_fin'] ?? '')) ?>"<?= field_error('date_fin') ? ' aria-invalid="true" aria-describedby="date-fin-err"' : '' ?>>
                            <?php if (field_error('date_fin')): ?><p class="field__error" id="date-fin-err"><?= icon('alert', 16) ?> <?= e(field_error('date_fin')) ?></p><?php endif; ?>
                        </div>
                        <div class="field<?= field_error('zone') ? ' has-error' : '' ?>">
                            <label class="field__label" for="zone">Zone géographique</label>
                            <input class="input" id="zone" name="zone" type="text" maxlength="200" placeholder="Ex. : région de Tombouctou" value="<?= e(old('zone', $p['zone'] ?? '')) ?>">
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="field<?= field_error('slug') ? ' has-error' : '' ?>">
                            <label class="field__label" for="slug">Adresse de la page (avancé)</label>
                            <input class="input" id="slug" name="slug" type="text" maxlength="180" value="<?= e(old('slug', $p['slug'])) ?>" pattern="[a-z0-9]+(-[a-z0-9]+)*">
                            <?php if (field_error('slug')): ?><p class="field__error"><?= icon('alert', 16) ?> <?= e(field_error('slug')) ?></p><?php endif; ?>
                            <p class="field__hint">Modifier l'adresse casse les liens déjà partagés vers ce projet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="form-layout__side">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Publication</h2></div>
                <div class="card__body stack">
                    <label class="switch">
                        <span class="switch__text"><strong>Visible sur le site public</strong><span>Décochez pour garder le projet en préparation.</span></span>
                        <input type="checkbox" name="publie" value="1" role="switch"<?= $publie ? ' checked' : '' ?>>
                        <span class="switch__track" aria-hidden="true"></span>
                    </label>
                    <?php if ($isEdit): ?>
                        <dl class="kv">
                            <div><dt>Créé le</dt><dd><?= e(date_fr((string) $p['cree_le'])) ?></dd></div>
                            <div><dt>Auteur</dt><dd><?= e($p['auteur_nom'] ?? '—') ?></dd></div>
                        </dl>
                    <?php endif; ?>
                </div>
            </section>

            <?= View::partial('partials/admin/image-field', [
                'titre'        => 'Image à la une',
                'imageId'      => old('image_id', $p['image_id'] ?? null),
                'imageFichier' => $p['image_fichier'] ?? null,
                'images'       => $images,
                'aide'         => 'JPEG, PNG ou WEBP · 5 Mo maximum',
            ]) ?>

            <?= View::partial('partials/admin/gallery-field', ['galerie' => $galerie, 'images' => $images]) ?>

            <?php if ($isEdit): ?>
                <section class="card card--danger">
                    <div class="card__body stack">
                        <h2 class="card__title card__title--danger">Supprimer le projet</h2>
                        <p class="card__text">Le projet sera retiré du site et de l'administration. Une confirmation vous sera demandée.</p>
                        <button type="submit" form="delete-form" class="btn btn--danger">Supprimer ce projet</button>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>

    <div class="savebar">
        <span class="savebar__status" data-dirty-status>Les champs marqués <span class="req">*</span> sont obligatoires.</span>
        <div class="savebar__actions">
            <a class="btn btn--secondary" href="<?= e(url('/admin/projets')) ?>">Annuler</a>
            <button type="submit" class="btn btn--primary"><?= icon('check', 17) ?> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le projet' ?></button>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <form id="delete-form" method="post" action="<?= e(url('/admin/projets/' . (int) $p['id'] . '/supprimer')) ?>" data-confirm="« <?= e($p['titre']) ?> » sera retiré du site public et de l'administration. Cette action est définitive." data-confirm-title="Supprimer ce projet ?">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

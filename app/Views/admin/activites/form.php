<?php

use App\Core\View;

$a = $activite ?? [];
$isEdit = $activite !== null;
$action = $isEdit ? '/admin/activites/' . (int) $a['id'] : '/admin/activites';
$statut = (string) old('statut', $a['statut'] ?? 'brouillon');
$erreur = static fn (string $champ): string => field_error($champ) ? '<p class="field__error" id="' . $champ . '-err">' . icon('alert', 16) . ' ' . e(field_error($champ)) . '</p>' : '';
$invalide = static fn (string $champ): string => field_error($champ) ? ' aria-invalid="true" aria-describedby="' . $champ . '-err"' : '';
?>
<form method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" class="form-page" data-submit-once data-dirty-watch novalidate>
    <?= csrf_field() ?>

    <div class="page-head">
        <div>
            <a class="back-link" href="<?= e(url('/admin/activites')) ?>"><?= icon('back', 16) ?> Retour aux activités</a>
            <h1 class="page-title"><?= $isEdit ? e($a['titre']) : 'Nouvelle activité' ?></h1>
            <?php if ($isEdit): ?>
                <p class="page-meta">
                    <?= $a['statut'] === 'publie' ? '<span class="state state--on">Publiée</span>' : '<span class="badge badge--navy">Brouillon</span>' ?>
                    <span>Modifiée le <?= e(date_fr((string) $a['modifie_le'], true)) ?></span>
                </p>
            <?php endif; ?>
        </div>
        <?php if ($isEdit && $a['statut'] === 'publie'): ?>
            <div class="page-head__actions">
                <a class="btn btn--secondary" href="<?= e(url('/actualites/' . $a['slug'])) ?>" target="_blank" rel="noopener"><?= icon('external', 17) ?> Voir sur le site</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-layout">
        <div class="form-layout__main">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Contenu</h2></div>
                <div class="card__body stack">
                    <div class="field<?= field_error('titre') ? ' has-error' : '' ?>">
                        <label class="field__label" for="titre">Titre <span class="req" aria-hidden="true">*</span></label>
                        <input class="input" id="titre" name="titre" type="text" maxlength="220" required value="<?= e(old('titre', $a['titre'] ?? '')) ?>"<?= $invalide('titre') ?>>
                        <?= $erreur('titre') ?>
                    </div>
                    <div class="field<?= field_error('resume') ? ' has-error' : '' ?>">
                        <div class="field__row">
                            <label class="field__label" for="resume">Chapeau</label>
                            <span class="field__counter" data-counter-for="resume" aria-live="polite"></span>
                        </div>
                        <textarea class="input" id="resume" name="resume" rows="2" maxlength="400" data-maxlength="400"<?= $invalide('resume') ?>><?= e(old('resume', $a['resume'] ?? '')) ?></textarea>
                        <?= $erreur('resume') ?>
                        <p class="field__hint">Une ou deux phrases affichées sur les cartes d'actualité.</p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="contenu">Texte de l'activité</label>
                        <textarea class="input editor-source" id="contenu" name="contenu" rows="12" data-editor><?= e(old('contenu', $a['contenu'] ?? '')) ?></textarea>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__head"><h2 class="card__title">Informations pratiques</h2></div>
                <div class="card__body stack">
                    <div class="grid-fields">
                        <div class="field<?= field_error('categorie') ? ' has-error' : '' ?>">
                            <label class="field__label" for="categorie">Catégorie <span class="req" aria-hidden="true">*</span></label>
                            <input class="input" id="categorie" name="categorie" type="text" maxlength="60" list="categories" required value="<?= e(old('categorie', $a['categorie'] ?? '')) ?>" placeholder="Choisir ou saisir"<?= $invalide('categorie') ?>>
                            <datalist id="categories">
                                <?php foreach ($categories as $c): ?><option value="<?= e($c) ?>"></option><?php endforeach; ?>
                            </datalist>
                            <?= $erreur('categorie') ?>
                        </div>
                        <div class="field<?= field_error('date_activite') ? ' has-error' : '' ?>">
                            <label class="field__label" for="date_activite">Date <span class="req" aria-hidden="true">*</span></label>
                            <input class="input" id="date_activite" name="date_activite" type="date" required value="<?= e(old('date_activite', $a['date_activite'] ?? date('Y-m-d'))) ?>"<?= $invalide('date_activite') ?>>
                            <?= $erreur('date_activite') ?>
                        </div>
                        <div class="field<?= field_error('lieu') ? ' has-error' : '' ?>">
                            <label class="field__label" for="lieu">Lieu</label>
                            <input class="input" id="lieu" name="lieu" type="text" maxlength="200" value="<?= e(old('lieu', $a['lieu'] ?? '')) ?>" placeholder="Ex. : Tombouctou">
                            <?= $erreur('lieu') ?>
                        </div>
                        <div class="field<?= field_error('domaine_id') ? ' has-error' : '' ?>">
                            <label class="field__label" for="domaine_id">Domaine d'action lié</label>
                            <select class="select" id="domaine_id" name="domaine_id">
                                <option value="">Aucun</option>
                                <?php foreach ($domaines as $d): ?>
                                    <option value="<?= (int) $d['id'] ?>"<?= (string) old('domaine_id', (string) ($a['domaine_id'] ?? '')) === (string) $d['id'] ? ' selected' : '' ?>><?= e($d['titre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $erreur('domaine_id') ?>
                        </div>
                    </div>
                    <?php if ($isEdit): ?>
                        <div class="field<?= field_error('slug') ? ' has-error' : '' ?>">
                            <label class="field__label" for="slug">Adresse de la page (avancé)</label>
                            <input class="input" id="slug" name="slug" type="text" maxlength="180" value="<?= e(old('slug', $a['slug'])) ?>"<?= $invalide('slug') ?>>
                            <?= $erreur('slug') ?>
                            <p class="field__hint">Modifier l'adresse casse les liens déjà partagés.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="form-layout__side">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Publication</h2></div>
                <div class="card__body stack">
                    <fieldset class="field">
                        <legend class="field__label">Statut</legend>
                        <div class="segmented segmented--form">
                            <label class="segmented__item"><input type="radio" name="statut" value="brouillon"<?= $statut !== 'publie' ? ' checked' : '' ?>><span>Brouillon</span></label>
                            <label class="segmented__item"><input type="radio" name="statut" value="publie"<?= $statut === 'publie' ? ' checked' : '' ?>><span>Publiée</span></label>
                        </div>
                        <p class="field__hint">Un brouillon n'apparaît pas sur le site public.</p>
                    </fieldset>
                    <?php if ($isEdit): ?>
                        <dl class="kv">
                            <div><dt>Créée le</dt><dd><?= e(date_fr((string) $a['cree_le'])) ?></dd></div>
                            <div><dt>Auteur</dt><dd><?= e($a['auteur_nom'] ?? '—') ?></dd></div>
                        </dl>
                    <?php endif; ?>
                </div>
            </section>

            <?= View::partial('partials/admin/image-field', [
                'titre'        => 'Photo',
                'imageId'      => old('image_id', $a['image_id'] ?? null),
                'imageFichier' => $a['image_fichier'] ?? null,
                'images'       => $images,
                'aide'         => 'Photo de terrain réelle et consentie · JPEG, PNG ou WEBP, 5 Mo max.',
            ]) ?>

            <?= View::partial('partials/admin/gallery-field', ['galerie' => $galerie, 'images' => $images]) ?>

            <?php if ($isEdit): ?>
                <section class="card card--danger">
                    <div class="card__body stack">
                        <h2 class="card__title card__title--danger">Supprimer l'activité</h2>
                        <p class="card__text">L'activité sera retirée du site et de l'administration.</p>
                        <button type="submit" form="delete-form" class="btn btn--danger">Supprimer cette activité</button>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>

    <div class="savebar">
        <span class="savebar__status" data-dirty-status>Les champs marqués <span class="req">*</span> sont obligatoires.</span>
        <div class="savebar__actions">
            <a class="btn btn--secondary" href="<?= e(url('/admin/activites')) ?>">Annuler</a>
            <button type="submit" class="btn btn--primary"><?= icon('check', 17) ?> <?= $isEdit ? 'Enregistrer les modifications' : "Enregistrer l'activité" ?></button>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <form id="delete-form" method="post" action="<?= e(url('/admin/activites/' . (int) $a['id'] . '/supprimer')) ?>" data-confirm="« <?= e($a['titre']) ?> » sera supprimée du site et de l'administration. Cette action est définitive." data-confirm-title="Supprimer cette activité ?">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

<?php

use App\Models\Partenaire;

$pa = $partenaire ?? [];
$isEdit = $partenaire !== null;
$action = $isEdit ? '/admin/partenaires/' . (int) $pa['id'] : '/admin/partenaires';
$publie = old_bool('publie', $isEdit ? (int) $pa['publie'] === 1 : true);
$categorie = (string) old('categorie', $pa['categorie'] ?? '');
$logoId = old('image_id', $pa['logo_id'] ?? null);
$erreur = static fn (string $champ): string => field_error($champ) ? '<p class="field__error" id="' . $champ . '-err">' . icon('alert', 16) . ' ' . e(field_error($champ)) . '</p>' : '';
$invalide = static fn (string $champ): string => field_error($champ) ? ' aria-invalid="true" aria-describedby="' . $champ . '-err"' : '';
?>
<form method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" class="form-page" data-submit-once data-dirty-watch novalidate>
    <?= csrf_field() ?>
    <div class="page-head">
        <div>
            <a class="back-link" href="<?= e(url('/admin/partenaires')) ?>"><?= icon('back', 16) ?> Retour aux partenaires</a>
            <h1 class="page-title"><?= $isEdit ? e($pa['nom']) : 'Nouveau partenaire' ?></h1>
        </div>
    </div>

    <div class="form-layout">
        <div class="form-layout__main">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Identité du partenaire</h2></div>
                <div class="card__body stack">
                    <div class="field<?= field_error('nom') ? ' has-error' : '' ?>">
                        <label class="field__label" for="nom">Nom complet <span class="req" aria-hidden="true">*</span></label>
                        <input class="input" id="nom" name="nom" type="text" maxlength="200" required value="<?= e(old('nom', $pa['nom'] ?? '')) ?>"<?= $invalide('nom') ?>>
                        <?= $erreur('nom') ?>
                    </div>
                    <div class="grid-fields">
                        <div class="field<?= field_error('sigle') ? ' has-error' : '' ?>">
                            <label class="field__label" for="sigle">Sigle</label>
                            <input class="input" id="sigle" name="sigle" type="text" maxlength="60" value="<?= e(old('sigle', $pa['sigle'] ?? '')) ?>" placeholder="Ex. : OIF">
                            <?= $erreur('sigle') ?>
                        </div>
                        <div class="field<?= field_error('pays') ? ' has-error' : '' ?>">
                            <label class="field__label" for="pays">Pays</label>
                            <input class="input" id="pays" name="pays" type="text" maxlength="80" value="<?= e(old('pays', $pa['pays'] ?? '')) ?>">
                            <?= $erreur('pays') ?>
                        </div>
                    </div>
                    <fieldset class="field<?= field_error('categorie') ? ' has-error' : '' ?>">
                        <legend class="field__label">Catégorie <span class="req" aria-hidden="true">*</span></legend>
                        <div class="choice-grid">
                            <?php foreach (Partenaire::CATEGORIES as $cle => $libelle): ?>
                                <label class="choice">
                                    <input type="radio" name="categorie" value="<?= e($cle) ?>"<?= $categorie === $cle ? ' checked' : '' ?>>
                                    <span><?= e($libelle) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?= $erreur('categorie') ?>
                    </fieldset>
                    <div class="field<?= field_error('description') ? ' has-error' : '' ?>">
                        <div class="field__row">
                            <label class="field__label" for="description">Description courte</label>
                            <span class="field__counter" data-counter-for="description" aria-live="polite"></span>
                        </div>
                        <textarea class="input" id="description" name="description" rows="3" maxlength="400" data-maxlength="400"><?= e(old('description', $pa['description'] ?? '')) ?></textarea>
                        <?= $erreur('description') ?>
                    </div>
                    <div class="field<?= field_error('site_web') ? ' has-error' : '' ?>">
                        <label class="field__label" for="site_web">Site web</label>
                        <input class="input" id="site_web" name="site_web" type="url" maxlength="255" placeholder="https://" value="<?= e(old('site_web', $pa['site_web'] ?? '')) ?>"<?= $invalide('site_web') ?>>
                        <?= $erreur('site_web') ?>
                    </div>
                </div>
            </section>
        </div>

        <aside class="form-layout__side">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Affichage</h2></div>
                <div class="card__body">
                    <label class="switch">
                        <span class="switch__text"><strong>Visible sur le site public</strong><span>Page Partenaires et bandeau de l'accueil.</span></span>
                        <input type="checkbox" name="publie" value="1" role="switch"<?= $publie ? ' checked' : '' ?>>
                        <span class="switch__track" aria-hidden="true"></span>
                    </label>
                </div>
            </section>

            <section class="card">
                <div class="card__head"><h2 class="card__title">Logo</h2></div>
                <div class="card__body stack">
                    <?php if ($isEdit && $pa['logo_id'] && $pa['logo_fichier']): ?>
                        <figure class="image-current image-current--logo">
                            <img src="<?= e(upload_url((string) $pa['logo_fichier'])) ?>" alt="Logo actuel">
                            <label class="check"><input type="checkbox" name="retirer_image" value="1"><span>Retirer ce logo</span></label>
                        </figure>
                    <?php endif; ?>
                    <label class="dropzone<?= field_error('image_fichier') ? ' has-error' : '' ?>" data-dropzone>
                        <span class="dropzone__icon"><?= icon('upload', 22) ?></span>
                        <span class="dropzone__title" data-dropzone-label><?= $isEdit && $pa['logo_id'] ? 'Remplacer le logo' : 'Téléverser le logo' ?></span>
                        <span class="dropzone__hint">PNG sur fond transparent de préférence · 5 Mo max.</span>
                        <input type="file" name="image_fichier" accept="image/jpeg,image/png,image/webp" class="dropzone__input">
                    </label>
                    <?= $erreur('image_fichier') ?>
                    <p class="field__hint">Sans logo, le nom du partenaire est affiché en toutes lettres.</p>
                    <?php if ($logoId): ?><input type="hidden" name="image_id" value="<?= (int) $logoId ?>"><?php endif; ?>
                </div>
            </section>

            <?php if ($isEdit): ?>
                <section class="card card--danger">
                    <div class="card__body stack">
                        <h2 class="card__title card__title--danger">Supprimer le partenaire</h2>
                        <p class="card__text">Il sera aussi retiré des projets auxquels il est lié.</p>
                        <button type="submit" form="delete-form" class="btn btn--danger">Supprimer ce partenaire</button>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>

    <div class="savebar">
        <span class="savebar__status" data-dirty-status>Les champs marqués <span class="req">*</span> sont obligatoires.</span>
        <div class="savebar__actions">
            <a class="btn btn--secondary" href="<?= e(url('/admin/partenaires')) ?>">Annuler</a>
            <button type="submit" class="btn btn--primary"><?= icon('check', 17) ?> <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter le partenaire' ?></button>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <form id="delete-form" method="post" action="<?= e(url('/admin/partenaires/' . (int) $pa['id'] . '/supprimer')) ?>" data-confirm="« <?= e($pa['nom']) ?> » sera supprimé et retiré des projets auxquels il est lié." data-confirm-title="Supprimer ce partenaire ?">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

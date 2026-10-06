<?php

use App\Core\View;

$d = $domaine ?? [];
$isEdit = $domaine !== null;
$action = $isEdit ? '/admin/domaines/' . (int) $d['id'] : '/admin/domaines';
$publie = old_bool('publie', $isEdit ? (int) $d['publie'] === 1 : true);
$avant = old_bool('mis_en_avant', $isEdit && (int) $d['mis_en_avant'] === 1);
$erreur = static fn (string $champ): string => field_error($champ) ? '<p class="field__error" id="' . $champ . '-err">' . icon('alert', 16) . ' ' . e(field_error($champ)) . '</p>' : '';
$invalide = static fn (string $champ): string => field_error($champ) ? ' aria-invalid="true" aria-describedby="' . $champ . '-err"' : '';
?>
<form method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" class="form-page" data-submit-once data-dirty-watch novalidate>
    <?= csrf_field() ?>
    <div class="page-head">
        <div>
            <a class="back-link" href="<?= e(url('/admin/domaines')) ?>"><?= icon('back', 16) ?> Retour aux domaines</a>
            <h1 class="page-title"><?= $isEdit ? e($d['titre']) : "Nouveau domaine d'action" ?></h1>
        </div>
        <?php if ($isEdit && (int) $d['publie'] === 1): ?>
            <div class="page-head__actions">
                <a class="btn btn--secondary" href="<?= e(url('/domaines-action/' . $d['slug'])) ?>" target="_blank" rel="noopener"><?= icon('external', 17) ?> Voir sur le site</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-layout">
        <div class="form-layout__main">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Présentation</h2></div>
                <div class="card__body stack">
                    <div class="field<?= field_error('titre') ? ' has-error' : '' ?>">
                        <label class="field__label" for="titre">Titre <span class="req" aria-hidden="true">*</span></label>
                        <input class="input" id="titre" name="titre" type="text" maxlength="180" required value="<?= e(old('titre', $d['titre'] ?? '')) ?>"<?= $invalide('titre') ?>>
                        <?= $erreur('titre') ?>
                    </div>
                    <div class="field<?= field_error('resume') ? ' has-error' : '' ?>">
                        <div class="field__row">
                            <label class="field__label" for="resume">Résumé <span class="req" aria-hidden="true">*</span></label>
                            <span class="field__counter" data-counter-for="resume" aria-live="polite"></span>
                        </div>
                        <textarea class="input" id="resume" name="resume" rows="3" maxlength="400" data-maxlength="400" required<?= $invalide('resume') ?>><?= e(old('resume', $d['resume'] ?? '')) ?></textarea>
                        <?= $erreur('resume') ?>
                        <p class="field__hint">Affiché sur les cartes de l'accueil et de la page Domaines d'action.</p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="description">Description détaillée</label>
                        <textarea class="input editor-source" id="description" name="description" rows="10" data-editor><?= e(old('description', $d['description'] ?? '')) ?></textarea>
                    </div>
                    <?php if ($isEdit): ?>
                        <div class="field<?= field_error('slug') ? ' has-error' : '' ?>">
                            <label class="field__label" for="slug">Adresse de la page (avancé)</label>
                            <input class="input" id="slug" name="slug" type="text" maxlength="180" value="<?= e(old('slug', $d['slug'])) ?>"<?= $invalide('slug') ?>>
                            <?= $erreur('slug') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="form-layout__side">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Affichage</h2></div>
                <div class="card__body stack">
                    <label class="switch">
                        <span class="switch__text"><strong>Visible sur le site public</strong><span>Un domaine masqué n'apparaît nulle part.</span></span>
                        <input type="checkbox" name="publie" value="1" role="switch"<?= $publie ? ' checked' : '' ?>>
                        <span class="switch__track" aria-hidden="true"></span>
                    </label>
                    <label class="switch">
                        <span class="switch__text"><strong>Domaine phare</strong><span>Mis en avant en grand sur l'accueil (un seul à la fois).</span></span>
                        <input type="checkbox" name="mis_en_avant" value="1" role="switch"<?= $avant ? ' checked' : '' ?>>
                        <span class="switch__track" aria-hidden="true"></span>
                    </label>
                </div>
            </section>

            <?= View::partial('partials/admin/image-field', [
                'titre'        => 'Photo illustrative',
                'imageId'      => old('image_id', $d['image_id'] ?? null),
                'imageFichier' => $d['image_fichier'] ?? null,
                'images'       => $images,
                'aide'         => 'Photo de terrain réelle et consentie · 5 Mo max.',
            ]) ?>

            <?= View::partial('partials/admin/gallery-field', ['galerie' => $galerie, 'images' => $images]) ?>

            <?php if ($isEdit): ?>
                <section class="card card--danger">
                    <div class="card__body stack">
                        <h2 class="card__title card__title--danger">Supprimer le domaine</h2>
                        <p class="card__text">Les projets et activités liés sont conservés, sans domaine.</p>
                        <button type="submit" form="delete-form" class="btn btn--danger">Supprimer ce domaine</button>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>

    <div class="savebar">
        <span class="savebar__status" data-dirty-status>Les champs marqués <span class="req">*</span> sont obligatoires.</span>
        <div class="savebar__actions">
            <a class="btn btn--secondary" href="<?= e(url('/admin/domaines')) ?>">Annuler</a>
            <button type="submit" class="btn btn--primary"><?= icon('check', 17) ?> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le domaine' ?></button>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <form id="delete-form" method="post" action="<?= e(url('/admin/domaines/' . (int) $d['id'] . '/supprimer')) ?>" data-confirm="« <?= e($d['titre']) ?> » sera supprimé. Les projets et activités liés seront conservés, sans domaine." data-confirm-title="Supprimer ce domaine ?">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

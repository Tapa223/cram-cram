<?php
$courante = $sections[$section];
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Paramètres</h1>
        <p class="page-sub">Informations générales du site, coordonnées et textes de l'accueil.</p>
    </div>
</div>

<div class="settings">
    <nav class="settings__nav" aria-label="Sections des paramètres">
        <?php foreach ($sections as $cle => $s): ?>
            <a class="settings__link<?= $cle === $section ? ' is-active' : '' ?>" href="<?= e(url('/admin/parametres?section=' . $cle)) ?>"<?= $cle === $section ? ' aria-current="page"' : '' ?>><?= e($s['titre']) ?></a>
        <?php endforeach; ?>
    </nav>

    <form method="post" action="<?= e(url('/admin/parametres')) ?>" class="form-page" enctype="multipart/form-data" data-submit-once data-dirty-watch novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="_section" value="<?= e($section) ?>">
        <section class="card">
            <div class="card__head">
                <div><h2 class="card__title"><?= e($courante['titre']) ?></h2><p class="card__sub"><?= e($courante['aide']) ?></p></div>
            </div>
            <div class="card__body">
                <div class="grid-fields">
                    <?php foreach ($courante['champs'] as $cle => [$libelle, $max, $type]): ?>
                        <?php $erreur = field_error($cle); $valeur = (string) old($cle, $valeurs[$cle] ?? ''); ?>
                        <div class="field<?= $type === 'textarea' ? ' field--full' : '' ?><?= $erreur ? ' has-error' : '' ?>">
                            <label class="field__label" for="p-<?= e($cle) ?>"><?= e($libelle) ?></label>
                            <?php if ($type === 'textarea'): ?>
                                <textarea class="input" id="p-<?= e($cle) ?>" name="<?= e($cle) ?>" rows="3" maxlength="<?= (int) $max ?>"<?= $erreur ? ' aria-invalid="true" aria-describedby="err-' . e($cle) . '"' : '' ?>><?= e($valeur) ?></textarea>
                            <?php else: ?>
                                <input class="input" id="p-<?= e($cle) ?>" name="<?= e($cle) ?>" type="<?= $type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text') ?>" maxlength="<?= (int) $max ?>" value="<?= e($valeur) ?>"<?= $type === 'url' ? ' placeholder="https://"' : '' ?><?= $erreur ? ' aria-invalid="true" aria-describedby="err-' . e($cle) . '"' : '' ?>>
                            <?php endif; ?>
                            <?php if ($erreur): ?><p class="field__error" id="err-<?= e($cle) ?>"><?= icon('alert', 16) ?> <?= e($erreur) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php if ($section === 'accueil'): ?>
            <?= \App\Core\View::partial('partials/admin/image-field', [
                'titre'        => 'Image de la première section',
                'imageId'      => $image['id'] ?? null,
                'imageFichier' => $image['fichier'] ?? null,
                'images'       => $images,
                'aide'         => 'Photo représentative (paysage, 1600 px de large conseillés). JPG, PNG ou WebP, 5 Mo maximum. Le texte alternatif se règle dans Médias.',
            ]) ?>
        <?php endif; ?>
        <div class="savebar">
            <span class="savebar__status" data-dirty-status>Les modifications s'appliquent immédiatement au site.</span>
            <div class="savebar__actions">
                <button type="submit" class="btn btn--primary"><?= icon('check', 17) ?> Enregistrer</button>
            </div>
        </div>
    </form>
</div>

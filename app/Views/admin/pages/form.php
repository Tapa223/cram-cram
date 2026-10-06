<?php

use App\Models\Page;

$erreur = static fn (string $champ): string => field_error($champ) ? '<p class="field__error" id="' . $champ . '-err">' . icon('alert', 16) . ' ' . e(field_error($champ)) . '</p>' : '';
?>
<form method="post" action="<?= e(url('/admin/pages/' . (int) $page['id'])) ?>" class="form-page" data-submit-once data-dirty-watch novalidate>
    <?= csrf_field() ?>
    <div class="page-head">
        <div>
            <a class="back-link" href="<?= e(url('/admin/pages')) ?>"><?= icon('back', 16) ?> Retour aux pages</a>
            <h1 class="page-title"><?= e($page['titre']) ?></h1>
            <p class="page-meta">Modifiée le <?= e(date_fr((string) $page['modifie_le'], true)) ?></p>
        </div>
        <?php if (isset(Page::ADRESSES[$page['slug']])): ?>
            <div class="page-head__actions">
                <a class="btn btn--secondary" href="<?= e(url(Page::ADRESSES[$page['slug']])) ?>" target="_blank" rel="noopener"><?= icon('external', 17) ?> Voir la page</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-layout">
        <div class="form-layout__main">
            <section class="card">
                <div class="card__body stack">
                    <div class="field<?= field_error('titre') ? ' has-error' : '' ?>">
                        <label class="field__label" for="titre">Titre de la page <span class="req" aria-hidden="true">*</span></label>
                        <input class="input" id="titre" name="titre" type="text" maxlength="200" required value="<?= e(old('titre', $page['titre'])) ?>">
                        <?= $erreur('titre') ?>
                    </div>
                    <div class="field<?= field_error('chapo') ? ' has-error' : '' ?>">
                        <div class="field__row">
                            <label class="field__label" for="chapo">Introduction</label>
                            <span class="field__counter" data-counter-for="chapo" aria-live="polite"></span>
                        </div>
                        <textarea class="input" id="chapo" name="chapo" rows="3" maxlength="400" data-maxlength="400"><?= e(old('chapo', $page['chapo'] ?? '')) ?></textarea>
                        <?= $erreur('chapo') ?>
                        <p class="field__hint">Affichée en grand sous le titre de la page.</p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="contenu">Contenu</label>
                        <textarea class="input editor-source" id="contenu" name="contenu" rows="18" data-editor><?= e(old('contenu', $page['contenu'] ?? '')) ?></textarea>
                        <p class="field__hint">Utilisez « Titre » pour structurer la page en sections : chaque intertitre devient une rubrique.</p>
                    </div>
                </div>
            </section>
        </div>
        <aside class="form-layout__side">
            <section class="card">
                <div class="card__head"><h2 class="card__title">Référencement</h2></div>
                <div class="card__body stack">
                    <div class="field<?= field_error('meta_description') ? ' has-error' : '' ?>">
                        <div class="field__row">
                            <label class="field__label" for="meta_description">Description pour les moteurs de recherche</label>
                        </div>
                        <textarea class="input" id="meta_description" name="meta_description" rows="4" maxlength="300" data-maxlength="300"><?= e(old('meta_description', $page['meta_description'] ?? '')) ?></textarea>
                        <span class="field__counter" data-counter-for="meta_description" aria-live="polite"></span>
                        <?= $erreur('meta_description') ?>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <div class="savebar">
        <span class="savebar__status" data-dirty-status>Les modifications sont visibles immédiatement après l'enregistrement.</span>
        <div class="savebar__actions">
            <a class="btn btn--secondary" href="<?= e(url('/admin/pages')) ?>">Annuler</a>
            <button type="submit" class="btn btn--primary"><?= icon('check', 17) ?> Enregistrer la page</button>
        </div>
    </div>
</form>

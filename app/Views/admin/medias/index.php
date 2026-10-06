<?php

use App\Core\View;
use App\Services\MediaUploader;

$retour = $_GET !== [] ? '?' . http_build_query($_GET) : '';
$onglets = ['' => 'Tous les fichiers', 'images' => 'Images', 'documents' => 'Documents PDF'];
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Médiathèque <span class="count-pill"><?= $pager->total ?></span></h1>
        <p class="page-sub">Photos, logos et documents utilisés sur le site · <?= e($volume) ?> utilisés.</p>
    </div>
</div>

<section class="card">
    <div class="card__head">
        <div>
            <h2 class="card__title">Ajouter des fichiers</h2>
            <p class="card__sub">Images JPEG, PNG ou WEBP (5 Mo max., redimensionnées automatiquement) et documents PDF (10 Mo max.).</p>
        </div>
    </div>
    <div class="card__body">
        <form method="post" action="<?= e(url('/admin/medias')) ?>" enctype="multipart/form-data" class="upload-panel" data-submit-once>
            <?= csrf_field() ?>
            <label class="dropzone dropzone--wide" data-dropzone>
                <span class="dropzone__icon"><?= icon('upload', 22) ?></span>
                <span class="dropzone__title" data-dropzone-label>Glissez vos fichiers ici ou parcourez</span>
                <span class="dropzone__hint">Plusieurs fichiers possibles. Utilisez uniquement des photos réelles et consenties.</span>
                <input type="file" name="fichiers[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf" class="dropzone__input" required>
            </label>
            <div class="stack upload-panel__side">
                <label class="check">
                    <input type="checkbox" name="est_publication" value="1">
                    <span>Publier les PDF dans la rubrique <strong>Recherche</strong> (rapports, notes, bulletins)</span>
                </label>
                <button type="submit" class="btn btn--primary"><?= icon('upload', 18) ?> Téléverser</button>
            </div>
        </form>
    </div>
</section>

<nav class="tabs" aria-label="Type de fichier">
    <?php foreach ($onglets as $cle => $libelle): ?>
        <?php $query = array_filter(['type' => $cle, 'q' => $q], static fn ($v) => $v !== ''); ?>
        <a class="tab<?= $type === $cle ? ' is-active' : '' ?>" href="<?= e(url('/admin/medias') . ($query ? '?' . http_build_query($query) : '')) ?>"<?= $type === $cle ? ' aria-current="page"' : '' ?>><?= e($libelle) ?></a>
    <?php endforeach; ?>
</nav>

<section class="card card--flush">
    <form class="toolbar" method="get" action="<?= e(url('/admin/medias')) ?>">
        <?php if ($type !== ''): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?>
        <label class="search-field">
            <?= icon('search', 18) ?>
            <span class="sr-only">Rechercher un fichier</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, titre ou texte alternatif">
        </label>
        <?php if ($q !== ''): ?><a class="btn btn--ghost" href="<?= e(url('/admin/medias' . ($type !== '' ? '?type=' . $type : ''))) ?>">Effacer</a><?php endif; ?>
    </form>

    <?php if ($medias === []): ?>
        <?= View::partial('partials/admin/empty', [
            'icone' => 'image',
            'titre' => $q !== '' || $type !== '' ? 'Aucun fichier ne correspond' : 'La médiathèque est vide',
            'texte' => $q !== '' || $type !== '' ? 'Modifiez la recherche ou le type de fichier.' : 'Ajoutez des photos de terrain, des logos ou des documents PDF ci-dessus.',
        ]) ?>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($medias as $m): ?>
                <?php $isImage = str_starts_with((string) $m['type_mime'], 'image/'); ?>
                <article class="media-card">
                    <div class="media-card__preview">
                        <?php if ($isImage): ?>
                            <img src="<?= e(upload_url((string) $m['fichier'])) ?>" alt="<?= e($m['texte_alt'] ?? '') ?>" loading="lazy">
                        <?php else: ?>
                            <span class="media-card__doc"><?= icon('file', 34) ?> PDF</span>
                        <?php endif; ?>
                        <?php if ((int) $m['utilisations'] > 0): ?>
                            <span class="badge badge--blue media-card__tag">Utilisé <?= (int) $m['utilisations'] ?>×</span>
                        <?php elseif ((int) $m['est_publication'] === 1): ?>
                            <span class="badge badge--green media-card__tag">Publication</span>
                        <?php endif; ?>
                    </div>
                    <div class="media-card__body">
                        <p class="media-card__name"><?= e($m['titre'] ?: $m['nom_original']) ?></p>
                        <p class="media-card__meta"><?= e(MediaUploader::humanSize((int) $m['taille'])) ?><?= $m['largeur'] ? ' · ' . (int) $m['largeur'] . '×' . (int) $m['hauteur'] : '' ?> · <?= e(date_court_fr((string) $m['cree_le'])) ?></p>
                        <?php if ($isImage && empty($m['texte_alt'])): ?><p class="media-card__meta media-card__warn">Texte alternatif manquant</p><?php endif; ?>
                        <details>
                            <summary>Modifier les informations</summary>
                            <form method="post" action="<?= e(url('/admin/medias/' . (int) $m['id'])) ?>" class="media-card__form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_retour" value="<?= e($retour) ?>">
                                <label class="field">
                                    <span class="field__label">Titre</span>
                                    <input class="input" type="text" name="titre" maxlength="200" value="<?= e($m['titre'] ?? '') ?>">
                                </label>
                                <?php if ($isImage): ?>
                                    <label class="field">
                                        <span class="field__label">Texte alternatif</span>
                                        <input class="input" type="text" name="texte_alt" maxlength="255" value="<?= e($m['texte_alt'] ?? '') ?>" placeholder="Décrire l'image en une phrase">
                                    </label>
                                <?php else: ?>
                                    <label class="check"><input type="checkbox" name="est_publication" value="1"<?= (int) $m['est_publication'] === 1 ? ' checked' : '' ?>><span>Afficher dans Recherche</span></label>
                                <?php endif; ?>
                                <button type="submit" class="btn btn--secondary btn--sm">Enregistrer</button>
                            </form>
                        </details>
                        <div class="media-card__actions">
                            <a class="btn btn--ghost btn--sm" href="<?= e(upload_url((string) $m['fichier'])) ?>" target="_blank" rel="noopener"><?= icon('external', 15) ?> Ouvrir</a>
                            <form method="post" action="<?= e(url('/admin/medias/' . (int) $m['id'] . '/supprimer')) ?>" data-confirm="« <?= e($m['nom_original']) ?> » sera supprimé définitivement.<?= (int) $m['utilisations'] > 0 ? ' Il est utilisé par ' . (int) $m['utilisations'] . ' contenu(s), qui n\'afficheront plus d\'image.' : '' ?>" data-confirm-title="Supprimer ce fichier ?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_retour" value="<?= e($retour) ?>">
                                <button type="submit" class="btn btn--ghost btn--sm media-card__delete"><?= icon('trash', 15) ?> Supprimer</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= View::partial('partials/admin/pagination', ['pager' => $pager, 'libelle' => 'fichiers']) ?>
    <?php endif; ?>
</section>

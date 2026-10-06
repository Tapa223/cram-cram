<?php
/**
 * Galerie photos d'un contenu : photos actuelles (retrait possible),
 * ajout de plusieurs fichiers à la fois ou choix dans la médiathèque.
 * @var list<array<string, mixed>> $galerie
 * @var list<array<string, mixed>> $images
 */
$dejaLa = array_map(static fn (array $m): int => (int) $m['id'], $galerie);
$disponibles = array_values(array_filter($images, static fn (array $m): bool => !in_array((int) $m['id'], $dejaLa, true)));
?>
<section class="card">
    <div class="card__head">
        <div>
            <h2 class="card__title">Galerie photos</h2>
            <p class="card__sub"><?= $galerie === [] ? 'Photos des activités menées sur cette thématique.' : pluriel(count($galerie), 'photo') . ' affichée' . (count($galerie) > 1 ? 's' : '') . ' sur le site' ?></p>
        </div>
    </div>
    <div class="card__body stack">
        <?php if ($galerie !== []): ?>
            <ul class="gallery-admin">
                <?php foreach ($galerie as $photo): ?>
                    <li class="gallery-admin__item">
                        <img src="<?= e(upload_url((string) $photo['fichier'])) ?>" alt="<?= e($photo['texte_alt'] ?: $photo['nom_original']) ?>" loading="lazy">
                        <label class="gallery-admin__remove">
                            <input type="checkbox" name="galerie_retirer[]" value="<?= (int) $photo['id'] ?>">
                            <span><?= icon('trash', 14) ?> Retirer</span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <label class="dropzone dropzone--compact" data-dropzone>
            <span class="dropzone__icon"><?= icon('upload', 20) ?></span>
            <span class="dropzone__title" data-dropzone-label>Ajouter des photos</span>
            <span class="dropzone__hint">Plusieurs fichiers possibles (20 au maximum par envoi) · JPEG, PNG ou WEBP, 5 Mo chacun</span>
            <input type="file" name="galerie_fichiers[]" accept="image/jpeg,image/png,image/webp" multiple class="dropzone__input">
        </label>

        <?php if ($disponibles !== []): ?>
            <details class="picker">
                <summary class="btn btn--secondary btn--block">Ajouter depuis la médiathèque</summary>
                <fieldset class="picker__grid">
                    <legend class="sr-only">Images à ajouter à la galerie</legend>
                    <?php foreach ($disponibles as $img): ?>
                        <label class="picker__item picker__item--multi">
                            <input type="checkbox" name="galerie_ids[]" value="<?= (int) $img['id'] ?>">
                            <img src="<?= e(upload_url((string) $img['fichier'])) ?>" alt="<?= e($img['texte_alt'] ?: $img['nom_original']) ?>" loading="lazy">
                            <span class="picker__check" aria-hidden="true"><?= icon('check', 14) ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            </details>
        <?php endif; ?>
    </div>
</section>

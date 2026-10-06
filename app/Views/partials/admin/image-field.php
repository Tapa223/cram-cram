<?php
/**
 * Champ image : téléversement, choix dans la médiathèque ou retrait.
 * @var string $titre
 * @var int|string|null $imageId
 * @var string|null $imageFichier
 * @var list<array<string, mixed>> $images
 * @var string $aide
 */
$currentId = $imageId !== null && $imageId !== '' ? (int) $imageId : null;
$error = field_error('image_fichier');
?>
<section class="card">
    <div class="card__head"><h2 class="card__title"><?= e($titre) ?></h2></div>
    <div class="card__body stack">
        <?php if ($currentId && $imageFichier): ?>
            <figure class="image-current">
                <img src="<?= e(upload_url($imageFichier)) ?>" alt="" loading="lazy">
                <label class="check">
                    <input type="checkbox" name="retirer_image" value="1">
                    <span>Retirer cette image</span>
                </label>
            </figure>
        <?php endif; ?>

        <label class="dropzone<?= $error ? ' has-error' : '' ?>" data-dropzone>
            <span class="dropzone__icon"><?= icon('upload', 22) ?></span>
            <span class="dropzone__title" data-dropzone-label><?= $currentId ? 'Remplacer par une nouvelle image' : 'Glissez une image ou parcourez' ?></span>
            <span class="dropzone__hint"><?= e($aide) ?></span>
            <input type="file" name="image_fichier" accept="image/jpeg,image/png,image/webp" class="dropzone__input"<?= $error ? ' aria-invalid="true" aria-describedby="image-error"' : '' ?>>
        </label>
        <?php if ($error): ?>
            <p class="field__error" id="image-error"><?= icon('alert', 16) ?> <?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($images !== []): ?>
            <details class="picker">
                <summary class="btn btn--secondary btn--block">Choisir dans la médiathèque</summary>
                <fieldset class="picker__grid">
                    <legend class="sr-only">Images de la médiathèque</legend>
                    <?php foreach ($images as $img): ?>
                        <label class="picker__item">
                            <input type="radio" name="image_id" value="<?= (int) $img['id'] ?>"<?= $currentId === (int) $img['id'] ? ' checked' : '' ?>>
                            <img src="<?= e(upload_url((string) $img['fichier'])) ?>" alt="<?= e($img['texte_alt'] ?: $img['nom_original']) ?>" loading="lazy">
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            </details>
        <?php endif; ?>
    </div>
</section>

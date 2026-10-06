<?php
/**
 * Galerie photos publique. Chaque vignette ouvre la photo en grand :
 * dans une visionneuse avec JavaScript, sinon directement dans le navigateur.
 * @var list<array<string, mixed>> $galerie
 * @var string $titre
 */
if ($galerie === []) {
    return;
}
?>
<section class="gallery reveal" aria-labelledby="galerie-titre" data-gallery>
    <div class="gallery__head">
        <h2 class="block-title" id="galerie-titre"><?= e($titre ?? 'En images') ?></h2>
        <span class="gallery__count"><?= pluriel(count($galerie), 'photo') ?></span>
    </div>
    <ul class="gallery__grid<?= count($galerie) === 1 ? ' gallery__grid--single' : '' ?>">
        <?php foreach ($galerie as $i => $photo): ?>
            <?php $legende = (string) ($photo['texte_alt'] ?: ($photo['titre'] ?? '')); ?>
            <li class="gallery__item<?= $i === 0 && count($galerie) >= 5 ? ' gallery__item--large' : '' ?>">
                <a href="<?= e(upload_url((string) $photo['fichier'])) ?>" class="gallery__link" data-gallery-item data-caption="<?= e($legende) ?>">
                    <img src="<?= e(upload_url((string) $photo['fichier'])) ?>" alt="<?= e($legende) ?>" loading="lazy" decoding="async">
                    <span class="gallery__zoom" aria-hidden="true"><?= icon('search', 18) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <dialog class="lightbox" data-lightbox aria-label="Visionneuse de photos">
        <figure class="lightbox__figure">
            <img class="lightbox__img" src="" alt="" data-lightbox-img>
            <figcaption class="lightbox__caption" data-lightbox-caption></figcaption>
        </figure>
        <button type="button" class="lightbox__btn lightbox__btn--close" data-lightbox-close aria-label="Fermer"><?= icon('x', 22) ?></button>
        <button type="button" class="lightbox__btn lightbox__btn--prev" data-lightbox-prev aria-label="Photo précédente"><?= icon('back', 22) ?></button>
        <button type="button" class="lightbox__btn lightbox__btn--next" data-lightbox-next aria-label="Photo suivante"><?= icon('arrow', 22) ?></button>
        <span class="lightbox__counter" data-lightbox-counter></span>
    </dialog>
</section>

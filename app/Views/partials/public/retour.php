<?php
/**
 * Flèche de retour discrète : revient à la page précédente du site si possible (site.js),
 * sinon à la page parente indiquée.
 * @var string $href
 */
?>
<a class="back-link" href="<?= e(url($href)) ?>" data-back aria-label="Retour" title="Retour"><?= icon('back', 18) ?></a>

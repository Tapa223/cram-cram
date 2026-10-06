<?php
/**
 * Flèche de retour : revient à la page précédente du site si possible (site.js),
 * sinon à la page parente indiquée.
 * @var string $href
 */
?>
<a class="back-link" href="<?= e(url($href)) ?>" data-back><?= icon('back', 18) ?><span>Retour</span></a>

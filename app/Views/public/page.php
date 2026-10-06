<?php

use App\Core\View;

/**
 * Page éditoriale (Qui sommes-nous, Mentions légales).
 * Le contenu est déjà nettoyé à l'enregistrement par ContentSanitizer (liste blanche stricte).
 * @var array<string, mixed> $page
 * @var string $variante
 */
$sections = [];
$contenu = (string) $page['contenu'];
// Découpe en rubriques à chaque intertitre h2 : chaque rubrique devient un bloc éditorial.
$morceaux = preg_split('#(?=<h2>)#', $contenu, -1, PREG_SPLIT_NO_EMPTY) ?: [];
foreach ($morceaux as $morceau) {
    if (preg_match('#^<h2>(.*?)</h2>(.*)$#s', $morceau, $m)) {
        $sections[] = ['titre' => html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'), 'html' => $m[2]];
    } elseif (trim(strip_tags($morceau)) !== '') {
        $sections[] = ['titre' => null, 'html' => $morceau];
    }
}
?>
<?= View::partial('partials/public/page-hero', [
    'titre'   => $page['titre'],
    'chapo'   => $page['chapo'],
]) ?>

<section class="section section--first">
    <div class="wrap">
        <?php if ($sections === []): ?>
            <p class="lead">Cette page est en cours de rédaction.</p>
        <?php elseif ($variante === 'qui'): ?>
            <div class="chapters">
                <?php foreach ($sections as $i => $s): ?>
                    <article class="chapter reveal">
                        <?php if ($s['titre']): ?>
                            <h2 class="chapter__title"><span class="chapter__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($s['titre']) ?></h2>
                        <?php endif; ?>
                        <div class="prose"><?= $s['html'] ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="prose prose--narrow"><?= $contenu ?></div>
        <?php endif; ?>
    </div>
</section>

<?php if ($variante === 'qui'): ?>
    <?= View::partial('partials/public/cta') ?>
<?php endif; ?>

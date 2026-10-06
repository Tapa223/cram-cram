<?php

use App\Core\View;
use App\Models\Page;

/**
 * @var array<string, mixed>|null $imageAccueil
 * @var list<array<string, mixed>> $domaines
 */
$recherche = Page::findBySlug('recherche');
$valeurs = [];
for ($n = 1; $n <= 3; $n++) {
    if (setting('valeur_' . $n . '_titre') !== '') {
        $valeurs[] = ['titre' => setting('valeur_' . $n . '_titre'), 'texte' => setting('valeur_' . $n . '_texte')];
    }
}
?>
<section class="hero">
    <div class="wrap hero__grid">
        <div class="hero__text">
            <h1 class="hero__title"><?= e(setting('accueil_titre', 'Construire la paix, protéger l\'espace numérique, renforcer la cohésion sociale.')) ?></h1>
            <p class="hero__lead"><?= e(setting('accueil_intro')) ?></p>
            <div class="hero__actions">
                <a class="btn btn--light" href="<?= e(url('/domaines-action')) ?>">Nos domaines d'action <?= icon('arrow', 18) ?></a>
                <a class="btn btn--ghost" href="<?= e(url('/contact')) ?>">Travailler avec nous</a>
            </div>
        </div>

        <figure class="hero__figure">
            <?php if ($imageAccueil): ?>
                <img class="hero__photo" src="<?= e(upload_url((string) $imageAccueil['fichier'])) ?>" alt="<?= e((string) ($imageAccueil['texte_alt'] ?: '')) ?>" decoding="async" fetchpriority="high">
            <?php else: ?>
                <?php
                $tuiles = array_map(static fn (array $d): string => domaine_icone($d['slug']), array_slice($domaines, 0, 8));
                $tuiles = array_slice(array_merge($tuiles, ['handshake', 'globe', 'network', 'peace']), 0, 8);
                array_splice($tuiles, 4, 0, ['logo']);
                ?>
                <div class="hero__mosaic" aria-hidden="true">
                    <?php foreach ($tuiles as $i => $t): ?>
                        <?php if ($t === 'logo'): ?>
                            <span class="hero__tile hero__tile--logo"><?= logo(96, true) ?></span>
                        <?php else: ?>
                            <span class="hero__tile hero__tile--<?= $i % 3 ?>"><?= icon($t, 30) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (setting('accueil_encart_valeur') !== ''): ?>
                <figcaption class="hero__caption">
                    <strong><?= e(setting('accueil_encart_valeur')) ?></strong>
                    <span><?= e(setting('accueil_encart_texte')) ?></span>
                </figcaption>
            <?php endif; ?>
        </figure>
    </div>
</section>

<?php if ($reperes !== []): ?>
    <section class="reperes" aria-label="Repères">
        <div class="wrap">
            <ul class="reperes__grid">
                <?php foreach ($reperes as $r): ?>
                    <li class="repere">
                        <span class="repere__valeur"><?= e($r['valeur']) ?></span>
                        <span class="repere__libelle"><?= e($r['libelle']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<section class="section">
    <div class="wrap split">
        <div class="split__intro reveal">
            <h2 class="section-title"><?= e(setting('accueil_qsn_titre', 'Qui sommes-nous')) ?></h2>
            <p class="lead"><?= e(setting('accueil_qsn_texte')) ?></p>
            <a class="link-arrow" href="<?= e(url('/qui-sommes-nous')) ?>">Découvrir notre histoire <?= icon('arrow', 16) ?></a>
        </div>
        <div class="piliers">
            <?php foreach (['Mission' => ['accueil_mission', 'target'], 'Vision' => ['accueil_vision', 'compass'], 'Gouvernance' => ['accueil_gouvernance', 'users']] as $libelle => [$cle, $ic]): ?>
                <?php if (setting($cle) !== ''): ?>
                    <article class="pilier reveal">
                        <span class="pilier__icon"><?= icon($ic, 24) ?></span>
                        <div>
                            <h3 class="pilier__title"><?= e($libelle) ?></h3>
                            <p class="pilier__text"><?= e(setting($cle)) ?></p>
                        </div>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($domaines !== []): ?>
<section class="section section--tint">
    <div class="wrap">
        <div class="section-head reveal">
            <h2 class="section-title">Nos domaines d'action</h2>
            <a class="link-arrow" href="<?= e(url('/domaines-action')) ?>">Tous les domaines <?= icon('arrow', 16) ?></a>
        </div>
        <div class="domaines-grid">
            <?php foreach ($domaines as $i => $d): ?>
                <article class="domaine-card reveal">
                    <div class="domaine-card__top">
                        <span class="domaine-card__icon"><?= icon(domaine_icone($d['slug']), 26) ?></span>
                        <span class="domaine-card__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <?php if ((int) $d['mis_en_avant'] === 1): ?><span class="domaine-card__phare">Domaine phare</span><?php endif; ?>
                    <h3 class="domaine-card__title"><a class="stretched" href="<?= e(url('/domaines-action/' . $d['slug'])) ?>"><?= e($d['titre']) ?></a></h3>
                    <p class="domaine-card__text"><?= e($d['resume']) ?></p>
                </article>
            <?php endforeach; ?>
            <?php if (count($domaines) % 4 !== 0): ?>
                <a class="domaine-card domaine-card--all reveal" href="<?= e(url('/domaines-action')) ?>">
                    <span class="domaine-card__title">Explorer nos <?= count($domaines) ?> domaines en détail</span>
                    <span class="link-arrow link-arrow--light">Voir tout <?= icon('arrow', 16) ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($projets !== []): ?>
<section class="section">
    <div class="wrap">
        <div class="section-head reveal">
            <h2 class="section-title">Projets et réalisations</h2>
            <a class="link-arrow" href="<?= e(url('/projets')) ?>">Tous les projets <?= icon('arrow', 16) ?></a>
        </div>
        <div class="grid-3">
            <?php foreach ($projets as $i => $p): ?>
                <div class="reveal"><?= View::partial('partials/public/projet-card', ['p' => $p, 'i' => $i]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section--navy">
    <div class="wrap split split--center">
        <div class="reveal">
            <h2 class="section-title section-title--light">Recherche et consortiums</h2>
            <?php if ($recherche && !empty($recherche['chapo'])): ?>
                <p class="lead lead--light"><?= e($recherche['chapo']) ?></p>
            <?php endif; ?>
            <ul class="checks">
                <li><span class="checks__icon"><?= icon('check', 16) ?></span> Notes d'analyse sur les dynamiques de conflit amplifiées par la technologie</li>
                <li><span class="checks__icon"><?= icon('check', 16) ?></span> Bulletins périodiques sur les dynamiques de conflit et les opportunités de paix</li>
                <li><span class="checks__icon"><?= icon('check', 16) ?></span> Études scientifiques menées dans le cadre du consortium de recherche</li>
            </ul>
            <a class="btn btn--light" href="<?= e(url('/recherche')) ?>">Découvrir la recherche</a>
        </div>
        <?php if ($consortium !== []): ?>
            <div class="consortium reveal">
                <p class="consortium__title">Consortium Mali – Bénin</p>
                <ul class="consortium__list">
                    <li><span class="consortium__code consortium__code--main">ML</span><span><strong><?= e(setting('site_nom', 'CRAM-CRAM Mali')) ?></strong><span>Mali</span></span></li>
                    <?php foreach ($consortium as $membre): ?>
                        <li><span class="consortium__code"><?= e(['Mali' => 'ML', 'Bénin' => 'BJ'][$membre['pays'] ?? ''] ?? mb_strtoupper(mb_substr((string) ($membre['pays'] ?? ''), 0, 2))) ?></span><span><strong><?= e($membre['nom']) ?></strong><span><?= e($membre['pays'] ?? '') ?></span></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($valeurs !== []): ?>
<section class="section section--clair">
    <div class="wrap">
        <div class="section-head reveal">
            <h2 class="section-title">Notre valeur ajoutée</h2>
            <a class="link-arrow" href="<?= e(url('/valeur-ajoutee')) ?>">En savoir plus <?= icon('arrow', 16) ?></a>
        </div>
        <div class="valeurs">
            <?php foreach ($valeurs as $i => $v): ?>
                <article class="valeur reveal">
                    <div class="valeur__top">
                        <span class="valeur__icon"><?= icon(['eye', 'network', 'globe'][$i] ?? 'check', 24) ?></span>
                        <span class="valeur__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <h3 class="valeur__title"><a class="stretched" href="<?= e(url('/valeur-ajoutee')) ?>"><?= e($v['titre']) ?></a></h3>
                    <p><?= e($v['texte']) ?></p>
                    <span class="link-arrow link-arrow--sm" aria-hidden="true">Lire la suite <?= icon('arrow', 16) ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($activites !== []): ?>
<section class="section">
    <div class="wrap">
        <div class="section-head reveal">
            <h2 class="section-title">Actualités du terrain</h2>
            <a class="link-arrow" href="<?= e(url('/actualites')) ?>">Toutes les actualités <?= icon('arrow', 16) ?></a>
        </div>
        <div class="actus">
            <?php foreach ($activites as $i => $a): ?>
                <div class="reveal"><?= View::partial('partials/public/actu-card', ['a' => $a, 'grand' => $i === 0]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($partenaires !== []): ?>
<section class="section section--tint">
    <div class="wrap">
        <div class="section-head reveal">
            <h2 class="section-title">Ils nous font confiance</h2>
            <a class="link-arrow" href="<?= e(url('/partenaires')) ?>">Tous nos partenaires <?= icon('arrow', 16) ?></a>
        </div>
        <ul class="logo-wall">
            <?php foreach ($partenaires as $pa): ?>
                <li class="logo-tile">
                    <span class="logo-tile__logo">
                        <?php if (!empty($pa['logo_fichier'])): ?>
                            <img src="<?= e(upload_url((string) $pa['logo_fichier'])) ?>" alt="Logo <?= e($pa['nom']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="logo-tile__mono" aria-hidden="true"><?= e(monogramme($pa['sigle'], (string) $pa['nom'])) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="logo-tile__name"><?= e($pa['sigle'] ?: $pa['nom']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?= View::partial('partials/public/cta') ?>

<?php

use App\Core\View;
use App\Models\Partenaire;
use App\Services\ActivityLog;
use App\Services\Auth;

$admin = Auth::user();
$prenom = explode(' ', trim((string) ($admin['nom'] ?? '')))[0] ?? '';
$periodes = ['7j' => '7 jours', '30j' => '30 jours', '12m' => '12 mois'];
$maxDomaine = max(1, ...array_map(static fn (array $d): int => (int) $d['total'], $parDomaine ?: [['total' => 1]]));
$categoriesNonVides = count(array_filter(array_intersect_key($partenaires, Partenaire::CATEGORIES)));
?>
<div class="page-head">
    <div>
        <p class="page-head__date"><?= e(jour_fr()) ?></p>
        <h1 class="page-title">Bonjour<?= $prenom !== '' ? ', ' . e($prenom) : '' ?></h1>
        <p class="page-sub">Voici l'état du site et ce qui demande votre attention.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= e(url('/admin/activites/nouvelle')) ?>"><?= icon('plus', 18) ?> Nouvelle activité</a>
        <a class="btn btn--primary" href="<?= e(url('/admin/projets/nouveau')) ?>"><?= icon('plus', 18) ?> Nouveau projet</a>
    </div>
</div>

<div class="kpis">
    <a class="kpi kpi--blue" href="<?= e(url('/admin/projets')) ?>">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('folder') ?></span> Projets</span>
        <span class="kpi__value"><?= $projets['tous'] ?></span>
        <span class="kpi__mark" aria-hidden="true"><?= icon('folder', 96) ?></span>
        <span class="kpi__meta"><?= $projetsPublies ?> publié<?= $projetsPublies > 1 ? 's' : '' ?> · <?= $projetsBrouillon ?> non publié<?= $projetsBrouillon > 1 ? 's' : '' ?></span>
    </a>
    <a class="kpi kpi--sky" href="<?= e(url('/admin/partenaires')) ?>">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('users') ?></span> Partenaires</span>
        <span class="kpi__value"><?= $partenaires['tous'] ?></span>
        <span class="kpi__mark" aria-hidden="true"><?= icon('users', 96) ?></span>
        <span class="kpi__meta">Répartis en <?= pluriel($categoriesNonVides, 'catégorie') ?></span>
    </a>
    <a class="kpi kpi--green" href="<?= e(url('/admin/activites')) ?>">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('calendar') ?></span> Activités</span>
        <span class="kpi__value"><?= $activites['tous'] ?></span>
        <span class="kpi__mark" aria-hidden="true"><?= icon('calendar', 96) ?></span>
        <span class="kpi__meta"><?= $activites['publie'] ?> publiée<?= $activites['publie'] > 1 ? 's' : '' ?> · <?= $activites['brouillon'] ?> brouillon<?= $activites['brouillon'] > 1 ? 's' : '' ?></span>
    </a>
    <a class="kpi kpi--navy<?= $nonLus > 0 ? ' kpi--alert' : '' ?>" href="<?= e(url('/admin/messages?filtre=non_lus')) ?>">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('mail') ?></span> Messages non lus</span>
        <span class="kpi__value"><?= $nonLus ?></span>
        <span class="kpi__mark" aria-hidden="true"><?= icon('mail', 96) ?></span>
        <span class="kpi__meta"><?= $derniersMessages !== [] ? 'Dernier reçu ' . e(depuis((string) $derniersMessages[0]['cree_le'])) : 'Aucun message en attente' ?></span>
    </a>
</div>

<div class="grid grid--2-1">
    <section class="card card--blue">
        <div class="card__head">
            <div>
                <h2 class="card__title"><span class="card__chip"><?= icon('chart', 18) ?></span> Fréquentation du site</h2>
                <p class="card__sub"><strong class="card__figure"><?= number_format($totalVisites, 0, ',', ' ') ?></strong> <?= $totalVisites > 1 ? 'visites' : 'visite' ?> sur <?= e($periodes[$periode]) ?></p>
            </div>
            <nav class="segmented" aria-label="Période">
                <?php foreach ($periodes as $cle => $libelle): ?>
                    <a class="segmented__item<?= $periode === $cle ? ' is-active' : '' ?>" href="<?= e(url('/admin?periode=' . $cle)) ?>"<?= $periode === $cle ? ' aria-current="true"' : '' ?>><?= e($libelle) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="card__body">
            <?php if ($totalVisites === 0): ?>
                <?= View::partial('partials/admin/empty', [
                    'icone' => 'chart',
                    'titre' => 'Pas encore de visite enregistrée sur cette période',
                    'texte' => 'Le compteur démarre dès la mise en ligne. Seules des données agrégées et anonymes sont conservées.',
                ]) ?>
            <?php else: ?>
                <?= View::partial('partials/admin/bar-chart', ['series' => $series, 'unite' => 'visites', 'description' => 'Visites du site sur ' . $periodes[$periode] . ', total ' . $totalVisites, 'id' => 'dash']) ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="card card--red">
        <div class="card__head"><h2 class="card__title"><span class="card__chip"><?= icon('bell', 18) ?></span> À traiter</h2></div>
        <div class="card__body">
            <ul class="todo">
                <?php foreach ($derniersMessages as $m): ?>
                    <li>
                        <a class="todo__item" href="<?= e(url('/admin/messages/' . (int) $m['id'])) ?>">
                            <span class="dot dot--red" aria-hidden="true"></span>
                            <span class="todo__text"><strong><?= e($m['objet']) ?></strong><span><?= e($m['nom']) ?> · <?= e(depuis((string) $m['cree_le'])) ?></span></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if ($activites['brouillon'] > 0): ?>
                    <li><a class="todo__item" href="<?= e(url('/admin/activites?statut=brouillon')) ?>"><span class="dot dot--blue" aria-hidden="true"></span><span class="todo__text"><strong><?= pluriel($activites['brouillon'], 'activité en brouillon', 'activités en brouillon') ?></strong><span>Non visibles sur le site public</span></span></a></li>
                <?php endif; ?>
                <?php if ($projetsBrouillon > 0): ?>
                    <li><a class="todo__item" href="<?= e(url('/admin/projets?publication=brouillon')) ?>"><span class="dot dot--blue" aria-hidden="true"></span><span class="todo__text"><strong><?= pluriel($projetsBrouillon, 'projet non publié', 'projets non publiés') ?></strong><span>À relire avant publication</span></span></a></li>
                <?php endif; ?>
            </ul>
            <?php if ($derniersMessages === [] && $activites['brouillon'] === 0 && $projetsBrouillon === 0): ?>
                <?= View::partial('partials/admin/empty', ['icone' => 'check', 'titre' => 'Tout est à jour', 'texte' => 'Aucun message ni contenu en attente.']) ?>
            <?php endif; ?>
            <a class="btn btn--soft btn--block" href="<?= e(url('/admin/messages')) ?>">Ouvrir la messagerie</a>
        </div>
    </section>
</div>

<div class="grid grid--3">
    <section class="card card--sky">
        <div class="card__head"><h2 class="card__title"><span class="card__chip"><?= icon('target', 18) ?></span> Projets par domaine</h2></div>
        <div class="card__body">
            <ul class="bars">
                <?php foreach ($parDomaine as $k => $d): ?>
                    <li class="bars__row bars__row--c<?= $k % 5 ?>">
                        <span class="bars__label"><?= e($d['titre']) ?></span>
                        <span class="bars__value"><?= (int) $d['total'] ?></span>
                        <svg class="bars__track" viewBox="0 0 100 8" preserveAspectRatio="none" aria-hidden="true"><rect width="100" height="8" rx="4" class="bars__bg"/><rect width="<?= round((int) $d['total'] / $maxDomaine * 100, 1) ?>" height="8" rx="4" class="bars__fill"/></svg>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="card card--navy">
        <div class="card__head">
            <div><h2 class="card__title"><span class="card__chip"><?= icon('check', 18) ?></span> Qualité des contenus</h2><p class="card__sub">Éléments à compléter avant la mise en ligne</p></div>
        </div>
        <div class="card__body">
            <?php if ($qualite === []): ?>
                <?= View::partial('partials/admin/empty', ['icone' => 'check', 'titre' => 'Contenus complets', 'texte' => 'Toutes les images et informations clés sont renseignées.']) ?>
            <?php else: ?>
                <ul class="checklist">
                    <?php foreach ($qualite as $item): ?>
                        <li><a class="checklist__item" href="<?= e(url($item['lien'])) ?>"><span class="checklist__count"><?= (int) $item['n'] ?></span><span class="checklist__text"><?= e($item['libelle']) ?></span><?= icon('chevron', 16) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

    <section class="card card--green">
        <div class="card__head"><h2 class="card__title"><span class="card__chip"><?= icon('list', 18) ?></span> Activité récente</h2></div>
        <div class="card__body">
            <?php if ($journal === []): ?>
                <?= View::partial('partials/admin/empty', ['icone' => 'list', 'titre' => 'Aucune action enregistrée', 'texte' => 'Les créations et modifications apparaîtront ici.']) ?>
            <?php else: ?>
                <ol class="timeline">
                    <?php foreach ($journal as $j): ?>
                        <li class="timeline__item timeline__item--<?= e($j['action']) ?>">
                            <span class="timeline__text"><?= e($j['libelle']) ?></span>
                            <span class="timeline__meta"><?= e($j['admin_nom'] ?? 'Système') ?> · <?= e(ActivityLog::LIBELLES_TYPES[$j['type']] ?? $j['type']) ?> · <?= e(depuis((string) $j['cree_le'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>
</div>

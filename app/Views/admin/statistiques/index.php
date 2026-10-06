<?php

use App\Core\View;
use App\Models\Partenaire;
use App\Models\Projet;

$maxDomaine = max(1, ...array_map(static fn (array $d): int => (int) $d['total'], $parDomaine ?: [['total' => 1]]));
$maxPart = max(1, ...array_map(static fn (string $k): int => (int) $partenaires[$k], array_keys(Partenaire::CATEGORIES)));
$totalActivites12 = array_sum(array_column($activitesSerie, 'visites'));
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Statistiques</h1>
        <p class="page-sub">Fréquentation agrégée et anonyme du site, et état des contenus. Aucune donnée personnelle n'est collectée.</p>
    </div>
</div>

<div class="kpis">
    <div class="kpi kpi--blue">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('eye') ?></span> Visites · 30 jours</span>
        <span class="kpi__value"><?= number_format($visites30, 0, ',', ' ') ?></span>
        <span class="kpi__meta">Une visite par personne et par jour</span>
    </div>
    <div class="kpi kpi--green">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('file') ?></span> Pages vues · 30 jours</span>
        <span class="kpi__value"><?= number_format($pages30, 0, ',', ' ') ?></span>
        <span class="kpi__meta"><?= $visites30 > 0 ? number_format($pages30 / $visites30, 1, ',', ' ') . ' pages par visite' : 'Aucune donnée' ?></span>
    </div>
    <div class="kpi kpi--sky">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('calendar') ?></span> Visites · 12 mois</span>
        <span class="kpi__value"><?= number_format($visites12, 0, ',', ' ') ?></span>
        <span class="kpi__meta">Depuis <?= e(mb_strtolower($mois12[0]['titre'] ?? '')) ?></span>
    </div>
    <div class="kpi kpi--navy">
        <span class="kpi__head"><span class="kpi__icon"><?= icon('chart') ?></span> Meilleure journée</span>
        <span class="kpi__value"><?= $meilleurJour ? (int) $meilleurJour['visites'] : '—' ?></span>
        <span class="kpi__meta"><?= $meilleurJour ? e(date_fr((string) $meilleurJour['jour'])) : 'Aucune visite enregistrée' ?></span>
    </div>
</div>

<div class="grid grid--2">
    <section class="card card--blue">
        <div class="card__head"><div><h2 class="card__title">Visites des 30 derniers jours</h2><p class="card__sub">Survolez une barre pour voir le détail.</p></div></div>
        <div class="card__body">
            <?php if ($visites30 === 0): ?>
                <?= View::partial('partials/admin/empty', ['icone' => 'chart', 'titre' => 'Pas encore de visite enregistrée', 'texte' => 'Le compteur démarre dès la mise en ligne du site.']) ?>
            <?php else: ?>
                <?= View::partial('partials/admin/bar-chart', ['series' => $jours30, 'unite' => 'visites', 'description' => 'Visites quotidiennes sur 30 jours, total ' . $visites30, 'id' => 'j30']) ?>
            <?php endif; ?>
        </div>
    </section>
    <section class="card card--sky">
        <div class="card__head"><div><h2 class="card__title">Visites par mois</h2><p class="card__sub">12 derniers mois</p></div></div>
        <div class="card__body">
            <?php if ($visites12 === 0): ?>
                <?= View::partial('partials/admin/empty', ['icone' => 'chart', 'titre' => 'Pas encore de donnée mensuelle', 'texte' => 'Les mois apparaîtront au fil de la fréquentation.']) ?>
            <?php else: ?>
                <?= View::partial('partials/admin/bar-chart', ['series' => $mois12, 'unite' => 'visites', 'description' => 'Visites mensuelles sur 12 mois, total ' . $visites12, 'id' => 'm12']) ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="grid grid--3">
    <section class="card card--green">
        <div class="card__head"><div><h2 class="card__title">Activités publiées par mois</h2><p class="card__sub"><?= pluriel($totalActivites12, 'activité') ?> sur 12 mois</p></div></div>
        <div class="card__body">
            <?php if ($totalActivites12 === 0): ?>
                <?= View::partial('partials/admin/empty', ['icone' => 'calendar', 'titre' => 'Aucune activité datée sur la période', 'texte' => 'Les activités apparaîtront selon leur date.']) ?>
            <?php else: ?>
                <?= View::partial('partials/admin/bar-chart', ['series' => $activitesSerie, 'unite' => 'activités', 'description' => 'Activités par mois sur 12 mois', 'id' => 'act']) ?>
            <?php endif; ?>
        </div>
    </section>
    <section class="card card--sky">
        <div class="card__head"><h2 class="card__title">Projets par domaine</h2></div>
        <div class="card__body">
            <ul class="bars">
                <?php foreach ($parDomaine as $k => $d): ?>
                    <li class="bars__row bars__row--c<?= $k % 5 ?>">
                        <span class="bars__label"><?= e($d['titre']) ?></span><span class="bars__value"><?= (int) $d['total'] ?></span>
                        <svg class="bars__track" viewBox="0 0 100 8" preserveAspectRatio="none" aria-hidden="true"><rect width="100" height="8" rx="4" class="bars__bg"/><rect width="<?= round((int) $d['total'] / $maxDomaine * 100, 1) ?>" height="8" rx="4" class="bars__fill"/></svg>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <section class="card card--navy">
        <div class="card__head"><h2 class="card__title">Projets et partenaires</h2></div>
        <div class="card__body stack">
            <ul class="bars">
                <?php foreach (Projet::STATUTS + ['non_precise' => 'Statut non précisé'] as $cle => $libelle): ?>
                    <li class="bars__row">
                        <span class="bars__label">Projets : <?= e(mb_strtolower($libelle)) ?></span><span class="bars__value"><?= (int) $projets[$cle] ?></span>
                        <svg class="bars__track" viewBox="0 0 100 8" preserveAspectRatio="none" aria-hidden="true"><rect width="100" height="8" rx="4" class="bars__bg"/><rect width="<?= round((int) $projets[$cle] / max(1, $projets['tous']) * 100, 1) ?>" height="8" rx="4" class="bars__fill"/></svg>
                    </li>
                <?php endforeach; ?>
                <?php foreach (Partenaire::CATEGORIES_COURTES as $cle => $libelle): ?>
                    <li class="bars__row">
                        <span class="bars__label">Partenaires : <?= e(mb_strtolower($libelle)) ?></span><span class="bars__value"><?= (int) $partenaires[$cle] ?></span>
                        <svg class="bars__track" viewBox="0 0 100 8" preserveAspectRatio="none" aria-hidden="true"><rect width="100" height="8" rx="4" class="bars__bg"/><rect width="<?= round((int) $partenaires[$cle] / $maxPart * 100, 1) ?>" height="8" rx="4" class="bars__fill bars__fill--navy"/></svg>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</div>

<p class="note"><?= icon('shield', 18) ?> Mesure d'audience interne : aucune adresse IP, aucun cookie de suivi, aucun service tiers. Les robots d'indexation connus sont exclus du comptage.</p>

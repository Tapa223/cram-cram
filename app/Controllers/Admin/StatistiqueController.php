<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Activite;
use App\Models\Domaine;
use App\Models\Partenaire;
use App\Models\Projet;
use App\Services\Statistics;

final class StatistiqueController extends AdminController
{
    public function index(): void
    {
        $jours30 = Statistics::daily(30);
        $mois12 = Statistics::monthly(12);
        $activitesParMois = Activite::perMonth(12);
        $activitesSerie = array_map(static fn (array $m): array => [
            'label'   => $m['label'],
            'titre'   => $m['titre'],
            'visites' => $activitesParMois[$m['cle']] ?? 0,
        ], $mois12);

        $this->admin('statistiques/index', [
            'titrePage'      => 'Statistiques',
            'fil'            => ['Pilotage', 'Statistiques'],
            'jours30'        => $jours30,
            'mois12'         => $mois12,
            'visites30'      => Statistics::total($jours30),
            'pages30'        => Statistics::total($jours30, 'pages'),
            'visites12'      => Statistics::total($mois12),
            'meilleurJour'   => Database::first('SELECT jour, visites FROM visites_journalieres WHERE visites > 0 ORDER BY visites DESC, jour DESC LIMIT 1'),
            'activitesSerie' => $activitesSerie,
            'projets'        => Projet::countsByStatut(),
            'parDomaine'     => Domaine::projectsPerDomain(),
            'partenaires'    => Partenaire::countsByCategorie(),
        ]);
    }
}

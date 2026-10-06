<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Activite;
use App\Models\Domaine;
use App\Models\Message;
use App\Models\Partenaire;
use App\Models\Projet;
use App\Services\ActivityLog;
use App\Services\Statistics;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        $periode = in_array($_GET['periode'] ?? '', ['7j', '30j', '12m'], true) ? $_GET['periode'] : '30j';
        $series = match ($periode) {
            '7j'  => Statistics::daily(7),
            '12m' => Statistics::monthly(12),
            default => Statistics::daily(30),
        };

        $projets = Projet::countsByStatut();
        $activites = Activite::countsByStatut();

        $qualite = array_values(array_filter([
            ['n' => Projet::withoutImage(), 'libelle' => 'Projets sans image à la une', 'lien' => '/admin/projets'],
            ['n' => (int) Database::value('SELECT COUNT(*) FROM projets WHERE statut IS NULL'), 'libelle' => 'Projets sans statut renseigné', 'lien' => '/admin/projets?statut=non_precise'],
            ['n' => (int) Database::value('SELECT COUNT(*) FROM domaines_action WHERE image_id IS NULL'), 'libelle' => 'Domaines sans photo', 'lien' => '/admin/domaines'],
            ['n' => Partenaire::withoutLogo(), 'libelle' => 'Partenaires sans logo', 'lien' => '/admin/partenaires'],
            ['n' => (int) Database::value("SELECT COUNT(*) FROM pages WHERE contenu IS NULL OR contenu = ''"), 'libelle' => 'Pages sans contenu', 'lien' => '/admin/pages'],
        ], static fn (array $item): bool => $item['n'] > 0));

        $this->admin('dashboard/index', [
            'titrePage'        => 'Tableau de bord',
            'fil'              => ['Administration', 'Tableau de bord'],
            'periode'          => $periode,
            'series'           => $series,
            'totalVisites'     => Statistics::total($series),
            'projets'          => $projets,
            'projetsPublies'   => Projet::count('publie = 1'),
            'partenaires'      => Partenaire::countsByCategorie(),
            'activites'        => $activites,
            'nonLus'           => Message::unreadCount(),
            'derniersMessages' => Message::latestUnread(3),
            'projetsBrouillon' => Projet::count('publie = 0'),
            'parDomaine'       => Domaine::projectsPerDomain(),
            'qualite'          => $qualite,
            'journal'          => ActivityLog::latest(5),
        ]);
    }
}

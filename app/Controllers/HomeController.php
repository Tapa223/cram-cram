<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Activite;
use App\Models\Domaine;
use App\Models\Media;
use App\Models\Partenaire;
use App\Models\Projet;

final class HomeController extends Controller
{
    public function index(): void
    {
        $domaines = Domaine::published();
        $phare = null;
        foreach ($domaines as $i => $d) {
            if ((int) $d['mis_en_avant'] === 1) {
                $phare = $d;
                $phare['numero'] = $i + 1;
                break;
            }
        }

        $reperes = [];
        for ($n = 1; $n <= 5; $n++) {
            $valeur = setting('repere_' . $n . '_valeur');
            if ($valeur !== '') {
                $reperes[] = ['valeur' => $valeur, 'libelle' => setting('repere_' . $n . '_libelle')];
            }
        }

        $imageId = (int) setting('accueil_image_id');
        $imageAccueil = $imageId > 0 && Media::isImage($imageId) ? Media::find($imageId) : null;

        $this->render('home', [
            'imageAccueil' => $imageAccueil,
            'titrePage'   => null,
            'domaines'    => $domaines,
            'phare'       => $phare,
            'projets'     => Projet::published('', null, 6),
            'activites'   => Activite::latestPublished(3),
            'partenaires' => Partenaire::publishedByCategorie()['financier'],
            'reperes'     => $reperes,
            'consortium'  => Database::all("SELECT nom, sigle, pays FROM partenaires WHERE categorie = 'technique' AND publie = 1 AND description LIKE '%consortium%' ORDER BY ordre"),
        ]);
    }
}

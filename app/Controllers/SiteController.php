<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Activite;
use App\Models\Domaine;
use App\Models\Galerie;
use App\Models\Media;
use App\Models\Page;
use App\Models\Partenaire;
use App\Models\Projet;

/**
 * Pages publiques de consultation.
 */
final class SiteController extends Controller
{
    public function quiSommesNous(): void
    {
        $page = $this->orNotFound(Page::findBySlug('qui-sommes-nous'));
        $this->render('page', ['page' => $page, 'titrePage' => $page['titre'], 'metaDescription' => $page['meta_description'], 'variante' => 'qui']);
    }

    public function recherche(): void
    {
        $page = $this->orNotFound(Page::findBySlug('recherche'));
        $this->render('recherche', [
            'page'            => $page,
            'titrePage'       => $page['titre'],
            'metaDescription' => $page['meta_description'],
            'publications'    => Media::publications(),
        ]);
    }

    public function valeurAjoutee(): void
    {
        $page = $this->orNotFound(Page::findBySlug('valeur-ajoutee'));
        $this->render('valeur-ajoutee', ['page' => $page, 'titrePage' => $page['titre'], 'metaDescription' => $page['meta_description']]);
    }

    public function mentionsLegales(): void
    {
        $page = $this->orNotFound(Page::findBySlug('mentions-legales'));
        $this->render('page', ['page' => $page, 'titrePage' => $page['titre'], 'metaDescription' => $page['meta_description'], 'variante' => 'legal']);
    }

    public function domaines(): void
    {
        $this->render('domaines', [
            'titrePage'       => "Domaines d'action",
            'metaDescription' => "Les domaines d'intervention de CRAM-CRAM Mali : éducation à la paix, intégrité de l'information, genre, ressources naturelles, recherche-action.",
            'domaines'        => Domaine::published(),
        ]);
    }

    public function domaine(string $slug): void
    {
        $domaine = $this->orNotFound(Domaine::findPublishedBySlug($slug));
        $tous = Domaine::published();
        $this->render('domaine', [
            'titrePage'       => $domaine['titre'],
            'metaDescription' => $domaine['resume'],
            'domaine'         => $domaine,
            'galerie'         => Galerie::items('domaine', (int) $domaine['id']),
            'projets'         => Projet::published('', (int) $domaine['id']),
            'activites'       => Activite::latestPublished(3, (int) $domaine['id']),
            'autres'          => array_values(array_filter($tous, static fn (array $d): bool => (int) $d['id'] !== (int) $domaine['id'])),
            'numero'          => (int) array_search((int) $domaine['id'], array_map('intval', array_column($tous, 'id')), true) + 1,
        ]);
    }

    public function projets(): void
    {
        $statut = isset(Projet::STATUTS[$_GET['statut'] ?? '']) ? (string) $_GET['statut'] : '';
        $domaineSlug = (string) ($_GET['domaine'] ?? '');
        $domaines = Domaine::published();
        $domaineId = null;
        foreach ($domaines as $d) {
            if ($d['slug'] === $domaineSlug) {
                $domaineId = (int) $d['id'];
            }
        }
        $this->render('projets', [
            'titrePage'       => 'Projets et réalisations',
            'metaDescription' => 'Les projets menés par CRAM-CRAM Mali avec ses partenaires nationaux et internationaux.',
            'projets'         => Projet::published($statut, $domaineId),
            'statut'          => $statut,
            'domaineSlug'     => $domaineId !== null ? $domaineSlug : '',
            'domaines'        => $domaines,
            'statutsUtilises' => array_keys(array_filter(Projet::countsByStatut(), static fn (int $n, string $k): bool => $n > 0 && isset(Projet::STATUTS[$k]), ARRAY_FILTER_USE_BOTH)),
        ]);
    }

    public function projet(string $slug): void
    {
        $projet = $this->orNotFound(Projet::findPublishedBySlug($slug));
        $this->render('projet', [
            'titrePage'       => $projet['titre'],
            'metaDescription' => $projet['resume'],
            'projet'          => $projet,
            'galerie'         => Galerie::items('projet', (int) $projet['id']),
            'partenaires'     => Projet::partners((int) $projet['id']),
            'autres'          => Projet::others((int) $projet['id']),
        ]);
    }

    public function actualites(): void
    {
        $categorie = mb_substr(trim((string) ($_GET['categorie'] ?? '')), 0, 60);
        [$activites, $pager] = Activite::paginatePublished($categorie, $this->page());
        $this->render('actualites', [
            'titrePage'       => 'Actualités',
            'metaDescription' => 'Formations, rencontres, campagnes et activités de terrain de CRAM-CRAM Mali.',
            'activites'       => $activites,
            'pager'           => $pager,
            'categorie'       => $categorie,
            'categories'      => Activite::usedCategories(true),
        ]);
    }

    public function actualite(string $slug): void
    {
        $activite = $this->orNotFound(Activite::findPublishedBySlug($slug));
        $this->render('actualite', [
            'titrePage'       => $activite['titre'],
            'metaDescription' => $activite['resume'] ?: excerpt((string) $activite['contenu']),
            'activite'        => $activite,
            'galerie'         => Galerie::items('activite', (int) $activite['id']),
            'autres'          => Activite::latestPublished(3, null, (int) $activite['id']),
        ]);
    }

    public function partenaires(): void
    {
        $this->render('partenaires', [
            'titrePage'       => 'Partenaires et bailleurs',
            'metaDescription' => 'Les bailleurs et partenaires techniques, de recherche, nationaux et internationaux de CRAM-CRAM Mali.',
            'groupes'         => Partenaire::publishedByCategorie(),
        ]);
    }
}

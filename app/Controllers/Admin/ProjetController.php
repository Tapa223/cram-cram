<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Domaine;
use App\Models\Galerie;
use App\Models\Media;
use App\Models\Partenaire;
use App\Models\Projet;
use App\Services\ActivityLog;
use App\Services\Auth;
use App\Services\ContentSanitizer;

final class ProjetController extends AdminController
{
    private const BASE = '/admin/projets';

    public function index(): void
    {
        $filters = [
            'q'           => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100),
            'statut'      => (string) ($_GET['statut'] ?? ''),
            'domaine'     => ctype_digit((string) ($_GET['domaine'] ?? '')) ? (int) $_GET['domaine'] : null,
            'partenaire'  => ctype_digit((string) ($_GET['partenaire'] ?? '')) ? (int) $_GET['partenaire'] : null,
            'publication' => (string) ($_GET['publication'] ?? ''),
        ];
        [$projets, $pager] = Projet::paginateAdmin($filters, $this->page());

        $this->admin('projets/index', [
            'titrePage'   => 'Projets',
            'fil'         => ['Contenus', 'Projets'],
            'projets'     => $projets,
            'pager'       => $pager,
            'filtres'     => $filters,
            'compteurs'   => Projet::countsByStatut(),
            'domaines'    => Domaine::options(),
            'partenaires' => Partenaire::options(),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $projet = $this->orNotFound(Projet::findWithMedia((int) $id));
        $this->form($projet);
    }

    /** @param array<string, mixed>|null $projet */
    private function form(?array $projet): void
    {
        $this->admin('projets/form', [
            'titrePage'      => $projet ? 'Modifier le projet' : 'Nouveau projet',
            'fil'            => ['Projets', $projet ? 'Modifier' : 'Nouveau'],
            'projet'         => $projet,
            'domaines'       => Domaine::options(),
            'partenaires'    => Partenaire::options(),
            'partenairesSel' => $projet ? Projet::partnerIds((int) $projet['id']) : [],
            'images'         => Media::recentImages(),
            'galerie'        => $projet ? Galerie::items('projet', (int) $projet['id']) : [],
        ]);
    }

    public function store(): void
    {
        $input = $this->input();
        [$data, $partners] = $this->validated($input, null, self::BASE . '/nouveau');
        $data['slug'] = Projet::uniqueSlug($data['titre']);
        $data['auteur_id'] = Auth::id();
        $id = Projet::create($data);
        Projet::syncPartners($id, $partners);
        $this->syncGallery('projet', $id, $input);
        ActivityLog::record('creation', 'projet', $id, 'Projet « ' . $data['titre'] . ' » créé');
        $this->done('Le projet « ' . $data['titre'] . ' » a été créé.', self::BASE . '/' . $id . '/modifier');
    }

    public function update(string $id): void
    {
        $projet = $this->orNotFound(Projet::find((int) $id));
        $input = $this->input();
        $redirect = self::BASE . '/' . $projet['id'] . '/modifier';
        [$data, $partners] = $this->validated($input, $projet, $redirect);

        $slug = $this->str($input, 'slug');
        $data['slug'] = $slug !== '' ? Projet::uniqueSlug($slug, (int) $projet['id']) : $projet['slug'];

        Projet::update((int) $projet['id'], $data);
        Projet::syncPartners((int) $projet['id'], $partners);
        $this->syncGallery('projet', (int) $projet['id'], $input);
        ActivityLog::record('modification', 'projet', (int) $projet['id'], 'Projet « ' . $data['titre'] . ' » modifié');
        $this->done('Les modifications ont été enregistrées.', $redirect);
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed>|null $current
     * @return array{0: array<string, mixed>, 1: list<int>}
     */
    private function validated(array $input, ?array $current, string $redirect): array
    {
        $v = (new Validator($input))
            ->required('titre', 'Titre')->max('titre', 220, 'Titre')
            ->required('resume', 'Résumé')->max('resume', 400, 'Résumé')
            ->in('statut', array_keys(Projet::STATUTS), 'Statut', true)
            ->date('date_debut', 'Date de début')->date('date_fin', 'Date de fin')
            ->dateAfter('date_fin', 'date_debut', 'La date de fin doit être postérieure ou égale à la date de début.')
            ->max('zone', 200, 'Zone géographique');

        $slug = $this->str($input, 'slug');
        if ($slug !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $v->error('slug', "L'adresse ne peut contenir que des lettres minuscules sans accent, des chiffres et des tirets.");
        }

        $domaineId = $this->intOrNull($input, 'domaine_id');
        if ($domaineId !== null && Domaine::find($domaineId) === null) {
            $v->error('domaine_id', "Le domaine d'action choisi n'existe plus.");
        }

        if ($v->fails()) {
            $this->failValidation($v, $input, $redirect);
        }
        [$imageId, $imageError] = $this->resolveImage($input, $current ? ($current['image_id'] !== null ? (int) $current['image_id'] : null) : null, $this->str($input, 'titre'));
        $this->imageErrorOrFail($imageError, $v, $input, $redirect);

        return [[
            'titre'       => $this->str($input, 'titre'),
            'resume'      => $this->str($input, 'resume'),
            'description' => ContentSanitizer::clean($this->str($input, 'description')),
            'domaine_id'  => $domaineId,
            'statut'      => $this->nullable($input, 'statut'),
            'date_debut'  => $this->nullable($input, 'date_debut'),
            'date_fin'    => $this->nullable($input, 'date_fin'),
            'zone'        => $this->nullable($input, 'zone'),
            'image_id'    => $imageId,
            'publie'      => !empty($input['publie']) ? 1 : 0,
        ], $this->ids($input, 'partenaires')];
    }

    public function destroy(string $id): void
    {
        $projet = $this->orNotFound(Projet::find((int) $id));
        Projet::delete((int) $projet['id']);
        ActivityLog::record('suppression', 'projet', (int) $projet['id'], 'Projet « ' . $projet['titre'] . ' » supprimé');
        $this->done('Le projet « ' . $projet['titre'] . ' » a été supprimé.', $this->returnTo(self::BASE));
    }

    public function bulk(): void
    {
        $input = $this->input();
        $ids = $this->ids($input);
        $back = $this->returnTo(self::BASE);
        $this->requireSelection($ids, $back);

        $action = $this->str($input, 'action');
        $n = match ($action) {
            'publier'   => Projet::setPublished($ids, true),
            'depublier' => Projet::setPublished($ids, false),
            'supprimer' => Projet::deleteMany($ids),
            default     => -1,
        };
        if ($n < 0) {
            $this->done('Action inconnue.', $back);
        }
        $libelles = ['publier' => 'publié(s)', 'depublier' => 'retiré(s) du site', 'supprimer' => 'supprimé(s)'];
        ActivityLog::record($action === 'supprimer' ? 'suppression' : 'modification', 'projet', null, count($ids) . ' projet(s) ' . $libelles[$action]);
        $this->done(pluriel(count($ids), 'projet') . ' ' . $libelles[$action] . '.', $back);
    }
}

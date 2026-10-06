<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Activite;
use App\Models\Domaine;
use App\Models\Galerie;
use App\Models\Media;
use App\Services\ActivityLog;
use App\Services\Auth;
use App\Services\ContentSanitizer;

final class ActiviteController extends AdminController
{
    private const BASE = '/admin/activites';

    public function index(): void
    {
        $filters = [
            'q'         => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100),
            'statut'    => (string) ($_GET['statut'] ?? ''),
            'categorie' => mb_substr(trim((string) ($_GET['categorie'] ?? '')), 0, 60),
        ];
        [$activites, $pager] = Activite::paginateAdmin($filters, $this->page());

        $this->admin('activites/index', [
            'titrePage'  => 'Activités',
            'fil'        => ['Contenus', 'Activités'],
            'activites'  => $activites,
            'pager'      => $pager,
            'filtres'    => $filters,
            'compteurs'  => Activite::countsByStatut(),
            'categories' => Activite::usedCategories(),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->form($this->orNotFound(Activite::findWithMedia((int) $id)));
    }

    /** @param array<string, mixed>|null $activite */
    private function form(?array $activite): void
    {
        $this->admin('activites/form', [
            'titrePage'  => $activite ? "Modifier l'activité" : 'Nouvelle activité',
            'fil'        => ['Activités', $activite ? 'Modifier' : 'Nouvelle'],
            'activite'   => $activite,
            'domaines'   => Domaine::options(),
            'categories' => array_values(array_unique(array_merge(Activite::CATEGORIES, Activite::usedCategories()))),
            'images'     => Media::recentImages(),
            'galerie'    => $activite ? Galerie::items('activite', (int) $activite['id']) : [],
        ]);
    }

    public function store(): void
    {
        $input = $this->input();
        $data = $this->validated($input, null, self::BASE . '/nouvelle');
        $data['slug'] = Activite::uniqueSlug($data['titre']);
        $data['auteur_id'] = Auth::id();
        $id = Activite::create($data);
        $this->syncGallery('activite', $id, $input);
        ActivityLog::record('creation', 'activite', $id, 'Activité « ' . $data['titre'] . ' » créée');
        $message = $data['statut'] === 'publie' ? "L'activité a été créée et publiée." : "L'activité a été enregistrée en brouillon.";
        $this->done($message, self::BASE . '/' . $id . '/modifier');
    }

    public function update(string $id): void
    {
        $activite = $this->orNotFound(Activite::find((int) $id));
        $input = $this->input();
        $redirect = self::BASE . '/' . $activite['id'] . '/modifier';
        $data = $this->validated($input, $activite, $redirect);

        $slug = $this->str($input, 'slug');
        $data['slug'] = $slug !== '' ? Activite::uniqueSlug($slug, (int) $activite['id']) : $activite['slug'];

        Activite::update((int) $activite['id'], $data);
        $this->syncGallery('activite', (int) $activite['id'], $input);
        ActivityLog::record('modification', 'activite', (int) $activite['id'], 'Activité « ' . $data['titre'] . ' » modifiée');
        $this->done('Les modifications ont été enregistrées.', $redirect);
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed>|null $current
     * @return array<string, mixed>
     */
    private function validated(array $input, ?array $current, string $redirect): array
    {
        $v = (new Validator($input))
            ->required('titre', 'Titre')->max('titre', 220, 'Titre')
            ->required('categorie', 'Catégorie')->max('categorie', 60, 'Catégorie')
            ->max('resume', 400, 'Résumé')
            ->required('date_activite', 'Date')->date('date_activite', 'Date')
            ->max('lieu', 200, 'Lieu')
            ->in('statut', ['brouillon', 'publie'], 'Statut');

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

        [$imageId, $imageError] = $this->resolveImage($input, $current && $current['image_id'] !== null ? (int) $current['image_id'] : null, $this->str($input, 'titre'));
        $this->imageErrorOrFail($imageError, $v, $input, $redirect);

        return [
            'titre'         => $this->str($input, 'titre'),
            'categorie'     => $this->str($input, 'categorie'),
            'resume'        => $this->nullable($input, 'resume'),
            'contenu'       => ContentSanitizer::clean($this->str($input, 'contenu')),
            'date_activite' => $this->str($input, 'date_activite'),
            'lieu'          => $this->nullable($input, 'lieu'),
            'domaine_id'    => $domaineId,
            'image_id'      => $imageId,
            'statut'        => $this->str($input, 'statut'),
        ];
    }

    public function destroy(string $id): void
    {
        $activite = $this->orNotFound(Activite::find((int) $id));
        Activite::delete((int) $activite['id']);
        ActivityLog::record('suppression', 'activite', (int) $activite['id'], 'Activité « ' . $activite['titre'] . ' » supprimée');
        $this->done("L'activité « " . $activite['titre'] . ' » a été supprimée.', $this->returnTo(self::BASE));
    }

    public function bulk(): void
    {
        $input = $this->input();
        $ids = $this->ids($input);
        $back = $this->returnTo(self::BASE);
        $this->requireSelection($ids, $back);

        $action = $this->str($input, 'action');
        $n = match ($action) {
            'publier'   => Activite::setStatut($ids, 'publie'),
            'depublier' => Activite::setStatut($ids, 'brouillon'),
            'supprimer' => Activite::deleteMany($ids),
            default     => -1,
        };
        if ($n < 0) {
            $this->done('Action inconnue.', $back);
        }
        $libelles = ['publier' => 'publiée(s)', 'depublier' => 'repassée(s) en brouillon', 'supprimer' => 'supprimée(s)'];
        ActivityLog::record($action === 'supprimer' ? 'suppression' : 'modification', 'activite', null, count($ids) . ' activité(s) ' . $libelles[$action]);
        $this->done(pluriel(count($ids), 'activité') . ' ' . $libelles[$action] . '.', $back);
    }
}

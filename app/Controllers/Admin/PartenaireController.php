<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Media;
use App\Models\Partenaire;
use App\Services\ActivityLog;

final class PartenaireController extends AdminController
{
    private const BASE = '/admin/partenaires';

    public function index(): void
    {
        $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $categorie = (string) ($_GET['categorie'] ?? '');
        $publication = (string) ($_GET['publication'] ?? '');
        $this->admin('partenaires/index', [
            'titrePage'   => 'Partenaires',
            'fil'         => ['Contenus', 'Partenaires'],
            'partenaires' => Partenaire::allForAdmin($q, $categorie, $publication),
            'compteurs'   => Partenaire::countsByCategorie(),
            'q'           => $q,
            'categorie'   => isset(Partenaire::CATEGORIES[$categorie]) ? $categorie : '',
            'publication' => $publication,
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->form($this->orNotFound(Partenaire::findWithLogo((int) $id)));
    }

    /** @param array<string, mixed>|null $partenaire */
    private function form(?array $partenaire): void
    {
        $this->admin('partenaires/form', [
            'titrePage'  => $partenaire ? 'Modifier le partenaire' : 'Nouveau partenaire',
            'fil'        => ['Partenaires', $partenaire ? 'Modifier' : 'Nouveau'],
            'partenaire' => $partenaire,
            'images'     => Media::recentImages(),
        ]);
    }

    public function store(): void
    {
        $input = $this->input();
        $data = $this->validated($input, null, self::BASE . '/nouveau');
        $data['ordre'] = Partenaire::nextOrder($data['categorie']);
        $id = Partenaire::create($data);
        ActivityLog::record('creation', 'partenaire', $id, 'Partenaire « ' . $data['nom'] . ' » ajouté');
        $this->done('Le partenaire « ' . $data['nom'] . ' » a été ajouté.', self::BASE . '/' . $id . '/modifier');
    }

    public function update(string $id): void
    {
        $partenaire = $this->orNotFound(Partenaire::find((int) $id));
        $input = $this->input();
        $redirect = self::BASE . '/' . $partenaire['id'] . '/modifier';
        $data = $this->validated($input, $partenaire, $redirect);
        Partenaire::update((int) $partenaire['id'], $data);
        ActivityLog::record('modification', 'partenaire', (int) $partenaire['id'], 'Partenaire « ' . $data['nom'] . ' » modifié');
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
            ->required('nom', 'Nom')->max('nom', 200, 'Nom')
            ->max('sigle', 60, 'Sigle')
            ->required('categorie', 'Catégorie')->in('categorie', array_keys(Partenaire::CATEGORIES), 'Catégorie')
            ->max('description', 400, 'Description')
            ->max('pays', 80, 'Pays')
            ->url('site_web', 'Site web')->max('site_web', 255, 'Site web');

        $nom = $this->str($input, 'nom');
        if ($nom !== '' && Partenaire::nameTaken($nom, $current ? (int) $current['id'] : null)) {
            $v->error('nom', 'Un partenaire porte déjà ce nom.');
        }
        if ($v->fails()) {
            $this->failValidation($v, $input, $redirect);
        }
        [$logoId, $imageError] = $this->resolveImage($input, $current && $current['logo_id'] !== null ? (int) $current['logo_id'] : null, 'Logo ' . $nom);
        $this->imageErrorOrFail($imageError, $v, $input, $redirect);

        return [
            'nom'         => $nom,
            'sigle'       => $this->nullable($input, 'sigle'),
            'categorie'   => $this->str($input, 'categorie'),
            'description' => $this->nullable($input, 'description'),
            'pays'        => $this->nullable($input, 'pays'),
            'site_web'    => $this->nullable($input, 'site_web'),
            'logo_id'     => $logoId,
            'publie'      => !empty($input['publie']) ? 1 : 0,
        ];
    }

    public function destroy(string $id): void
    {
        $partenaire = $this->orNotFound(Partenaire::find((int) $id));
        Partenaire::delete((int) $partenaire['id']);
        ActivityLog::record('suppression', 'partenaire', (int) $partenaire['id'], 'Partenaire « ' . $partenaire['nom'] . ' » supprimé');
        $this->done('Le partenaire « ' . $partenaire['nom'] . ' » a été supprimé.', $this->returnTo(self::BASE));
    }

    public function bulk(): void
    {
        $input = $this->input();
        $ids = $this->ids($input);
        $back = $this->returnTo(self::BASE);
        $this->requireSelection($ids, $back);
        $action = $this->str($input, 'action');
        $n = match ($action) {
            'publier'   => Partenaire::setPublished($ids, true),
            'depublier' => Partenaire::setPublished($ids, false),
            'supprimer' => Partenaire::deleteMany($ids),
            default     => -1,
        };
        if ($n < 0) {
            $this->done('Action inconnue.', $back);
        }
        $libelles = ['publier' => 'affiché(s) sur le site', 'depublier' => 'masqué(s) du site', 'supprimer' => 'supprimé(s)'];
        ActivityLog::record($action === 'supprimer' ? 'suppression' : 'modification', 'partenaire', null, count($ids) . ' partenaire(s) ' . $libelles[$action]);
        $this->done(pluriel(count($ids), 'partenaire') . ' ' . $libelles[$action] . '.', $back);
    }
}

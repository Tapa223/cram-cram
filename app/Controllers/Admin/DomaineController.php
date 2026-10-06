<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Validator;
use App\Models\Domaine;
use App\Models\Galerie;
use App\Models\Media;
use App\Services\ActivityLog;
use App\Services\ContentSanitizer;

final class DomaineController extends AdminController
{
    private const BASE = '/admin/domaines';

    public function index(): void
    {
        $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $this->admin('domaines/index', [
            'titrePage' => "Domaines d'action",
            'fil'       => ['Contenus', "Domaines d'action"],
            'domaines'  => Domaine::allForAdmin($q),
            'q'         => $q,
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $domaine = $this->orNotFound(Database::first(
            'SELECT d.*, m.fichier AS image_fichier, m.texte_alt AS image_alt FROM domaines_action d LEFT JOIN medias m ON m.id = d.image_id WHERE d.id = ?',
            [(int) $id]
        ));
        $this->form($domaine);
    }

    /** @param array<string, mixed>|null $domaine */
    private function form(?array $domaine): void
    {
        $this->admin('domaines/form', [
            'titrePage' => $domaine ? 'Modifier le domaine' : "Nouveau domaine d'action",
            'fil'       => ["Domaines d'action", $domaine ? 'Modifier' : 'Nouveau'],
            'domaine'   => $domaine,
            'images'    => Media::recentImages(),
            'galerie'   => $domaine ? Galerie::items('domaine', (int) $domaine['id']) : [],
        ]);
    }

    public function store(): void
    {
        $input = $this->input();
        $data = $this->validated($input, null, self::BASE . '/nouveau');
        $data['slug'] = Domaine::uniqueSlug($data['titre']);
        $data['ordre'] = Domaine::nextOrder();
        $id = Domaine::create($data);
        $this->exclusiveFeatured($id, (bool) $data['mis_en_avant']);
        $this->syncGallery('domaine', $id, $input);
        ActivityLog::record('creation', 'domaine', $id, 'Domaine « ' . $data['titre'] . ' » créé');
        $this->done("Le domaine d'action a été créé.", self::BASE . '/' . $id . '/modifier');
    }

    public function update(string $id): void
    {
        $domaine = $this->orNotFound(Domaine::find((int) $id));
        $input = $this->input();
        $redirect = self::BASE . '/' . $domaine['id'] . '/modifier';
        $data = $this->validated($input, $domaine, $redirect);
        $slug = $this->str($input, 'slug');
        $data['slug'] = $slug !== '' ? Domaine::uniqueSlug($slug, (int) $domaine['id']) : $domaine['slug'];
        Domaine::update((int) $domaine['id'], $data);
        $this->exclusiveFeatured((int) $domaine['id'], (bool) $data['mis_en_avant']);
        $this->syncGallery('domaine', (int) $domaine['id'], $input);
        ActivityLog::record('modification', 'domaine', (int) $domaine['id'], 'Domaine « ' . $data['titre'] . ' » modifié');
        $this->done('Les modifications ont été enregistrées.', $redirect);
    }

    /** Un seul domaine « phare » mis en avant sur l'accueil. */
    private function exclusiveFeatured(int $id, bool $featured): void
    {
        if ($featured) {
            Database::query('UPDATE domaines_action SET mis_en_avant = 0 WHERE id <> ?', [$id]);
        }
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed>|null $current
     * @return array<string, mixed>
     */
    private function validated(array $input, ?array $current, string $redirect): array
    {
        $v = (new Validator($input))
            ->required('titre', 'Titre')->max('titre', 180, 'Titre')
            ->required('resume', 'Résumé')->max('resume', 400, 'Résumé');
        $slug = $this->str($input, 'slug');
        if ($slug !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $v->error('slug', "L'adresse ne peut contenir que des lettres minuscules sans accent, des chiffres et des tirets.");
        }
        if ($v->fails()) {
            $this->failValidation($v, $input, $redirect);
        }
        [$imageId, $imageError] = $this->resolveImage($input, $current && $current['image_id'] !== null ? (int) $current['image_id'] : null, $this->str($input, 'titre'));
        $this->imageErrorOrFail($imageError, $v, $input, $redirect);

        return [
            'titre'        => $this->str($input, 'titre'),
            'resume'       => $this->str($input, 'resume'),
            'description'  => ContentSanitizer::clean($this->str($input, 'description')),
            'image_id'     => $imageId,
            'mis_en_avant' => !empty($input['mis_en_avant']) ? 1 : 0,
            'publie'       => !empty($input['publie']) ? 1 : 0,
        ];
    }

    public function destroy(string $id): void
    {
        $domaine = $this->orNotFound(Domaine::find((int) $id));
        Domaine::delete((int) $domaine['id']);
        ActivityLog::record('suppression', 'domaine', (int) $domaine['id'], 'Domaine « ' . $domaine['titre'] . ' » supprimé');
        $this->done('Le domaine « ' . $domaine['titre'] . ' » a été supprimé. Les projets et activités liés sont conservés, sans domaine.', self::BASE);
    }

    public function bulk(): void
    {
        $input = $this->input();
        $ids = $this->ids($input);
        $this->requireSelection($ids, self::BASE);
        $action = $this->str($input, 'action');
        if (!in_array($action, ['publier', 'depublier'], true)) {
            $this->done('Action inconnue.', self::BASE);
        }
        Domaine::setPublished($ids, $action === 'publier');
        ActivityLog::record('modification', 'domaine', null, count($ids) . ' domaine(s) ' . ($action === 'publier' ? 'publié(s)' : 'masqué(s)'));
        $this->done(pluriel(count($ids), 'domaine') . ($action === 'publier' ? ' visible(s) sur le site.' : ' masqué(s) du site.'), self::BASE);
    }

    /** Déplace un domaine d'une position vers le haut ou le bas. */
    public function reorder(): void
    {
        $input = $this->input();
        $id = $this->intOrNull($input, 'id');
        $direction = $this->str($input, 'direction');
        $rows = Database::all('SELECT id FROM domaines_action ORDER BY ordre, titre');
        $ids = array_map('intval', array_column($rows, 'id'));
        $pos = $id !== null ? array_search($id, $ids, true) : false;
        if ($pos !== false) {
            $swap = $direction === 'haut' ? $pos - 1 : $pos + 1;
            if (isset($ids[$swap])) {
                [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
            }
            Database::transaction(static function () use ($ids): void {
                foreach ($ids as $i => $domaineId) {
                    Database::query('UPDATE domaines_action SET ordre = ? WHERE id = ?', [$i + 1, $domaineId]);
                }
            });
        }
        $this->done("L'ordre d'affichage a été mis à jour.", self::BASE);
    }
}

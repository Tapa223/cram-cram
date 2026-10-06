<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Response;
use App\Core\Session;
use App\Models\Media;
use App\Services\ActivityLog;
use App\Services\MediaUploader;

final class MediaController extends AdminController
{
    private const BASE = '/admin/medias';

    public function index(): void
    {
        $type = in_array($_GET['type'] ?? '', ['images', 'documents'], true) ? (string) $_GET['type'] : '';
        $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        [$medias, $pager] = Media::paginate($type, $q, $this->page());

        $this->admin('medias/index', [
            'titrePage' => 'Médiathèque',
            'fil'       => ['Contenus', 'Médias'],
            'medias'    => $medias,
            'pager'     => $pager,
            'type'      => $type,
            'q'         => $q,
            'volume'    => MediaUploader::humanSize(Media::totalSize()),
        ]);
    }

    public function store(): void
    {
        $files = $_FILES['fichiers'] ?? null;
        if (!is_array($files) || !is_array($files['name'] ?? null)) {
            Session::flash('erreur', 'Choisissez au moins un fichier à téléverser.');
            Response::redirect(self::BASE);
        }

        $publication = !empty($_POST['est_publication']);
        $ok = 0;
        $errors = [];
        foreach ($files['name'] as $i => $name) {
            if ((int) $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $file = [
                'name'     => $name,
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            $result = MediaUploader::store($file, true, '', '', $publication);
            $result['ok'] ? $ok++ : $errors[] = $result['message'];
        }

        if ($ok === 0 && $errors === []) {
            Session::flash('erreur', 'Choisissez au moins un fichier à téléverser.');
        }
        if ($ok > 0) {
            Session::flash('succes', pluriel($ok, 'fichier') . ' ajouté(s) à la médiathèque.');
        }
        foreach ($errors as $error) {
            Session::flash('erreur', $error);
        }
        Response::redirect(self::BASE);
    }

    public function update(string $id): void
    {
        $media = $this->orNotFound(Media::find((int) $id));
        $input = $this->input();
        Media::updateMeta(
            (int) $media['id'],
            mb_substr($this->str($input, 'texte_alt'), 0, 255),
            mb_substr($this->str($input, 'titre'), 0, 200),
            !empty($input['est_publication'])
        );
        ActivityLog::record('modification', 'media', (int) $media['id'], 'Média « ' . $media['nom_original'] . ' » modifié');
        $this->done('Les informations du fichier ont été enregistrées.', $this->returnTo(self::BASE));
    }

    public function destroy(string $id): void
    {
        $media = $this->orNotFound(Media::find((int) $id));
        Media::delete((int) $media['id']);
        MediaUploader::delete($media);
        ActivityLog::record('suppression', 'media', (int) $media['id'], 'Média « ' . $media['nom_original'] . ' » supprimé');
        $this->done('Le fichier « ' . $media['nom_original'] . ' » a été supprimé. Les contenus qui l\'utilisaient n\'affichent plus d\'image.', $this->returnTo(self::BASE));
    }
}

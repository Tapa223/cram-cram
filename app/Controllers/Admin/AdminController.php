<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Galerie;
use App\Models\Media;
use App\Services\MediaUploader;

/**
 * Base des contrôleurs d'administration.
 */
abstract class AdminController extends Controller
{
    /**
     * Détermine l'image associée à un contenu à partir du formulaire :
     * nouveau fichier téléversé, image choisie dans la médiathèque, ou retrait.
     * Retourne [identifiant du média ou null, message d'erreur éventuel].
     *
     * @param array<string, mixed> $input
     * @return array{0: ?int, 1: ?string}
     */
    protected function resolveImage(array $input, ?int $current, string $alt, string $fileField = 'image_fichier', string $idField = 'image_id'): array
    {
        $file = $_FILES[$fileField] ?? null;
        if (is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $result = MediaUploader::store($file, false, $alt);
            return $result['ok'] ? [(int) $result['id'], null] : [$current, $result['message']];
        }
        if (!empty($input['retirer_image'])) {
            return [null, null];
        }
        $chosen = $this->intOrNull($input, $idField);
        if ($chosen !== null && Media::isImage($chosen)) {
            return [$chosen, null];
        }
        return [$current, null];
    }

    /**
     * Met à jour la galerie photos d'un contenu : retraits cochés, images choisies
     * dans la médiathèque et nouveaux fichiers (plusieurs à la fois).
     * Une photo refusée n'empêche pas l'enregistrement du reste : le motif est signalé.
     *
     * @param array<string, mixed> $input
     */
    protected function syncGallery(string $type, int $ownerId, array $input): void
    {
        Galerie::remove($type, $ownerId, $this->ids($input, 'galerie_retirer'));

        $toAdd = array_values(array_filter($this->ids($input, 'galerie_ids'), static fn (int $id): bool => Media::isImage($id)));

        $errors = [];
        $files = $_FILES['galerie_fichiers'] ?? null;
        if (is_array($files) && is_array($files['name'] ?? null)) {
            foreach ($files['name'] as $i => $name) {
                if ((int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $result = MediaUploader::store([
                    'name'     => $name,
                    'type'     => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size'     => $files['size'][$i] ?? 0,
                ]);
                $result['ok'] ? $toAdd[] = (int) $result['id'] : $errors[] = $result['message'];
            }
        }
        Galerie::add($type, $ownerId, $toAdd);

        if ($errors !== []) {
            Session::flash('erreur', 'Certaines photos de la galerie n\'ont pas été ajoutées : ' . implode(' ', $errors));
        }
    }

    /** Message de succès puis retour à une adresse d'administration. */
    protected function done(string $message, string $redirect): never
    {
        Session::flash('succes', $message);
        Response::redirect($redirect);
    }

    /** Refuse une action groupée sans sélection. */
    protected function requireSelection(array $ids, string $redirect): void
    {
        if ($ids === []) {
            Session::flash('info', 'Sélectionnez au moins un élément avant de lancer une action groupée.');
            Response::redirect($redirect);
        }
    }

    /** @param array<string, mixed> $input */
    protected function imageErrorOrFail(?string $imageError, Validator $v, array $input, string $redirect): void
    {
        if ($imageError !== null) {
            $v->error('image_fichier', $imageError);
        }
        if ($v->fails()) {
            $this->failValidation($v, $input, $redirect);
        }
    }

    /** Liste des filtres de la requête courante, pour conserver le contexte après une action. */
    protected function returnTo(string $base): string
    {
        $back = Request::isPost() ? (string) ($_POST['_retour'] ?? '') : '';
        if (preg_match('/^\?[A-Za-z0-9_=&%.+\-]{0,300}$/', $back) === 1) {
            return $base . $back;
        }
        return $base;
    }
}

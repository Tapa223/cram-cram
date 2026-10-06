<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Téléversement sécurisé :
 * - type réel vérifié par le contenu du fichier (finfo), jamais par l'extension déclarée ;
 * - images réencodées par GD (suppression des métadonnées et de tout contenu parasite),
 *   redimensionnées à 2000 px maximum ;
 * - PDF vérifiés par leur signature ;
 * - nom de fichier aléatoire, rangement par année/mois dans public/uploads.
 */
final class MediaUploader
{
    public const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    public const DOCUMENT_TYPES = ['application/pdf' => 'pdf'];
    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
    public const MAX_DOCUMENT_BYTES = 10 * 1024 * 1024;
    private const MAX_DIMENSION = 2000;

    /**
     * @param array<string, mixed> $file Élément de $_FILES
     * @return array{ok: bool, id?: int, message: string}
     */
    public static function store(array $file, bool $allowDocuments = false, string $alt = '', string $title = '', bool $publication = false): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => self::uploadErrorMessage($error)];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'message' => "Le fichier n'a pas pu être reçu. Merci de réessayer."];
        }

        $originalName = self::cleanName((string) ($file['name'] ?? 'fichier'));
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        $size = (int) filesize($tmp);

        $isImage = isset(self::IMAGE_TYPES[$mime]);
        $isDocument = $allowDocuments && isset(self::DOCUMENT_TYPES[$mime]);

        if (!$isImage && !$isDocument) {
            $formats = $allowDocuments ? 'JPEG, PNG, WEBP ou PDF' : 'JPEG, PNG ou WEBP';
            return ['ok' => false, 'message' => "Le fichier « {$originalName} » n'a pas été accepté. Formats autorisés : {$formats}."];
        }
        if ($isImage && $size > self::MAX_IMAGE_BYTES) {
            return ['ok' => false, 'message' => "L'image « {$originalName} » dépasse 5 Mo. Réduisez-la avant de la téléverser."];
        }
        if ($isDocument && $size > self::MAX_DOCUMENT_BYTES) {
            return ['ok' => false, 'message' => "Le document « {$originalName} » dépasse 10 Mo."];
        }

        $width = null;
        $height = null;
        if ($isImage) {
            $info = @getimagesize($tmp);
            if ($info === false || $info[0] < 1 || $info[1] < 1) {
                return ['ok' => false, 'message' => "Le fichier « {$originalName} » n'est pas une image valide."];
            }
            [$width, $height] = $info;
        } else {
            $handle = fopen($tmp, 'rb');
            $signature = $handle ? (string) fread($handle, 5) : '';
            if ($handle) {
                fclose($handle);
            }
            if ($signature !== '%PDF-') {
                return ['ok' => false, 'message' => "Le fichier « {$originalName} » n'est pas un PDF valide."];
            }
        }

        $ext = $isImage ? self::IMAGE_TYPES[$mime] : self::DOCUMENT_TYPES[$mime];
        $relativeDir = date('Y') . '/' . date('m');
        $absoluteDir = APP_ROOT . '/public/uploads/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            return ['ok' => false, 'message' => "Le dossier de téléversement n'est pas accessible en écriture."];
        }

        $relativePath = $relativeDir . '/' . bin2hex(random_bytes(12)) . '.' . $ext;
        $destination = APP_ROOT . '/public/uploads/' . $relativePath;

        $saved = false;
        if ($isImage && extension_loaded('gd')) {
            $result = self::reencode($tmp, $mime, $destination);
            if ($result !== null) {
                [$width, $height] = $result;
                $saved = true;
            }
        }
        if (!$saved && !move_uploaded_file($tmp, $destination)) {
            return ['ok' => false, 'message' => "Le fichier n'a pas pu être enregistré sur le serveur."];
        }
        @chmod($destination, 0644);

        Database::query(
            'INSERT INTO medias (fichier, nom_original, type_mime, taille, largeur, hauteur, titre, texte_alt, est_publication, televerse_par)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $relativePath,
                $originalName,
                $mime,
                (int) filesize($destination),
                $width,
                $height,
                $title !== '' ? mb_substr($title, 0, 200) : null,
                $alt !== '' ? mb_substr($alt, 0, 255) : null,
                $isDocument && $publication ? 1 : 0,
                Auth::id(),
            ]
        );
        $id = Database::lastId();
        ActivityLog::record('creation', 'media', $id, 'Média « ' . $originalName . ' » ajouté');

        return ['ok' => true, 'id' => $id, 'message' => 'Fichier téléversé.'];
    }

    /** @return array{0: int, 1: int}|null */
    private static function reencode(string $source, string $mime, string $destination): ?array
    {
        $image = match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($source) : false,
            'image/png'  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($source) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default      => false,
        };
        if ($image === false) {
            return null;
        }

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($source);
            $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
            $rotated = match ($orientation) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };
            if ($rotated !== false) {
                $image = $rotated;
            }
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min(1, self::MAX_DIMENSION / max($width, $height));
        if ($ratio < 1) {
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            if ($mime !== 'image/jpeg') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $resized;
            $width = $newWidth;
            $height = $newHeight;
        } elseif ($mime !== 'image/jpeg') {
            imagesavealpha($image, true);
        }

        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($image, $destination, 82),
            'image/png'  => imagepng($image, $destination, 7),
            'image/webp' => function_exists('imagewebp') && imagewebp($image, $destination, 82),
            default      => false,
        };
        return $ok ? [$width, $height] : null;
    }

    public static function delete(array $media): void
    {
        $path = realpath(APP_ROOT . '/public/uploads/' . $media['fichier']);
        $uploads = realpath(APP_ROOT . '/public/uploads');
        if ($path !== false && $uploads !== false && str_starts_with($path, $uploads . DIRECTORY_SEPARATOR) && is_file($path)) {
            @unlink($path);
        }
    }

    private static function cleanName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[^\p{L}\p{N}\s._()-]/u', '', $name) ?? 'fichier';
        return mb_substr(trim($name) !== '' ? trim($name) : 'fichier', 0, 200);
    }

    public static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux pour le serveur.',
            UPLOAD_ERR_PARTIAL => "Le fichier n'a été reçu que partiellement. Merci de réessayer.",
            UPLOAD_ERR_NO_FILE => 'Aucun fichier sélectionné.',
            default => "Le téléversement a échoué. Merci de réessayer.",
        };
    }

    public static function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', ' ') . ' Mo';
        }
        return max(1, (int) round($bytes / 1024)) . ' Ko';
    }
}

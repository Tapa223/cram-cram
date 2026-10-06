<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Services\Settings;

/** Échappement HTML de toute donnée affichée. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    $base = Request::basePath();
    $path = '/' . ltrim($path, '/');
    return ($base === '' ? '' : $base) . ($path === '/' && $base !== '' ? '/' : $path);
}

function asset(string $path): string
{
    $file = APP_ROOT . '/public/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return url('/assets/' . ltrim($path, '/')) . '?v=' . $version;
}

function upload_url(?string $relativePath): string
{
    return $relativePath ? url('/uploads/' . ltrim($relativePath, '/')) : '';
}

function csrf_field(): string
{
    return Csrf::field();
}

function old(string $key, mixed $default = ''): mixed
{
    return Session::hasOld() ? Session::old($key, $default) : $default;
}

/** Case à cocher : après une erreur, reflète la saisie (absente = décochée). */
function old_bool(string $key, bool $default): bool
{
    return Session::hasOld() ? !empty(Session::old($key)) : $default;
}

/** @param list<int> $default @return list<int> Valeurs multiples (cases à cocher) après une erreur. */
function old_ids(string $key, array $default): array
{
    if (!Session::hasOld()) {
        return $default;
    }
    $values = Session::old($key, []);
    return is_array($values) ? array_values(array_map('intval', array_filter($values, 'is_numeric'))) : [];
}

function field_error(string $key): ?string
{
    return Session::errors()[$key] ?? null;
}

function setting(string $key, string $default = ''): string
{
    return Settings::get($key, $default);
}

function is_safe_url(string $url): bool
{
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}

function slugify(string $text): string
{
    $text = trim($text);
    if (class_exists(\Transliterator::class)) {
        $tr = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');
        $text = $tr ? (string) $tr->transliterate($text) : strtolower($text);
    } else {
        $text = strtolower((string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text));
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? substr($text, 0, 180) : 'element';
}

function excerpt(?string $html, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(preg_replace('#<(/p|br|/li|/h[1-6])\b[^>]*>#i', '$0 ', (string) $html) ?? ''), ENT_QUOTES, 'UTF-8')) ?? '');
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $cut = mb_substr($text, 0, $length);
    $space = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $space !== false ? $space : $length), ' ,;:.') . '…';
}

const MOIS_FR = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
const MOIS_COURTS_FR = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
const JOURS_FR = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

function date_fr(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return '';
    }
    $out = (int) date('j', $ts) . ' ' . MOIS_FR[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
    return $withTime ? $out . ' à ' . date('H:i', $ts) : $out;
}

function date_court_fr(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts === false ? '' : (int) date('j', $ts) . ' ' . MOIS_COURTS_FR[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

function jour_fr(): string
{
    $ts = time();
    return ucfirst(JOURS_FR[(int) date('w', $ts)]) . ' ' . date_fr(date('Y-m-d', $ts));
}

/** Durée relative lisible : « il y a 5 min », « hier », sinon la date. */
function depuis(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date . (str_contains($date, 'T') ? '' : ' UTC'));
    if ($ts === false) {
        return '';
    }
    $diff = time() - $ts;
    if ($diff < 60) {
        return "à l'instant";
    }
    if ($diff < 3600) {
        return 'il y a ' . intdiv($diff, 60) . ' min';
    }
    if ($diff < 86400) {
        return 'il y a ' . intdiv($diff, 3600) . ' h';
    }
    if ($diff < 172800) {
        return 'hier';
    }
    return date_court_fr(date('Y-m-d', $ts));
}

function periode_projet(?string $debut, ?string $fin): string
{
    $d = $debut ? date('Y', (int) strtotime($debut)) : '';
    $f = $fin ? date('Y', (int) strtotime($fin)) : '';
    if ($d && $f) {
        return $d === $f ? $d : $d . ' – ' . $f;
    }
    return $d ?: $f;
}

function pluriel(int $n, string $singulier, ?string $pluriel = null): string
{
    return $n . ' ' . ($n > 1 ? ($pluriel ?? $singulier . 's') : $singulier);
}

/** Classe CSS « actif » pour la navigation. */
function nav_active(string $prefix, bool $exact = false): string
{
    $path = Request::path();
    $match = $exact ? $path === $prefix : ($path === $prefix || str_starts_with($path, rtrim($prefix, '/') . '/'));
    return $match ? ' is-active' : '';
}

function aria_current(string $prefix, bool $exact = false): string
{
    return nav_active($prefix, $exact) !== '' ? ' aria-current="page"' : '';
}

/**
 * Icônes SVG (trait) centralisées. Décoratives par défaut (aria-hidden).
 */
function icon(string $name, int $size = 20): string
{
    static $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'chart'     => '<path d="M4 20V11M10 20V5M16 20v-6M21 20H3"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/>',
        'folder'    => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'target'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
        'file'      => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
        'image'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'sliders'   => '<path d="M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1"/><circle cx="15" cy="6" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="17" cy="18" r="2"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'logout'    => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l-5-5 5-5M5 12h11"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'bell'      => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 20a2 2 0 0 0 4 0"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'edit'      => '<path d="M4 20h4L19 9l-4-4L4 16z"/>',
        'trash'     => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
        'eye'       => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'external'  => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'back'      => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'chevron'   => '<path d="m9 6 6 6-6 6"/>',
        'down'      => '<path d="m6 9 6 6 6-6"/>',
        'check'     => '<path d="m5 12 5 5 9-10"/>',
        'x'         => '<path d="M6 6l12 12M18 6 6 18"/>',
        'upload'    => '<path d="M12 16V4M7 9l5-5 5 5M4 20h16"/>',
        'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'alert'     => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>',
        'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'shield'    => '<path d="M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'pin'       => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone'     => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
        'globe'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'book'      => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 19V5M19 19v2H6"/>',
        'download'  => '<path d="M12 4v12M7 11l5 5 5-5M4 20h16"/>',
        'link'      => '<path d="M10 14a4 4 0 0 0 6 0l3-3a4 4 0 0 0-6-6l-1 1M14 10a4 4 0 0 0-6 0l-3 3a4 4 0 0 0 6 6l1-1"/>',
        'list'      => '<path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01"/>',
        'olist'     => '<path d="M10 6h10M10 12h10M10 18h10M4 5h1v4M4 9h2M4 15h2l-2 3h2"/>',
        'quote'     => '<path d="M7 7h4v4c0 3-2 5-4 6M14 7h4v4c0 3-2 5-4 6"/>',
        'dots'      => '<circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>',
        'inbox'     => '<path d="M3 13h5l2 3h4l2-3h5M5 5h14l2 8v6H3v-6z"/>',
        'undo'      => '<path d="M9 14 4 9l5-5M4 9h11a5 5 0 0 1 0 10h-4"/>',
        'lock'      => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        // Pictogrammes des domaines d'action
        'education' => '<path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5M22 9v6"/>',
        'radio'     => '<rect x="3" y="8" width="18" height="12" rx="2"/><path d="m7 8 9-5"/><circle cx="15.5" cy="14" r="2.5"/><path d="M7 12h3M7 16h3"/>',
        'megaphone' => '<path d="M3 10v4a1 1 0 0 0 1 1h3l8 5V4L7 9H4a1 1 0 0 0-1 1zM19 9a4 4 0 0 1 0 6"/>',
        'music'     => '<path d="M9 18V5l11-2v13"/><circle cx="6.5" cy="18" r="2.5"/><circle cx="17.5" cy="16" r="2.5"/>',
        'women'     => '<circle cx="12" cy="9" r="5"/><path d="M12 14v8M9 19h6"/>',
        'leaf'      => '<path d="M5 19c0-9 6-14 15-15-1 9-6 15-15 15z"/><path d="M5 19 13 11"/>',
        'flask'     => '<path d="M9 3h6M10 3v6L4.5 18.5A1.7 1.7 0 0 0 6 21h12a1.7 1.7 0 0 0 1.5-2.5L14 9V3"/><path d="M7 15h10"/>',
        'peace'     => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18M12 12l-6.4 6.4M12 12l6.4 6.4"/>',
        'network'   => '<circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="18" r="2.5"/><circle cx="19" cy="18" r="2.5"/><path d="M10.8 7.2 6.2 15.8M13.2 7.2l4.6 8.6M7.5 18h9"/>',
        'compass'   => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>',
        'handshake' => '<path d="m11 17 2 2a1.4 1.4 0 0 0 2-2M14 14l2.5 2.5a1.4 1.4 0 0 0 2-2l-3.9-3.9a2 2 0 0 0-2.8 0l-.9.9a1.4 1.4 0 0 1-2-2l2.8-2.8a3.5 3.5 0 0 1 4.3-.5l.5.3a2 2 0 0 0 1.4.2L21 6M21 5v8h-2M3 5v8h2l5.5 5.5a1.4 1.4 0 0 0 2-2"/><path d="M3 6h8"/>',
    ];
    $inner = $paths[$name] ?? $paths['dots'];
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
}

/**
 * Pictogramme associé à un domaine d'action, déduit de son adresse (slug).
 * Les domaines créés plus tard reçoivent un pictogramme générique.
 */
function domaine_icone(?string $slug): string
{
    $slug = (string) $slug;
    $correspondances = [
        'desinformation' => 'shield',
        'haine'          => 'shield',
        'medias'         => 'radio',
        'media'          => 'radio',
        'education'      => 'education',
        'jeune'          => 'education',
        'culture'        => 'music',
        'diversite'      => 'music',
        'genre'          => 'women',
        'femme'          => 'women',
        'ressources'     => 'leaf',
        'environnement'  => 'leaf',
        'recherche'      => 'flask',
    ];
    foreach ($correspondances as $motCle => $icone) {
        if (str_contains($slug, $motCle)) {
            return $icone;
        }
    }
    return 'peace';
}

/** Logo de l'organisation : un seul fichier à remplacer (public/assets/img/logo.svg). */
function logo(int $size = 48, bool $blanc = false): string
{
    $fichier = $blanc ? 'img/logo-blanc.svg' : 'img/logo.svg';
    return '<img class="logo" src="' . e(asset($fichier)) . '" width="' . $size . '" height="' . $size . '" alt="">';
}

/** Initiales d'un partenaire (sigle ou nom), affichées tant qu'aucun logo n'est fourni. */
function monogramme(?string $sigle, string $nom): string
{
    if ($sigle !== null && trim($sigle) !== '') {
        return mb_strtoupper(mb_substr(preg_replace('/[^\p{L}\p{N}]/u', '', $sigle) ?? '', 0, 4));
    }
    $mots = preg_split('/[\s\-]+/u', $nom) ?: [];
    $initiales = '';
    foreach ($mots as $mot) {
        if (mb_strlen($mot) > 3 || preg_match('/^\p{Lu}/u', $mot)) {
            $initiales .= mb_substr($mot, 0, 1);
        }
    }
    return mb_strtoupper(mb_substr($initiales !== '' ? $initiales : $nom, 0, 3));
}

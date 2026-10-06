<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Media;
use App\Services\ActivityLog;
use App\Services\Settings;

final class ParametreController extends AdminController
{
    /**
     * Champs modifiables, regroupés par section : clé => [libellé, longueur max, type].
     * @var array<string, array{titre: string, aide: string, champs: array<string, array{0: string, 1: int, 2: string}>}>
     */
    public const SECTIONS = [
        'identite' => [
            'titre'  => 'Identité du site',
            'aide'   => 'Nom et description utilisés dans l\'en-tête, le pied de page et les moteurs de recherche.',
            'champs' => [
                'site_nom'         => ['Nom du site', 80, 'text'],
                'site_sous_titre'  => ['Sous-titre', 120, 'text'],
                'site_description' => ['Description (moteurs de recherche)', 300, 'textarea'],
            ],
        ],
        'contact' => [
            'titre'  => 'Coordonnées',
            'aide'   => 'Affichées sur la page Contact et dans le pied de page.',
            'champs' => [
                'contact_adresse'     => ['Adresse du siège', 200, 'text'],
                'contact_telephone'   => ['Téléphone principal', 40, 'text'],
                'contact_telephone_2' => ['Téléphone secondaire', 40, 'text'],
                'contact_email'       => ['E-mail principal', 190, 'email'],
                'contact_email_2'     => ['E-mail secondaire', 190, 'email'],
            ],
        ],
        'reseaux' => [
            'titre'  => 'Réseaux sociaux',
            'aide'   => 'Adresses complètes (https://…). Un réseau laissé vide n\'est pas affiché.',
            'champs' => [
                'reseau_facebook' => ['Facebook', 255, 'url'],
                'reseau_x'        => ['X', 255, 'url'],
                'reseau_linkedin' => ['LinkedIn', 255, 'url'],
                'reseau_tiktok'   => ['TikTok', 255, 'url'],
            ],
        ],
        'accueil' => [
            'titre'  => "Page d'accueil",
            'aide'   => 'Image et textes de la première section, puis bloc « Qui sommes-nous » de l\'accueil.',
            'champs' => [
                'accueil_titre'        => ['Titre principal', 160, 'textarea'],
                'accueil_intro'        => ['Texte d\'introduction', 400, 'textarea'],
                'accueil_encart_valeur' => ['Légende de l\'image : valeur mise en avant', 20, 'text'],
                'accueil_encart_texte' => ['Légende de l\'image : texte', 200, 'textarea'],
                'accueil_qsn_titre'    => ['Bloc « Qui sommes-nous » : titre', 160, 'text'],
                'accueil_qsn_texte'    => ['Bloc « Qui sommes-nous » : texte', 600, 'textarea'],
                'accueil_mission'      => ['Mission (résumé)', 300, 'textarea'],
                'accueil_vision'       => ['Vision (résumé)', 300, 'textarea'],
                'accueil_gouvernance'  => ['Gouvernance (résumé)', 300, 'textarea'],
            ],
        ],
        'reperes' => [
            'titre'  => 'Repères (bandeau de l\'accueil)',
            'aide'   => 'Uniquement des faits vérifiables. Laissez une valeur vide pour masquer le repère.',
            'champs' => [
                'repere_1_valeur' => ['Repère 1 : valeur', 20, 'text'], 'repere_1_libelle' => ['Repère 1 : libellé', 120, 'text'],
                'repere_2_valeur' => ['Repère 2 : valeur', 20, 'text'], 'repere_2_libelle' => ['Repère 2 : libellé', 120, 'text'],
                'repere_3_valeur' => ['Repère 3 : valeur', 20, 'text'], 'repere_3_libelle' => ['Repère 3 : libellé', 120, 'text'],
                'repere_4_valeur' => ['Repère 4 : valeur', 20, 'text'], 'repere_4_libelle' => ['Repère 4 : libellé', 120, 'text'],
                'repere_5_valeur' => ['Repère 5 : valeur', 20, 'text'], 'repere_5_libelle' => ['Repère 5 : libellé', 120, 'text'],
            ],
        ],
        'valeur' => [
            'titre'  => 'Valeur ajoutée',
            'aide'   => 'Les trois arguments affichés sur l\'accueil et sur la page « Valeur ajoutée ».',
            'champs' => [
                'valeur_1_titre' => ['Argument 1 : titre', 160, 'text'], 'valeur_1_texte' => ['Argument 1 : texte', 600, 'textarea'],
                'valeur_2_titre' => ['Argument 2 : titre', 160, 'text'], 'valeur_2_texte' => ['Argument 2 : texte', 600, 'textarea'],
                'valeur_3_titre' => ['Argument 3 : titre', 160, 'text'], 'valeur_3_texte' => ['Argument 3 : texte', 600, 'textarea'],
            ],
        ],
    ];

    public function edit(): void
    {
        $section = isset(self::SECTIONS[$_GET['section'] ?? '']) ? (string) $_GET['section'] : 'identite';
        $this->admin('parametres/index', [
            'titrePage' => 'Paramètres',
            'fil'       => ['Administration', 'Paramètres'],
            'sections'  => self::SECTIONS,
            'section'   => $section,
            'valeurs'   => Settings::all(),
            'image'     => $section === 'accueil' ? $this->imageAccueil() : null,
            'images'    => $section === 'accueil' ? Media::recentImages() : [],
        ]);
    }

    /** @return array<string, mixed>|null */
    private function imageAccueil(): ?array
    {
        $id = (int) Settings::get('accueil_image_id');
        return $id > 0 && Media::isImage($id) ? Media::find($id) : null;
    }

    public function update(): void
    {
        $input = $this->input();
        $section = isset(self::SECTIONS[$input['_section'] ?? '']) ? (string) $input['_section'] : 'identite';
        $redirect = '/admin/parametres?section=' . $section;
        $v = new Validator($input);
        $values = [];
        foreach (self::SECTIONS[$section]['champs'] as $key => [$label, $max, $type]) {
            $v->max($key, $max, $label);
            if ($type === 'email') {
                $v->email($key, $label);
            } elseif ($type === 'url') {
                $v->url($key, $label);
            }
            $values[$key] = $this->str($input, $key);
        }
        if ($section === 'accueil') {
            $current = (int) Settings::get('accueil_image_id');
            [$imageId, $imageError] = $this->resolveImage($input, $current > 0 ? $current : null, 'Image d\'accueil');
            $this->imageErrorOrFail($imageError, $v, $input, $redirect);
            $values['accueil_image_id'] = $imageId !== null ? (string) $imageId : '';
        }
        if ($v->fails()) {
            $this->failValidation($v, $input, $redirect);
        }
        Settings::save($values);
        ActivityLog::record('modification', 'parametres', null, 'Paramètres « ' . self::SECTIONS[$section]['titre'] . ' » mis à jour');
        $this->done('Les paramètres ont été enregistrés.', $redirect);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Message;

/**
 * Formulaire de contact : validation serveur, consentement explicite,
 * piège à robots et limitation du nombre d'envois par session.
 */
final class ContactController extends Controller
{
    private const MAX_PAR_HEURE = 5;

    public function show(): void
    {
        $this->render('contact', [
            'titrePage'       => 'Contact',
            'metaDescription' => 'Contacter CRAM-CRAM Mali : partenariat, recherche, demande d\'information.',
            'envoye'          => Session::get('_contact_envoye') === true,
        ]);
        Session::forget('_contact_envoye');
    }

    public function send(): void
    {
        $input = $this->input();

        // Piège à robots : champ invisible pour un humain. Réponse neutre, rien n'est enregistré.
        if ($this->str($input, 'site_web') !== '') {
            Session::set('_contact_envoye', true);
            Response::redirect('/contact');
        }

        $envois = array_filter((array) Session::get('_contact_envois', []), static fn ($t): bool => is_int($t) && $t > time() - 3600);
        if (count($envois) >= self::MAX_PAR_HEURE) {
            Session::withInput($input);
            Session::flash('erreur', 'Vous avez déjà envoyé plusieurs messages. Merci de patienter avant un nouvel envoi, ou écrivez-nous directement par e-mail.');
            Response::redirect('/contact#formulaire');
        }

        $v = (new Validator($input))
            ->required('nom', 'Nom complet')->max('nom', 150, 'Nom complet')
            ->required('email', 'Adresse e-mail')->email('email')->max('email', 190, 'Adresse e-mail')
            ->required('objet', 'Objet')->max('objet', 200, 'Objet')
            ->required('message', 'Message')->min('message', 10, 'Message')->max('message', 5000, 'Message');
        if (empty($input['consentement'])) {
            $v->error('consentement', 'Merci de cocher la case de consentement pour que nous puissions vous répondre.');
        }
        if ($v->fails()) {
            Session::withInput($input, $v->errors());
            Session::flash('erreur', 'Le message n\'a pas été envoyé : vérifiez les champs signalés.');
            Response::redirect('/contact#formulaire');
        }

        // Formulaire complet rempli en moins de 3 secondes : comportement de robot, réponse neutre.
        $debut = (int) ($input['_t'] ?? 0);
        if ($debut > 0 && time() - $debut < 3) {
            Session::set('_contact_envoye', true);
            Response::redirect('/contact#formulaire');
        }

        Message::create(
            $this->str($input, 'nom'),
            strtolower($this->str($input, 'email')),
            $this->str($input, 'objet'),
            $this->str($input, 'message')
        );
        $envois[] = time();
        Session::set('_contact_envois', array_values($envois));
        Session::set('_contact_envoye', true);
        Response::redirect('/contact#formulaire');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Core\Validator;
use App\Models\AdminUser;
use App\Services\ActivityLog;
use App\Services\Auth;

final class CompteController extends AdminController
{
    private const BASE = '/admin/compte';

    public function edit(): void
    {
        $this->admin('compte/index', [
            'titrePage' => 'Mon compte',
            'fil'       => ['Administration', 'Mon compte'],
            'compte'    => Auth::user(),
        ]);
    }

    public function updateProfile(): void
    {
        $user = (array) Auth::user();
        $input = $this->input();
        $v = (new Validator($input))
            ->required('nom', 'Nom affiché')->max('nom', 120, 'Nom affiché')
            ->required('email', 'Adresse e-mail')->email('email')->max('email', 190, 'Adresse e-mail');
        $email = strtolower($this->str($input, 'email'));
        if ($email !== '' && AdminUser::emailTaken($email, (int) $user['id'])) {
            $v->error('email', 'Cette adresse e-mail est déjà utilisée par un autre compte.');
        }
        if ($v->fails()) {
            $this->failValidation($v, $input, self::BASE);
        }
        AdminUser::updateProfile((int) $user['id'], $this->str($input, 'nom'), $email);
        ActivityLog::record('modification', 'compte', (int) $user['id'], 'Profil mis à jour');
        $this->done('Votre profil a été mis à jour.', self::BASE);
    }

    public function updatePassword(): void
    {
        $user = (array) Auth::user();
        $current = is_string($_POST['mot_de_passe_actuel'] ?? null) ? $_POST['mot_de_passe_actuel'] : '';
        $new = is_string($_POST['nouveau_mot_de_passe'] ?? null) ? $_POST['nouveau_mot_de_passe'] : '';
        $confirm = is_string($_POST['confirmation'] ?? null) ? $_POST['confirmation'] : '';

        $v = new Validator([]);
        if (!AdminUser::passwordMatches((int) $user['id'], $current)) {
            $v->error('mot_de_passe_actuel', 'Le mot de passe actuel est incorrect.');
        }
        if (mb_strlen($new) < 12) {
            $v->error('nouveau_mot_de_passe', 'Le nouveau mot de passe doit contenir au moins 12 caractères.');
        } elseif ($new === $current) {
            $v->error('nouveau_mot_de_passe', 'Le nouveau mot de passe doit être différent de l\'actuel.');
        } elseif ($new !== $confirm) {
            $v->error('confirmation', 'La confirmation ne correspond pas au nouveau mot de passe.');
        }
        if ($v->fails()) {
            $this->failValidation($v, [], self::BASE);
        }
        AdminUser::updatePassword((int) $user['id'], $new);
        Session::regenerate();
        ActivityLog::record('modification', 'compte', (int) $user['id'], 'Mot de passe modifié');
        $this->done('Votre mot de passe a été modifié.', self::BASE);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Services\Auth;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        header('Cache-Control: no-store, private');
        Response::view('admin/auth/login', ['titrePage' => 'Connexion'], 'layouts/auth');
    }

    public function login(): void
    {
        $email = strtolower($this->str($_POST, 'email'));
        $password = is_string($_POST['mot_de_passe'] ?? null) ? $_POST['mot_de_passe'] : '';

        if ($email === '' || $password === '') {
            Session::withInput(['email' => $email]);
            Session::flash('erreur', 'Renseignez votre adresse e-mail et votre mot de passe.');
            Response::redirect('/admin/connexion');
        }

        $result = Auth::attempt($email, $password);
        if (!$result['ok']) {
            Session::withInput(['email' => $email]);
            Session::flash('erreur', $result['message']);
            Response::redirect('/admin/connexion');
        }

        $intended = Session::get('_intended');
        Session::forget('_intended');
        $target = is_string($intended) && str_starts_with($intended, '/admin') && !str_starts_with($intended, '/admin/connexion') ? $intended : '/admin';
        Response::redirect($target);
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        Session::flash('succes', 'Vous êtes déconnecté(e).');
        Response::redirect('/admin/connexion');
    }
}

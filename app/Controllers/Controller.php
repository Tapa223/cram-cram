<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;

/**
 * Base des contrôleurs : rendu des vues et petits utilitaires de formulaire.
 */
abstract class Controller
{
    /** @param array<string, mixed> $data */
    protected function render(string $view, array $data = [], int $status = 200): void
    {
        Response::view('public/' . $view, $data, 'layouts/public', $status);
    }

    /** @param array<string, mixed> $data */
    protected function admin(string $view, array $data = []): void
    {
        Response::view('admin/' . $view, $data, 'layouts/admin');
    }

    /** @return array<string, mixed> */
    protected function input(): array
    {
        $data = [];
        foreach ($_POST as $key => $value) {
            if ($key === '_csrf') {
                continue;
            }
            $data[$key] = is_string($value) ? trim($value) : $value;
        }
        return $data;
    }

    protected function str(array $data, string $key): string
    {
        $value = $data[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    protected function nullable(array $data, string $key): ?string
    {
        $value = $this->str($data, $key);
        return $value === '' ? null : $value;
    }

    protected function intOrNull(array $data, string $key): ?int
    {
        $value = $this->str($data, $key);
        return ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }

    /** @return list<int> */
    protected function ids(array $data, string $key = 'ids'): array
    {
        $raw = $data[$key] ?? [];
        if (!is_array($raw)) {
            return [];
        }
        $ids = [];
        foreach ($raw as $value) {
            if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
                $ids[] = (int) $value;
            }
        }
        return array_values(array_unique($ids));
    }

    /** Renvoie au formulaire avec les erreurs et la saisie conservée. */
    protected function failValidation(Validator $validator, array $input, string $redirect): never
    {
        Session::withInput($input, $validator->errors());
        Session::flash('erreur', 'Le formulaire contient des erreurs. Corrigez les champs signalés puis enregistrez à nouveau.');
        Response::redirect($redirect);
    }

    /** @param array<string, mixed>|null $row @return array<string, mixed> */
    protected function orNotFound(?array $row): array
    {
        if ($row === null) {
            throw new HttpException(404);
        }
        return $row;
    }

    protected function page(): int
    {
        $page = $_GET['page'] ?? '1';
        return is_string($page) && ctype_digit($page) && (int) $page > 0 ? (int) $page : 1;
    }
}

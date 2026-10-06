<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\Page;
use App\Services\ActivityLog;
use App\Services\Auth;
use App\Services\ContentSanitizer;

final class PageController extends AdminController
{
    public function index(): void
    {
        $this->admin('pages/index', [
            'titrePage' => 'Pages',
            'fil'       => ['Contenus', 'Pages'],
            'pages'     => Page::allForAdmin(),
        ]);
    }

    public function edit(string $id): void
    {
        $page = $this->orNotFound(Page::find((int) $id));
        $this->admin('pages/form', [
            'titrePage' => 'Modifier la page',
            'fil'       => ['Pages', $page['titre']],
            'page'      => $page,
        ]);
    }

    public function update(string $id): void
    {
        $page = $this->orNotFound(Page::find((int) $id));
        $input = $this->input();
        $redirect = '/admin/pages/' . $page['id'] . '/modifier';
        $v = (new Validator($input))
            ->required('titre', 'Titre')->max('titre', 200, 'Titre')
            ->max('chapo', 400, 'Introduction')
            ->max('meta_description', 300, 'Description pour les moteurs de recherche');
        if ($v->fails()) {
            $this->failValidation($v, $input, $redirect);
        }
        Page::update(
            (int) $page['id'],
            $this->str($input, 'titre'),
            $this->nullable($input, 'chapo'),
            ContentSanitizer::clean($this->str($input, 'contenu')),
            $this->nullable($input, 'meta_description'),
            Auth::id()
        );
        ActivityLog::record('modification', 'page', (int) $page['id'], 'Page « ' . $this->str($input, 'titre') . ' » mise à jour');
        $this->done('La page a été mise à jour.', $redirect);
    }
}

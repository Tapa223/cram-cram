<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Message;
use App\Services\ActivityLog;

final class MessageController extends AdminController
{
    private const BASE = '/admin/messages';

    public function index(): void
    {
        $filtre = in_array($_GET['filtre'] ?? '', ['non_lus', 'lus'], true) ? (string) $_GET['filtre'] : '';
        $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        [$messages, $pager] = Message::paginateAdmin($filtre, $q, $this->page());

        $this->admin('messages/index', [
            'titrePage' => 'Messages',
            'fil'       => ['Échanges', 'Messages'],
            'messages'  => $messages,
            'pager'     => $pager,
            'filtre'    => $filtre,
            'q'         => $q,
            'nonLus'    => Message::unreadCount(),
            'total'     => Message::count(),
        ]);
    }

    public function show(string $id): void
    {
        $message = $this->orNotFound(Message::find((int) $id));
        if ((int) $message['lu'] === 0) {
            Message::markRead([(int) $message['id']], true);
            $message['lu'] = 1;
        }
        $this->admin('messages/show', [
            'titrePage' => 'Message',
            'fil'       => ['Messages', $message['objet']],
            'message'   => $message,
            'voisins'   => Message::neighbours((int) $message['id']),
        ]);
    }

    public function toggleRead(string $id): void
    {
        $message = $this->orNotFound(Message::find((int) $id));
        $read = (int) $message['lu'] === 0;
        Message::markRead([(int) $message['id']], $read);
        $this->done($read ? 'Message marqué comme lu.' : 'Message marqué comme non lu.', $read ? '/admin/messages/' . $message['id'] : self::BASE);
    }

    public function destroy(string $id): void
    {
        $message = $this->orNotFound(Message::find((int) $id));
        Message::delete((int) $message['id']);
        ActivityLog::record('suppression', 'message', (int) $message['id'], 'Message de ' . $message['nom'] . ' supprimé');
        $this->done('Le message a été supprimé.', $this->returnTo(self::BASE));
    }

    public function bulk(): void
    {
        $input = $this->input();
        $ids = $this->ids($input);
        $back = $this->returnTo(self::BASE);
        $this->requireSelection($ids, $back);
        $action = $this->str($input, 'action');
        $n = match ($action) {
            'lu'        => Message::markRead($ids, true),
            'non_lu'    => Message::markRead($ids, false),
            'supprimer' => Message::deleteMany($ids),
            default     => -1,
        };
        if ($n < 0) {
            $this->done('Action inconnue.', $back);
        }
        if ($action === 'supprimer') {
            ActivityLog::record('suppression', 'message', null, count($ids) . ' message(s) supprimé(s)');
        }
        $libelles = ['lu' => 'marqué(s) comme lu(s)', 'non_lu' => 'marqué(s) comme non lu(s)', 'supprimer' => 'supprimé(s)'];
        $this->done(pluriel(count($ids), 'message') . ' ' . $libelles[$action] . '.', $back);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Paginator;

final class Message extends Model
{
    protected const TABLE = 'messages_contact';

    /** @return array{0: list<array<string, mixed>>, 1: Paginator} */
    public static function paginateAdmin(string $filtre, string $search, int $page, int $perPage = 20): array
    {
        $where = ['1 = 1'];
        $params = [];
        if ($filtre === 'non_lus') {
            $where[] = 'lu = 0';
        } elseif ($filtre === 'lus') {
            $where[] = 'lu = 1';
        }
        if ($search !== '') {
            $where[] = '(nom LIKE ? OR email LIKE ? OR objet LIKE ? OR message LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $sqlWhere = implode(' AND ', $where);
        $total = (int) Database::value("SELECT COUNT(*) FROM messages_contact WHERE {$sqlWhere}", $params);
        $pager = new Paginator($total, $page, $perPage);
        $rows = Database::all(
            "SELECT * FROM messages_contact WHERE {$sqlWhere} ORDER BY cree_le DESC, id DESC LIMIT {$pager->perPage} OFFSET {$pager->offset}",
            $params
        );
        return [$rows, $pager];
    }

    public static function unreadCount(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM messages_contact WHERE lu = 0');
    }

    /** @return list<array<string, mixed>> */
    public static function latestUnread(int $limit = 4): array
    {
        return Database::all('SELECT id, nom, objet, cree_le FROM messages_contact WHERE lu = 0 ORDER BY cree_le DESC LIMIT ' . max(1, $limit));
    }

    public static function create(string $nom, string $email, string $objet, string $message): int
    {
        Database::query(
            'INSERT INTO messages_contact (nom, email, objet, message, consentement) VALUES (?, ?, ?, ?, 1)',
            [$nom, $email, $objet, $message]
        );
        return Database::lastId();
    }

    /** @param list<int> $ids */
    public static function markRead(array $ids, bool $read): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query(
            "UPDATE messages_contact SET lu = ?, lu_le = IF(? = 1, COALESCE(lu_le, UTC_TIMESTAMP()), NULL) WHERE id IN ({$in})",
            [$read ? 1 : 0, $read ? 1 : 0, ...$ids]
        )->rowCount();
    }

    /** @param list<int> $ids */
    public static function deleteMany(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::query("DELETE FROM messages_contact WHERE id IN ({$in})", $ids)->rowCount();
    }

    /** @return array{prev: ?int, next: ?int} */
    public static function neighbours(int $id): array
    {
        $prev = Database::value('SELECT id FROM messages_contact WHERE id > ? ORDER BY id ASC LIMIT 1', [$id]);
        $next = Database::value('SELECT id FROM messages_contact WHERE id < ? ORDER BY id DESC LIMIT 1', [$id]);
        return ['prev' => $prev !== null ? (int) $prev : null, 'next' => $next !== null ? (int) $next : null];
    }
}

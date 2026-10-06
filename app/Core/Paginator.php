<?php

declare(strict_types=1);

namespace App\Core;

final class Paginator
{
    public readonly int $pages;
    public readonly int $offset;

    public function __construct(public readonly int $total, public readonly int $page, public readonly int $perPage)
    {
        $this->pages = max(1, (int) ceil($total / max(1, $perPage)));
        $current = min(max(1, $page), $this->pages);
        $this->offset = ($current - 1) * $perPage;
    }

    public function current(): int
    {
        return min(max(1, $this->page), $this->pages);
    }

    public function from(): int
    {
        return $this->total === 0 ? 0 : $this->offset + 1;
    }

    public function to(): int
    {
        return min($this->total, $this->offset + $this->perPage);
    }

    /** URL de la page $n en conservant les autres paramètres de la requête. */
    public function link(int $n): string
    {
        $query = $_GET;
        $query['page'] = $n;
        return '?' . http_build_query($query);
    }
}

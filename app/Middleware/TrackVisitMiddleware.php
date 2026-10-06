<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\VisitTracker;

final class TrackVisitMiddleware
{
    public function handle(): void
    {
        VisitTracker::track();
    }
}

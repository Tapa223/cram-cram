<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Session;

/**
 * Mesure d'audience agrégée : une visite par session et par jour, et le nombre de pages vues.
 * Aucun identifiant personnel, aucune IP, aucun outil tiers. Les robots connus sont ignorés.
 */
final class VisitTracker
{
    private const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget|python|headless|lighthouse/i';

    public static function track(): void
    {
        if (Request::method() !== 'GET') {
            return;
        }
        $ua = Request::userAgent();
        if ($ua === '' || preg_match(self::BOTS, $ua)) {
            return;
        }

        $today = date('Y-m-d');
        $newVisit = Session::get('_visite_jour') !== $today;
        if ($newVisit) {
            Session::set('_visite_jour', $today);
        }

        try {
            Database::query(
                'INSERT INTO visites_journalieres (jour, visites, pages_vues) VALUES (?, ?, 1)
                 ON DUPLICATE KEY UPDATE visites = visites + VALUES(visites), pages_vues = pages_vues + 1',
                [$today, $newVisit ? 1 : 0]
            );
        } catch (\Throwable $e) {
            ErrorHandler::log($e);
        }
    }
}

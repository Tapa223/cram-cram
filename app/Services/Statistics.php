<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Séries de fréquentation pour le tableau de bord et la page Statistiques.
 * Uniquement des données réelles : un jour sans visite vaut 0, rien n'est extrapolé.
 */
final class Statistics
{
    /** @return list<array{cle: string, label: string, titre: string, visites: int, pages: int}> */
    public static function daily(int $days): array
    {
        $rows = Database::all(
            'SELECT jour, visites, pages_vues FROM visites_journalieres WHERE jour >= DATE_SUB(CURDATE(), INTERVAL ? DAY)',
            [$days - 1]
        );
        $byDay = [];
        foreach ($rows as $row) {
            $byDay[(string) $row['jour']] = $row;
        }
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $ts = strtotime("-{$i} day");
            $key = date('Y-m-d', $ts);
            $label = $days <= 7
                ? ucfirst(mb_substr(JOURS_FR[(int) date('w', $ts)], 0, 3)) . '.'
                : (int) date('j', $ts) . '/' . date('m', $ts);
            $series[] = [
                'cle'     => $key,
                'label'   => $label,
                'titre'   => date_fr($key),
                'visites' => (int) ($byDay[$key]['visites'] ?? 0),
                'pages'   => (int) ($byDay[$key]['pages_vues'] ?? 0),
            ];
        }
        return $series;
    }

    /** @return list<array{cle: string, label: string, titre: string, visites: int, pages: int}> */
    public static function monthly(int $months = 12): array
    {
        $rows = Database::all(
            "SELECT DATE_FORMAT(jour, '%Y-%m') AS mois, SUM(visites) AS visites, SUM(pages_vues) AS pages
             FROM visites_journalieres
             WHERE jour >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL ? MONTH)
             GROUP BY mois",
            [$months - 1]
        );
        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[(string) $row['mois']] = $row;
        }
        $series = [];
        $first = strtotime(date('Y-m-01'));
        for ($i = $months - 1; $i >= 0; $i--) {
            $ts = strtotime("-{$i} month", $first);
            $key = date('Y-m', $ts);
            $monthIndex = (int) date('n', $ts) - 1;
            $series[] = [
                'cle'     => $key,
                'label'   => MOIS_COURTS_FR[$monthIndex],
                'titre'   => ucfirst(MOIS_FR[$monthIndex]) . ' ' . date('Y', $ts),
                'visites' => (int) ($byMonth[$key]['visites'] ?? 0),
                'pages'   => (int) ($byMonth[$key]['pages'] ?? 0),
            ];
        }
        return $series;
    }

    /** @param list<array{visites: int}> $series */
    public static function total(array $series, string $key = 'visites'): int
    {
        return array_sum(array_column($series, $key));
    }

    /** Graduation « ronde » de l'axe vertical, jamais inférieure à 5. */
    public static function scaleMax(int $max): int
    {
        if ($max <= 5) {
            return 5;
        }
        $magnitude = 10 ** (int) floor(log10($max));
        foreach ([1, 2, 2.5, 5, 10] as $step) {
            $candidate = (int) ceil($step * $magnitude);
            if ($candidate >= $max) {
                return $candidate;
            }
        }
        return $max;
    }
}

<?php
/**
 * Histogramme SVG rendu côté serveur (aucune bibliothèque, compatible CSP).
 * @var list<array{label: string, titre: string, visites: int}> $series
 * @var string $unite  ex. « visites »
 * @var string $description  texte alternatif du graphique
 */

use App\Services\Statistics;

$values = array_column($series, 'visites');
$max = Statistics::scaleMax($values === [] ? 0 : max($values));
$count = max(1, count($series));
$width = 720;
$height = 220;
$left = 36;
$bottom = 26;
$top = 8;
$plotW = $width - $left;
$plotH = $height - $bottom - $top;
$slot = $plotW / $count;
$barW = max(3, min(36, $slot * 0.62));
$labelEvery = $count > 16 ? (int) ceil($count / 8) : 1;
$lastIndex = $count - 1;
?>
<figure class="chart">
    <svg viewBox="0 0 <?= $width ?> <?= $height ?>" role="img" aria-labelledby="chart-desc-<?= e($id ?? 'main') ?>" class="chart__svg">
        <title id="chart-desc-<?= e($id ?? 'main') ?>"><?= e($description) ?></title>
        <?php foreach ([0, 0.5, 1] as $ratio): ?>
            <?php $y = $top + $plotH - $plotH * $ratio; ?>
            <line x1="<?= $left ?>" x2="<?= $width ?>" y1="<?= $y ?>" y2="<?= $y ?>" class="chart__grid"/>
            <text x="<?= $left - 8 ?>" y="<?= $y + 4 ?>" class="chart__axis" text-anchor="end"><?= (int) round($max * $ratio) ?></text>
        <?php endforeach; ?>
        <?php foreach ($series as $i => $point): ?>
            <?php
            $h = $max > 0 ? ($point['visites'] / $max) * $plotH : 0;
            $x = $left + $slot * $i + ($slot - $barW) / 2;
            ?>
            <g class="chart__bar<?= $i === $lastIndex ? ' is-current' : '' ?>">
                <rect x="<?= round($x, 1) ?>" y="<?= round($top + $plotH - max($h, $point['visites'] > 0 ? 2 : 0), 1) ?>" width="<?= round($barW, 1) ?>" height="<?= round(max($h, $point['visites'] > 0 ? 2 : 0), 1) ?>" rx="3">
                    <title><?= e($point['titre'] . ' : ' . $point['visites'] . ' ' . $unite) ?></title>
                </rect>
                <?php if (($lastIndex - $i) % $labelEvery === 0): ?>
                    <text x="<?= round($x + $barW / 2, 1) ?>" y="<?= $height - 6 ?>" class="chart__axis" text-anchor="middle"><?= e($point['label']) ?></text>
                <?php endif; ?>
            </g>
        <?php endforeach; ?>
    </svg>
</figure>

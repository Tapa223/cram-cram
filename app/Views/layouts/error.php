<?php
/**
 * Gabarit autonome des pages d'erreur (ne dépend ni de la base ni de la session).
 * @var string $content
 */
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($titrePage ?? 'Erreur') ?> · CRAM-CRAM Mali</title>
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
</head>
<body class="error-body">
<main class="error-page">
    <a href="<?= e(url('/')) ?>" class="brand brand--center" aria-label="CRAM-CRAM Mali, accueil">
        <?= logo(64) ?>
    </a>
    <?= $content ?>
</main>
</body>
</html>

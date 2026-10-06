<?php
/**
 * Gabarit des pages de connexion.
 * @var string $content
 */

use App\Core\Session;
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titrePage ?? 'Connexion') ?> · Administration CRAM-CRAM</title>
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
</head>
<body class="auth-body">
<main class="auth">
    <section class="auth__panel" aria-hidden="true">
        <?= logo(72, true) ?>
        <p class="auth__quote">Construire la paix, protéger l'espace numérique, renforcer la cohésion sociale.</p>
        <ul class="auth__tags">
            <li>Contenus</li><li>Médias</li><li>Messages</li><li>Statistiques</li>
        </ul>
        <p class="auth__org">CRAM-CRAM Mali · Espace d'administration</p>
    </section>
    <section class="auth__form">
        <?php foreach (Session::flashes() as $flash): ?>
            <div class="alert alert--<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'erreur' ? 'alert' : 'status' ?>">
                <?= icon($flash['type'] === 'succes' ? 'check' : ($flash['type'] === 'erreur' ? 'alert' : 'info'), 18) ?>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endforeach; ?>
        <?= $content ?>
    </section>
</main>
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>

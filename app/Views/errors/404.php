<p class="eyebrow">Erreur 404</p>
<h1 class="error-page__title">Cette page est introuvable.</h1>
<p class="error-page__text">L'adresse a peut-être changé, ou le contenu n'est plus publié.</p>
<div class="error-page__actions">
    <?php if (!empty($isAdmin)): ?>
        <a class="btn btn--primary" href="<?= e(url('/admin')) ?>">Retour au tableau de bord</a>
    <?php else: ?>
        <a class="btn btn--primary" href="<?= e(url('/')) ?>">Retour à l'accueil</a>
        <a class="btn btn--outline" href="<?= e(url('/contact')) ?>">Nous contacter</a>
    <?php endif; ?>
</div>

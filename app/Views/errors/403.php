<p class="eyebrow">Accès refusé</p>
<h1 class="error-page__title">Vous n'avez pas accès à cette page.</h1>
<p class="error-page__text">Si vous pensez qu'il s'agit d'une erreur, reconnectez-vous puis réessayez.</p>
<div class="error-page__actions">
    <a class="btn btn--primary" href="<?= e(url(!empty($isAdmin) ? '/admin' : '/')) ?>">Revenir en lieu sûr</a>
</div>

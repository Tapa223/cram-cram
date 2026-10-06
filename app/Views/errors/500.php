<?php /** @var \Throwable|null $debug */ ?>
<p class="eyebrow">Erreur technique</p>
<h1 class="error-page__title">Une erreur est survenue.</h1>
<p class="error-page__text">L'incident a été enregistré. Merci de réessayer dans quelques instants.</p>
<div class="error-page__actions">
    <a class="btn btn--primary" href="<?= e(url(!empty($isAdmin) ? '/admin' : '/')) ?>"><?= !empty($isAdmin) ? 'Retour au tableau de bord' : "Retour à l'accueil" ?></a>
</div>
<?php if (!empty($debug)): ?>
    <details class="debug" open>
        <summary>Détail technique (visible uniquement en local, APP_DEBUG=true)</summary>
        <pre><?= e(get_class($debug) . ' : ' . $debug->getMessage() . "\n" . $debug->getFile() . ':' . $debug->getLine() . "\n\n" . $debug->getTraceAsString()) ?></pre>
    </details>
<?php endif; ?>

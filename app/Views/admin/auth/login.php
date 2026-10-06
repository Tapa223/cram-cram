<div class="auth__card">
    <h1 class="auth__title">Connexion</h1>
    <p class="auth__sub">Accédez à la gestion des contenus du site.</p>
    <form method="post" action="<?= e(url('/admin/connexion')) ?>" class="stack" data-submit-once novalidate>
        <?= csrf_field() ?>
        <div class="field">
            <label class="field__label" for="email">Adresse e-mail</label>
            <input class="input" id="email" type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label class="field__label" for="mot_de_passe">Mot de passe</label>
            <div class="input-group">
                <input class="input" id="mot_de_passe" type="password" name="mot_de_passe" autocomplete="current-password" required>
                <button type="button" class="input-group__btn" data-toggle-password="mot_de_passe" aria-label="Afficher le mot de passe"><?= icon('eye', 18) ?></button>
            </div>
        </div>
        <button type="submit" class="btn btn--primary btn--block btn--lg">Se connecter</button>
    </form>
    <p class="auth__foot"><a href="<?= e(url('/')) ?>"><?= icon('back', 16) ?> Retour au site</a></p>
</div>

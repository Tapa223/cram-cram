<?php

use App\Services\Auth;

$erreur = static fn (string $champ): string => field_error($champ) ? '<p class="field__error" id="' . $champ . '-err">' . icon('alert', 16) . ' ' . e(field_error($champ)) . '</p>' : '';
$invalide = static fn (string $champ): string => field_error($champ) ? ' aria-invalid="true" aria-describedby="' . $champ . '-err"' : '';
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Mon compte</h1>
        <p class="page-sub">Vos informations de connexion à l'administration.</p>
    </div>
</div>

<?php if ((int) (Auth::user()['doit_changer_mdp'] ?? 0) === 1): ?>
    <div class="alert alert--info alert--block" role="status">
        <?= icon('lock', 18) ?>
        <span><strong>Mot de passe provisoire.</strong> Choisissez un mot de passe personnel (12 caractères minimum) pour accéder au reste de l'administration. Pensez aussi à remplacer l'adresse e-mail de connexion par la vôtre.</span>
    </div>
<?php endif; ?>

<div class="grid grid--2">
    <section class="card">
        <div class="card__head">
            <div class="message__from">
                <span class="avatar" aria-hidden="true"><?= e(Auth::initials((string) $compte['nom'])) ?></span>
                <div><h2 class="card__title">Profil</h2><p class="card__sub">Dernière connexion : <?= $compte['derniere_connexion'] ? e(date_fr((string) $compte['derniere_connexion'], true)) : '—' ?></p></div>
            </div>
        </div>
        <form method="post" action="<?= e(url('/admin/compte')) ?>" class="card__body stack" data-submit-once novalidate>
            <?= csrf_field() ?>
            <div class="field<?= field_error('nom') ? ' has-error' : '' ?>">
                <label class="field__label" for="nom">Nom affiché</label>
                <input class="input" id="nom" name="nom" type="text" maxlength="120" required value="<?= e(old('nom', $compte['nom'])) ?>" autocomplete="name"<?= $invalide('nom') ?>>
                <?= $erreur('nom') ?>
            </div>
            <div class="field<?= field_error('email') ? ' has-error' : '' ?>">
                <label class="field__label" for="email">Adresse e-mail de connexion</label>
                <input class="input" id="email" name="email" type="email" maxlength="190" required value="<?= e(old('email', $compte['email'])) ?>" autocomplete="email"<?= $invalide('email') ?>>
                <?= $erreur('email') ?>
            </div>
            <div><button type="submit" class="btn btn--primary"><?= icon('check', 17) ?> Enregistrer le profil</button></div>
        </form>
    </section>

    <section class="card">
        <div class="card__head">
            <div><h2 class="card__title">Mot de passe</h2><p class="card__sub">12 caractères minimum. Utilisez une phrase facile à retenir.</p></div>
        </div>
        <form method="post" action="<?= e(url('/admin/compte/mot-de-passe')) ?>" class="card__body stack" data-submit-once novalidate>
            <?= csrf_field() ?>
            <div class="field<?= field_error('mot_de_passe_actuel') ? ' has-error' : '' ?>">
                <label class="field__label" for="mot_de_passe_actuel">Mot de passe actuel</label>
                <input class="input" id="mot_de_passe_actuel" name="mot_de_passe_actuel" type="password" required autocomplete="current-password"<?= $invalide('mot_de_passe_actuel') ?>>
                <?= $erreur('mot_de_passe_actuel') ?>
            </div>
            <div class="field<?= field_error('nouveau_mot_de_passe') ? ' has-error' : '' ?>">
                <label class="field__label" for="nouveau_mot_de_passe">Nouveau mot de passe</label>
                <div class="input-group">
                    <input class="input" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" type="password" minlength="12" required autocomplete="new-password"<?= $invalide('nouveau_mot_de_passe') ?>>
                    <button type="button" class="input-group__btn" data-toggle-password="nouveau_mot_de_passe" aria-label="Afficher le mot de passe"><?= icon('eye', 18) ?></button>
                </div>
                <?= $erreur('nouveau_mot_de_passe') ?>
            </div>
            <div class="field<?= field_error('confirmation') ? ' has-error' : '' ?>">
                <label class="field__label" for="confirmation">Confirmer le nouveau mot de passe</label>
                <input class="input" id="confirmation" name="confirmation" type="password" required autocomplete="new-password"<?= $invalide('confirmation') ?>>
                <?= $erreur('confirmation') ?>
            </div>
            <div><button type="submit" class="btn btn--primary"><?= icon('lock', 17) ?> Changer le mot de passe</button></div>
        </form>
    </section>
</div>

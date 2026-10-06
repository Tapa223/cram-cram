<?php

use App\Core\Session;
use App\Core\View;

$erreur = static fn (string $champ): string => field_error($champ) ? '<p class="field__error" id="' . $champ . '-err">' . icon('alert', 16) . ' ' . e(field_error($champ)) . '</p>' : '';
$invalide = static fn (string $champ): string => field_error($champ) ? ' aria-invalid="true" aria-describedby="' . $champ . '-err"' : '';
?>
<?= View::partial('partials/public/page-hero', [
    'titre'   => 'Nous contacter',
    'chapo'   => 'Une question, un partenariat, une demande d\'information : notre équipe vous répond.',
]) ?>

<section class="section section--first">
    <div class="wrap contact">
        <aside class="contact__infos reveal">
            <?php if (setting('contact_adresse') !== ''): ?>
                <div class="contact__item"><span class="contact__icon"><?= icon('pin', 22) ?></span><div><p class="contact__label">Siège</p><p><?= e(setting('contact_adresse')) ?></p></div></div>
            <?php endif; ?>
            <?php $tels = array_filter([setting('contact_telephone'), setting('contact_telephone_2')]); ?>
            <?php if ($tels !== []): ?>
                <div class="contact__item"><span class="contact__icon"><?= icon('phone', 22) ?></span><div><p class="contact__label">Téléphone</p><?php foreach ($tels as $tel): ?><p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $tel)) ?>"><?= e($tel) ?></a></p><?php endforeach; ?></div></div>
            <?php endif; ?>
            <?php $mails = array_filter([setting('contact_email'), setting('contact_email_2')]); ?>
            <?php if ($mails !== []): ?>
                <div class="contact__item"><span class="contact__icon"><?= icon('mail', 22) ?></span><div><p class="contact__label">Courriel</p><?php foreach ($mails as $mail): ?><p><a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></p><?php endforeach; ?></div></div>
            <?php endif; ?>
        </aside>

        <div class="contact__form reveal" id="formulaire">
            <?php if ($envoye): ?>
                <div class="notice notice--succes" role="status" tabindex="-1" data-focus>
                    <span class="notice__icon"><?= icon('check', 22) ?></span>
                    <div>
                        <p class="notice__title">Merci, votre message a bien été envoyé.</p>
                        <p>Notre équipe vous répondra dans les meilleurs délais à l'adresse indiquée.</p>
                    </div>
                </div>
            <?php endif; ?>
            <?php foreach (Session::flashes() as $flash): ?>
                <div class="notice notice--<?= e($flash['type']) ?>" role="alert" tabindex="-1" data-focus>
                    <span class="notice__icon"><?= icon('alert', 22) ?></span>
                    <div><p><?= e($flash['message']) ?></p></div>
                </div>
            <?php endforeach; ?>

            <form method="post" action="<?= e(url('/contact')) ?>#formulaire" class="form-grid" data-submit-once novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="_t" value="<?= time() ?>">
                <div class="hp" aria-hidden="true">
                    <label for="site_web">Ne pas remplir ce champ</label>
                    <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
                </div>
                <div class="field<?= field_error('nom') ? ' has-error' : '' ?>">
                    <label class="field__label" for="nom">Nom complet <span class="req" aria-hidden="true">*</span></label>
                    <input class="input" id="nom" name="nom" type="text" maxlength="150" required autocomplete="name" value="<?= e(old('nom')) ?>"<?= $invalide('nom') ?>>
                    <?= $erreur('nom') ?>
                </div>
                <div class="field<?= field_error('email') ? ' has-error' : '' ?>">
                    <label class="field__label" for="email">Adresse e-mail <span class="req" aria-hidden="true">*</span></label>
                    <input class="input" id="email" name="email" type="email" maxlength="190" required autocomplete="email" value="<?= e(old('email')) ?>"<?= $invalide('email') ?>>
                    <?= $erreur('email') ?>
                </div>
                <div class="field field--full<?= field_error('objet') ? ' has-error' : '' ?>">
                    <label class="field__label" for="objet">Objet <span class="req" aria-hidden="true">*</span></label>
                    <input class="input" id="objet" name="objet" type="text" maxlength="200" required value="<?= e(old('objet')) ?>"<?= $invalide('objet') ?>>
                    <?= $erreur('objet') ?>
                </div>
                <div class="field field--full<?= field_error('message') ? ' has-error' : '' ?>">
                    <label class="field__label" for="message">Message <span class="req" aria-hidden="true">*</span></label>
                    <textarea class="input" id="message" name="message" rows="7" maxlength="5000" required<?= $invalide('message') ?>><?= e(old('message')) ?></textarea>
                    <?= $erreur('message') ?>
                </div>
                <div class="field field--full<?= field_error('consentement') ? ' has-error' : '' ?>">
                    <label class="check">
                        <input type="checkbox" name="consentement" value="1" required<?= old('consentement') ? ' checked' : '' ?><?= $invalide('consentement') ?>>
                        <span>J'accepte que mes données soient utilisées uniquement pour répondre à ma demande, conformément à la <a href="<?= e(url('/mentions-legales')) ?>">politique de confidentialité</a>.</span>
                    </label>
                    <?= $erreur('consentement') ?>
                </div>
                <div class="field--full form-grid__foot">
                    <p class="form-grid__note">Les champs marqués <span class="req">*</span> sont obligatoires.</p>
                    <button type="submit" class="btn btn--primary btn--lg">Envoyer le message <?= icon('arrow', 18) ?></button>
                </div>
            </form>
        </div>
    </div>
</section>

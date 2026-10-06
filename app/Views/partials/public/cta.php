<section class="cta">
    <div class="wrap cta__grid">
        <div class="reveal">
            <h2 class="cta__title">Construisons ensemble la paix et la cohésion sociale.</h2>
            <p class="cta__text">Partenariat, recherche, demande d'information : notre équipe vous répond.</p>
            <a class="btn btn--light btn--lg" href="<?= e(url('/contact')) ?>">Écrire à CRAM-CRAM</a>
        </div>
        <dl class="cta__infos reveal">
            <?php if (setting('contact_adresse') !== ''): ?>
                <div><dt>Siège</dt><dd><?= e(setting('contact_adresse')) ?></dd></div>
            <?php endif; ?>
            <?php $tels = array_filter([setting('contact_telephone'), setting('contact_telephone_2')]); ?>
            <?php if ($tels !== []): ?>
                <div><dt>Téléphone</dt><dd><?php foreach (array_values($tels) as $i => $tel): ?><?= $i > 0 ? ' · ' : '' ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $tel)) ?>"><?= e($tel) ?></a><?php endforeach; ?></dd></div>
            <?php endif; ?>
            <?php $mails = array_filter([setting('contact_email'), setting('contact_email_2')]); ?>
            <?php if ($mails !== []): ?>
                <div><dt>Courriel</dt><dd><?php foreach (array_values($mails) as $i => $mail): ?><?= $i > 0 ? ' · ' : '' ?><a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a><?php endforeach; ?></dd></div>
            <?php endif; ?>
        </dl>
    </div>
</section>

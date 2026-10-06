<?php

use App\Services\Auth;

$reponse = 'mailto:' . rawurlencode((string) $message['email']) . '?subject=' . rawurlencode('Re : ' . $message['objet']);
?>
<div class="page-head">
    <div>
        <a class="back-link" href="<?= e(url('/admin/messages')) ?>"><?= icon('back', 16) ?> Retour aux messages</a>
        <h1 class="page-title">Message reçu</h1>
    </div>
    <div class="page-head__actions">
        <?php if ($voisins['prev']): ?><a class="icon-btn icon-btn--outline" href="<?= e(url('/admin/messages/' . $voisins['prev'])) ?>" aria-label="Message plus récent" title="Message plus récent"><svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg></a><?php endif; ?>
        <?php if ($voisins['next']): ?><a class="icon-btn icon-btn--outline" href="<?= e(url('/admin/messages/' . $voisins['next'])) ?>" aria-label="Message plus ancien" title="Message plus ancien"><?= icon('chevron', 18) ?></a><?php endif; ?>
    </div>
</div>

<div class="form-layout">
    <section class="card">
        <div class="card__body message">
            <div class="message__head">
                <div class="message__from">
                    <span class="avatar avatar--light" aria-hidden="true"><?= e(Auth::initials((string) $message['nom'])) ?></span>
                    <div>
                        <strong><?= e($message['nom']) ?></strong>
                        <span><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></span>
                    </div>
                </div>
                <span class="table__muted"><?= e(date_fr((string) $message['cree_le'], true)) ?></span>
            </div>
            <h2 class="message__subject"><?= e($message['objet']) ?></h2>
            <div class="message__body"><?= e($message['message']) ?></div>
            <p class="field__hint"><?= icon('check', 16) ?> L'expéditeur a consenti au traitement de ses données pour le traitement de sa demande.</p>
        </div>
    </section>
    <aside class="form-layout__side">
        <section class="card">
            <div class="card__head"><h2 class="card__title">Actions</h2></div>
            <div class="card__body stack">
                <a class="btn btn--primary btn--block" href="<?= e($reponse) ?>"><?= icon('mail', 18) ?> Répondre par e-mail</a>
                <form method="post" action="<?= e(url('/admin/messages/' . (int) $message['id'] . '/lu')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--secondary btn--block"><?= icon('undo', 18) ?> Marquer comme non lu</button>
                </form>
                <form method="post" action="<?= e(url('/admin/messages/' . (int) $message['id'] . '/supprimer')) ?>" data-confirm="Le message de <?= e($message['nom']) ?> sera supprimé définitivement." data-confirm-title="Supprimer ce message ?">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--danger btn--block"><?= icon('trash', 18) ?> Supprimer</button>
                </form>
            </div>
        </section>
    </aside>
</div>

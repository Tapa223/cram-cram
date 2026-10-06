/* CRAM-CRAM Mali — site public : menu mobile, onglets, apparition douce, retours de formulaire.
 * Le site reste entièrement utilisable sans JavaScript.
 */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

    document.addEventListener('DOMContentLoaded', function () {
        /* En-tête : ombre au défilement */
        var header = $('[data-header]');
        var onScroll = function () { if (header) { header.classList.toggle('is-scrolled', window.scrollY > 8); } };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        /* Menu mobile */
        var toggle = $('[data-nav-toggle]');
        var nav = $('[data-nav]');
        var setNav = function (open) {
            if (!toggle || !nav) { return; }
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        };
        if (toggle && nav) {
            toggle.addEventListener('click', function () { setNav(!nav.classList.contains('is-open')); });
            $$('a', nav).forEach(function (a) { a.addEventListener('click', function () { setNav(false); }); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && nav.classList.contains('is-open')) { setNav(false); toggle.focus(); }
            });
            window.addEventListener('resize', function () { if (window.innerWidth > 1120) { setNav(false); } });
        }

        /* Onglets (page Partenaires) */
        $$('[data-tabs]').forEach(function (box) {
            var tabs = $$('[role="tab"]', box);
            var panels = $$('[role="tabpanel"]', box);
            var select = function (tab, focus) {
                tabs.forEach(function (t) {
                    var on = t === tab;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                    t.tabIndex = on ? 0 : -1;
                });
                panels.forEach(function (p) { p.hidden = p.id !== tab.getAttribute('aria-controls'); });
                if (focus) { tab.focus(); }
            };
            tabs.forEach(function (tab, i) {
                tab.addEventListener('click', function () { select(tab, false); });
                tab.addEventListener('keydown', function (e) {
                    var next = null;
                    if (e.key === 'ArrowRight') { next = tabs[(i + 1) % tabs.length]; }
                    if (e.key === 'ArrowLeft') { next = tabs[(i - 1 + tabs.length) % tabs.length]; }
                    if (e.key === 'Home') { next = tabs[0]; }
                    if (e.key === 'End') { next = tabs[tabs.length - 1]; }
                    if (next) { e.preventDefault(); select(next, true); }
                });
            });
            if (tabs.length) { select(tabs[0], false); }
        });

        /* Flèche retour : page précédente si elle appartient au site */
        $$('[data-back]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var ref = document.referrer;
                if (ref && ref.indexOf(window.location.origin) === 0 && window.history.length > 1) {
                    e.preventDefault();
                    window.history.back();
                }
            });
        });

        /* Galerie : visionneuse avec navigation clavier et tactile */
        $$('[data-gallery]').forEach(function (box) {
            var links = $$('[data-gallery-item]', box);
            var dialog = $('[data-lightbox]', box);
            if (!dialog || typeof dialog.showModal !== 'function' || !links.length) { return; }
            var img = $('[data-lightbox-img]', dialog);
            var caption = $('[data-lightbox-caption]', dialog);
            var counter = $('[data-lightbox-counter]', dialog);
            var index = 0;
            var lastFocus = null;
            var show = function (i) {
                index = (i + links.length) % links.length;
                var link = links[index];
                img.src = link.getAttribute('href');
                img.alt = link.dataset.caption || '';
                caption.textContent = link.dataset.caption || '';
                counter.textContent = (index + 1) + ' / ' + links.length;
            };
            var single = links.length < 2;
            $('[data-lightbox-prev]', dialog).hidden = single;
            $('[data-lightbox-next]', dialog).hidden = single;
            links.forEach(function (link, i) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    lastFocus = link;
                    show(i);
                    dialog.showModal();
                    document.body.style.overflow = 'hidden';
                });
            });
            $('[data-lightbox-prev]', dialog).addEventListener('click', function () { show(index - 1); });
            $('[data-lightbox-next]', dialog).addEventListener('click', function () { show(index + 1); });
            $('[data-lightbox-close]', dialog).addEventListener('click', function () { dialog.close(); });
            dialog.addEventListener('click', function (e) { if (e.target === dialog) { dialog.close(); } });
            dialog.addEventListener('close', function () {
                document.body.style.overflow = '';
                if (lastFocus) { lastFocus.focus(); }
            });
            dialog.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') { show(index - 1); }
                if (e.key === 'ArrowRight') { show(index + 1); }
            });
            var startX = null;
            dialog.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
            dialog.addEventListener('touchend', function (e) {
                if (startX === null) { return; }
                var dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 50) { show(dx > 0 ? index - 1 : index + 1); }
                startX = null;
            });
        });

        /* Filtres appliqués au changement */
        $$('form[data-autosubmit] select').forEach(function (s) {
            s.addEventListener('change', function () { s.form.submit(); });
        });

        /* Envoi unique */
        $$('form[data-submit-once]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.sending === '1') { e.preventDefault(); return; }
                form.dataset.sending = '1';
                var btn = $('button[type="submit"]', form);
                if (btn) { btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true'); }
            });
        });

        /* Message de confirmation ou d'erreur : focus pour les lecteurs d'écran */
        var notice = $('[data-focus]');
        if (notice) { notice.focus({ preventScroll: false }); }

        /* Apparition douce au défilement */
        var items = $$('.reveal');
        if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
            items.forEach(function (el) { io.observe(el); });
        } else {
            items.forEach(function (el) { el.classList.add('is-visible'); });
        }
    });
})();

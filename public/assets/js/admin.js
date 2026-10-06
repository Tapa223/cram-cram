/* CRAM-CRAM Mali — administration.
 * Amélioration progressive : chaque formulaire fonctionne sans JavaScript,
 * ce script ajoute confirmations, sélection groupée, éditeur de texte et retours visuels.
 */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

    /* ---------- Menu latéral (mobile / tablette) ---------- */
    var sidebar = $('#sidebar');
    var backdrop = $('.sidebar-backdrop');
    var opener = $('[data-sidebar-open]');
    function setSidebar(open) {
        if (!sidebar) { return; }
        sidebar.classList.toggle('is-open', open);
        if (backdrop) { backdrop.hidden = !open; }
        if (opener) { opener.setAttribute('aria-expanded', open ? 'true' : 'false'); }
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
            var first = $('.sidebar__link', sidebar);
            if (first) { first.focus(); }
        } else if (opener && window.matchMedia('(max-width: 1080px)').matches) {
            opener.focus();
        }
    }
    if (opener) { opener.addEventListener('click', function () { setSidebar(true); }); }
    $$('[data-sidebar-close]').forEach(function (el) { el.addEventListener('click', function () { setSidebar(false); }); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) { setSidebar(false); }
    });

    /* ---------- Notifications ---------- */
    function closeToast(toast) {
        if (!toast || toast.classList.contains('is-leaving')) { return; }
        toast.classList.add('is-leaving');
        setTimeout(function () { toast.remove(); }, 260);
    }
    $$('[data-toast]').forEach(function (toast) {
        var delay = toast.classList.contains('toast--erreur') ? 12000 : 6000;
        var timer = setTimeout(function () { closeToast(toast); }, delay);
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        var btn = $('[data-toast-close]', toast);
        if (btn) { btn.addEventListener('click', function () { closeToast(toast); }); }
    });

    /* ---------- Confirmation des actions sensibles ---------- */
    var dialog = $('#confirm-dialog');
    function confirmThen(title, text, onConfirm) {
        if (!dialog || typeof dialog.showModal !== 'function') {
            if (window.confirm(text)) { onConfirm(); }
            return;
        }
        $('#confirm-title', dialog).textContent = title || 'Confirmer la suppression';
        $('#confirm-text', dialog).textContent = text;
        dialog.returnValue = '';
        dialog.showModal();
        dialog.addEventListener('close', function handler() {
            dialog.removeEventListener('close', handler);
            if (dialog.returnValue === 'confirmer') { onConfirm(); }
        });
    }
    $$('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.confirmed === '1') { return; }
            e.preventDefault();
            confirmThen(form.dataset.confirmTitle, form.dataset.confirm, function () {
                form.dataset.confirmed = '1';
                form.submit();
            });
        });
    });

    /* ---------- Sélection et actions groupées ---------- */
    var bulk = $('[data-bulk]');
    if (bulk) {
        var items = $$('[data-bulk-item]');
        var all = $('[data-bulk-all]');
        var countEl = $('[data-bulk-count]', bulk);
        var update = function () {
            var checked = items.filter(function (i) { return i.checked; });
            bulk.classList.toggle('is-visible', checked.length > 0);
            if (countEl) {
                countEl.textContent = checked.length + (checked.length > 1 ? ' éléments sélectionnés' : ' élément sélectionné');
            }
            items.forEach(function (i) {
                var row = i.closest('tr');
                if (row) { row.classList.toggle('is-selected', i.checked); }
            });
            if (all) {
                all.checked = checked.length > 0 && checked.length === items.length;
                all.indeterminate = checked.length > 0 && checked.length < items.length;
            }
        };
        items.forEach(function (i) { i.addEventListener('change', update); });
        if (all) {
            all.addEventListener('change', function () {
                items.forEach(function (i) { i.checked = all.checked; });
                update();
            });
        }
        var clear = $('[data-bulk-clear]', bulk);
        if (clear) {
            clear.addEventListener('click', function () {
                items.forEach(function (i) { i.checked = false; });
                update();
            });
        }
        bulk.addEventListener('submit', function (e) {
            var select = $('select[name="action"]', bulk);
            var option = select ? select.options[select.selectedIndex] : null;
            if (!option || !option.value) {
                e.preventDefault();
                if (select) { select.focus(); }
                return;
            }
            var trigger = $('[data-confirm-if-danger]', bulk);
            if (option.hasAttribute('data-danger') && bulk.dataset.confirmed !== '1') {
                e.preventDefault();
                confirmThen('Supprimer la sélection ?', trigger ? trigger.dataset.confirmIfDanger : 'Les éléments sélectionnés seront supprimés définitivement.', function () {
                    bulk.dataset.confirmed = '1';
                    bulk.submit();
                });
            }
        });
        update();
    }

    /* ---------- Filtres appliqués dès le changement ---------- */
    $$('form[data-autosubmit]').forEach(function (form) {
        $$('select', form).forEach(function (s) {
            s.addEventListener('change', function () { form.submit(); });
        });
    });

    /* ---------- Éditeur de texte enrichi ---------- */
    var tools = [
        ['bold', 'G', 'Gras'],
        ['italic', 'I', 'Italique'],
        ['sep'],
        ['h2', 'Titre', 'Intertitre'],
        ['h3', 'Sous-titre', 'Sous-intertitre'],
        ['p', 'Texte', 'Paragraphe normal'],
        ['sep'],
        ['insertUnorderedList', 'Liste', 'Liste à puces'],
        ['insertOrderedList', '1. Liste', 'Liste numérotée'],
        ['blockquote', 'Citation', 'Citation'],
        ['sep'],
        ['link', 'Lien', 'Insérer un lien'],
        ['unlink', 'Retirer le lien', 'Retirer le lien']
    ];
    $$('textarea[data-editor]').forEach(function (textarea) {
        if (typeof document.execCommand !== 'function') { return; }
        var wrap = document.createElement('div');
        wrap.className = 'editor';
        var bar = document.createElement('div');
        bar.className = 'editor__toolbar';
        bar.setAttribute('role', 'toolbar');
        bar.setAttribute('aria-label', 'Mise en forme du texte');
        var area = document.createElement('div');
        area.className = 'editor__area';
        area.contentEditable = 'true';
        area.setAttribute('role', 'textbox');
        area.setAttribute('aria-multiline', 'true');
        var label = textarea.id ? $('label[for="' + textarea.id + '"]') : null;
        if (label) {
            label.id = label.id || textarea.id + '-label';
            area.setAttribute('aria-labelledby', label.id);
            label.addEventListener('click', function () { area.focus(); });
        }
        area.dataset.placeholder = 'Rédigez le texte ici…';
        area.innerHTML = textarea.value;

        tools.forEach(function (t) {
            if (t[0] === 'sep') {
                var s = document.createElement('span');
                s.className = 'editor__sep';
                bar.appendChild(s);
                return;
            }
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'editor__btn';
            b.textContent = t[1];
            b.title = t[2];
            b.setAttribute('aria-label', t[2]);
            b.addEventListener('mousedown', function (e) { e.preventDefault(); });
            b.addEventListener('click', function () {
                area.focus();
                if (t[0] === 'link') {
                    var url = window.prompt('Adresse du lien (https://…)', 'https://');
                    if (url && /^(https?:\/\/|mailto:)/i.test(url)) { document.execCommand('createLink', false, url); }
                } else if (['h2', 'h3', 'p', 'blockquote'].indexOf(t[0]) !== -1) {
                    document.execCommand('formatBlock', false, t[0]);
                } else {
                    document.execCommand(t[0], false, null);
                }
                sync();
            });
            bar.appendChild(b);
        });

        function sync() {
            textarea.value = area.innerHTML.trim() === '<br>' ? '' : area.innerHTML;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }
        area.addEventListener('input', sync);
        area.addEventListener('paste', function (e) {
            // Collage en texte simple : évite d'importer des styles externes.
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });
        document.execCommand('defaultParagraphSeparator', false, 'p');

        wrap.appendChild(bar);
        wrap.appendChild(area);
        textarea.classList.add('is-enhanced');
        textarea.parentNode.insertBefore(wrap, textarea.nextSibling);
        if (textarea.form) { textarea.form.addEventListener('submit', sync); }
    });

    /* ---------- Compteurs de caractères ---------- */
    $$('[data-counter-for]').forEach(function (counter) {
        var field = document.getElementById(counter.dataset.counterFor);
        if (!field) { return; }
        var max = parseInt(field.dataset.maxlength || field.getAttribute('maxlength') || '0', 10);
        var render = function () {
            var n = field.value.length;
            counter.textContent = n + ' / ' + max;
            counter.classList.toggle('is-over', max > 0 && n > max);
        };
        field.addEventListener('input', render);
        render();
    });

    /* ---------- Modifications non enregistrées ---------- */
    $$('form[data-dirty-watch]').forEach(function (form) {
        var status = $('[data-dirty-status]', form);
        var initial = status ? status.innerHTML : '';
        var dirty = false;
        var mark = function () {
            if (dirty) { return; }
            dirty = true;
            if (status) {
                status.textContent = 'Modifications non enregistrées';
                status.classList.add('is-dirty');
            }
        };
        form.addEventListener('input', mark);
        form.addEventListener('change', mark);
        form.addEventListener('submit', function () { dirty = false; });
        window.addEventListener('beforeunload', function (e) {
            if (dirty) { e.preventDefault(); e.returnValue = ''; }
        });
        form.addEventListener('reset', function () {
            dirty = false;
            if (status) { status.innerHTML = initial; status.classList.remove('is-dirty'); }
        });
    });

    /* ---------- Envoi unique (bouton désactivé pendant l'envoi) ---------- */
    $$('form[data-submit-once]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.sending === '1') { e.preventDefault(); return; }
            form.dataset.sending = '1';
            var btn = e.submitter || $('button[type="submit"]', form);
            if (btn) {
                btn.classList.add('is-loading');
                btn.setAttribute('aria-busy', 'true');
            }
        });
    });

    /* ---------- Zone de dépôt de fichier ---------- */
    $$('[data-dropzone]').forEach(function (zone) {
        var input = $('input[type="file"]', zone);
        var label = $('[data-dropzone-label]', zone);
        if (!input) { return; }
        input.addEventListener('change', function () {
            if (!label || !input.files.length) { return; }
            label.textContent = input.files.length === 1
                ? 'Fichier choisi : ' + input.files[0].name
                : input.files.length + ' fichiers choisis';
        });
        ['dragenter', 'dragover'].forEach(function (ev) {
            zone.addEventListener(ev, function () { zone.classList.add('is-over'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            zone.addEventListener(ev, function () { zone.classList.remove('is-over'); });
        });
    });

    /* ---------- Liste à choix multiples filtrable ---------- */
    $$('[data-multi]').forEach(function (box) {
        var filter = $('[data-multi-filter]', box);
        if (!filter) { return; }
        filter.addEventListener('input', function () {
            var q = filter.value.trim().toLowerCase();
            $$('[data-multi-item]', box).forEach(function (item) {
                item.hidden = q !== '' && item.textContent.toLowerCase().indexOf(q) === -1;
            });
        });
        filter.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });
    });

    /* ---------- Afficher / masquer le mot de passe ---------- */
    $$('[data-toggle-password]').forEach(function (btn) {
        var input = document.getElementById(btn.dataset.togglePassword);
        if (!input) { return; }
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    });
})();

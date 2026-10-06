<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Nettoie le contenu riche saisi dans l'administration (liste blanche stricte).
 * Seules quelques balises de mise en forme sont conservées ; tout attribut est retiré,
 * sauf href (http, https, mailto) sur les liens. Scripts, styles, iframes : supprimés.
 */
final class ContentSanitizer
{
    private const ALLOWED = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'a', 'blockquote'];
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'noscript', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta', 'head', 'title'];
    private const RENAME = ['div' => 'p', 'h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4', 'b' => 'strong', 'i' => 'em'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Texte brut sans balise : conversion en paragraphes.
        if ($html === strip_tags($html)) {
            $paragraphs = preg_split('/\R{2,}/', $html) ?: [];
            $out = '';
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if ($paragraph !== '') {
                    $out .= '<p>' . nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8'), false) . '</p>';
                }
            }
            return $out;
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__root');
        if (!$root) {
            return '';
        }
        self::walk($root, $doc);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        // Retire les paragraphes vides laissés par l'éditeur.
        $out = preg_replace('#<p>(\s|&nbsp;|<br>)*</p>#u', '', $out) ?? '';
        return trim($out);
    }

    private static function walk(\DOMNode $node, \DOMDocument $doc): void
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            if (isset(self::RENAME[$tag])) {
                $renamed = $doc->createElement(self::RENAME[$tag]);
                while ($child->firstChild) {
                    $renamed->appendChild($child->firstChild);
                }
                $node->replaceChild($renamed, $child);
                $child = $renamed;
                $tag = self::RENAME[$tag];
            }

            self::walk($child, $doc);

            if (!in_array($tag, self::ALLOWED, true)) {
                // Balise non autorisée : on garde son texte, on retire la balise.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            $href = $tag === 'a' ? trim($child->getAttribute('href')) : '';
            while ($child->attributes->length > 0) {
                $child->removeAttribute($child->attributes->item(0)->nodeName);
            }
            if ($tag === 'a') {
                if ($href !== '' && (is_safe_url($href) || preg_match('/^mailto:[^\s<>"]+@[^\s<>"]+$/i', $href))) {
                    $child->setAttribute('href', $href);
                    if (str_starts_with(strtolower($href), 'http')) {
                        $child->setAttribute('rel', 'noopener noreferrer');
                        $child->setAttribute('target', '_blank');
                    }
                } else {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                }
            }
        }
    }
}

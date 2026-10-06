<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Rendu des vues PHP. Toute donnée affichée doit passer par e() (échappement HTML),
 * sauf le contenu riche déjà nettoyé par ContentSanitizer.
 */
final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $content = self::capture($view, $data);
        if ($layout === null) {
            return $content;
        }
        return self::capture($layout, array_merge($data, ['content' => $content]));
    }

    /** @param array<string, mixed> $data */
    public static function partial(string $view, array $data = []): string
    {
        return self::capture($view, $data);
    }

    /** @param array<string, mixed> $data */
    private static function capture(string $view, array $data): string
    {
        $file = APP_ROOT . '/app/Views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('Vue introuvable : ' . $view);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}

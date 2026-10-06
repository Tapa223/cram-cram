<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routeur maison : routes statiques et paramètres dynamiques {nom}.
 * Un paramètre {id} n'accepte que des chiffres ; les autres acceptent [a-z0-9-].
 */
final class Router
{
    /** @var list<array{method: string, pattern: string, regex: string, handler: array{0: class-string, 1: string}, middleware: list<class-string>}> */
    private array $routes = [];

    /** @var list<class-string> */
    private array $groupMiddleware = [];

    private string $groupPrefix = '';

    /** @param array{0: class-string, 1: string} $handler @param list<class-string> $middleware */
    public function get(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    /** @param array{0: class-string, 1: string} $handler @param list<class-string> $middleware */
    public function post(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    /** @param list<class-string> $middleware */
    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;
        $this->groupPrefix .= $prefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $callback($this);
        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /** @param array{0: class-string, 1: string} $handler @param list<class-string> $middleware */
    private function add(string $method, string $pattern, array $handler, array $middleware): void
    {
        $full = '/' . trim($this->groupPrefix . $pattern, '/');
        $regex = preg_replace_callback('#\{([a-z_]+)\}#', static function (array $m): string {
            $segment = $m[1] === 'id' ? '\d+' : '[a-z0-9-]+';
            return '(?P<' . $m[1] . '>' . $segment . ')';
        }, $full);
        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $full,
            'regex'      => '#^' . $regex . '$#',
            'handler'    => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $allowed = [];
        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $allowed[] = $route['method'];
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $middlewareClass) {
                (new $middlewareClass())->handle();
            }

            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->{$action}(...array_values($params));
            return;
        }

        if ($allowed !== []) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowed)));
        }
        throw new HttpException(404);
    }
}

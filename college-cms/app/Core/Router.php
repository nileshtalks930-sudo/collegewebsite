<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, list<array{pattern:string,regex:string,names:list<string>,action:array{0:class-string,1:string}}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $uri, array $action): void
    {
        $this->add('GET', $uri, $action);
    }

    public function post(string $uri, array $action): void
    {
        $this->add('POST', $uri, $action);
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = $this->normalize($uri);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            $params = [];
            foreach ($route['names'] as $name) {
                $params[$name] = $matches[$name] ?? null;
            }

            [$class, $methodName] = $route['action'];
            if (!class_exists($class)) {
                http_response_code(500);
                echo 'Controller not found';
                return;
            }

            $controller = new $class();
            if (!method_exists($controller, $methodName)) {
                http_response_code(500);
                echo 'Action not found';
                return;
            }

            $controller->$methodName(...array_values($params));
            return;
        }

        http_response_code(404);
        echo '404 — Page not found';
    }

    private function add(string $method, string $uri, array $action): void
    {
        $pattern = $this->normalize($uri);
        $names = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $m) use (&$names): string {
            $names[] = $m[1];
            return '(?P<' . $m[1] . '>[^/]+)';
        }, $pattern);

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex' => '#^' . $regex . '$#',
            'names' => $names,
            'action' => $action,
        ];
    }

    private function normalize(string $uri): string
    {
        $uri = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }
}

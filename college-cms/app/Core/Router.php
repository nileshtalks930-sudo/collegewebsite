<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, array{0:class-string,1:string}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = $this->normalize($uri);

        $action = $this->routes[$method][$uri] ?? null;
        if ($action === null) {
            http_response_code(404);
            echo '404 — Page not found';
            return;
        }

        [$class, $methodName] = $action;
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

        $controller->$methodName();
    }

    private function normalize(string $uri): string
    {
        $uri = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }
}

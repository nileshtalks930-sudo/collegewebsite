<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $path, array $data = [], ?string $layout = null): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $path) . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
            return;
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $layout) . '.php';
        if (!is_file($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }

    protected function redirect(string $path): never
    {
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            $path = url($path);
        }

        header('Location: ' . $path);
        exit;
    }

    protected function validateCsrf(): void
    {
        $token = $_POST['_token'] ?? null;

        if ((!is_string($token) || $token === '') && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        $body = $this->jsonBody();
        if ((!is_string($token) || $token === '') && isset($body['_token']) && is_string($body['_token'])) {
            $token = $body['_token'];
        }

        if (!Csrf::validate(is_string($token) ? $token : null)) {
            http_response_code(419);
            if ($this->wantsJson()) {
                $this->json(['ok' => false, 'message' => 'Invalid security token.'], 419);
            }
            Session::flash('error', 'Invalid security token. Please try again.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/login');
        }
    }

    protected function jsonBody(): array
    {
        if (isset($GLOBALS['__json_body']) && is_array($GLOBALS['__json_body'])) {
            return $GLOBALS['__json_body'];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (!str_contains(strtolower($contentType), 'application/json')) {
            $GLOBALS['__json_body'] = [];
            return [];
        }

        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        $GLOBALS['__json_body'] = is_array($json) ? $json : [];

        return $GLOBALS['__json_body'];
    }

    protected function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || str_contains($_SERVER['REQUEST_URI'] ?? '', '/reorder');
    }

    /** @param array<string,mixed> $payload */
    protected function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

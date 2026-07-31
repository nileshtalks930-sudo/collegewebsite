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
        if (!Csrf::validate(is_string($token) ? $token : null)) {
            http_response_code(419);
            Session::flash('error', 'Invalid security token. Please try again.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/login');
        }
    }

    protected function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

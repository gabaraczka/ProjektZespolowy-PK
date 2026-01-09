<?php

namespace App\Config;

class Router {
    private array $routes = [];

    public function addRoute(string $method, string $path, string $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function handleRequest(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {

                if ($route['path'] === $requestPath) {
                    $this->executeRoute($route['handler']);
                    return;
                }
                
                $params = $this->matchParameterizedRoute($route['path'], $requestPath);
                if ($params !== false) {
                    $this->executeRoute($route['handler'], $params);
                    return;
                }
            }
        }

        if ($this->isStaticFile($requestPath)) {
            $this->serveStaticFile($requestPath);
            return;
        }

        if ($this->isUploadedFile($requestPath)) {
            $this->serveUploadedFile($requestPath);
            return;
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    private function matchParameterizedRoute(string $routePath, string $requestPath): array|false {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches);
            return $matches;
        }
        
        return false;
    }

    private function executeRoute(string $handler, array $params = []): void {
        [$controller, $method] = explode('@', $handler);
        $controllerClass = "App\\Controller\\{$controller}";
        
        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $method)) {
                call_user_func_array([$controllerInstance, $method], $params);
                return;
            }
        }
        
        http_response_code(500);
        echo "Controller or method not found";
    }

    private function isStaticFile(string $path): bool {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico']);
    }

    private function isUploadedFile(string $path): bool {
        return strpos($path, '/uploads/') === 0;
    }

    private function serveStaticFile(string $path): void {
        $file = __DIR__ . '/../../public' . $path;
        if (file_exists($file)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon'
            ];
            
            header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
            readfile($file);
            exit;
        }
    }

    private function serveUploadedFile(string $path): void {
        $file = __DIR__ . '/../../' . ltrim($path, '/');
        if (file_exists($file)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif'
            ];
            
            header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
            header('Content-Disposition: inline; filename="' . basename($file) . '"');
            readfile($file);
            exit;
        }
    }
} 
<?php

class Router
{
    private array $routes = [];

    public function register(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $this->convertPatternToRegex($pattern),
            'variables' => $this->extractVariables($pattern),
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                $params = [];
                foreach ($route['variables'] as $variable) {
                    if (isset($matches[$variable])) {
                        $params[$variable] = $matches[$variable];
                    }
                }

                call_user_func($route['handler'], $params);
                return;
            }
        }

        Response::json(['error' => 'Ruta no encontrada'], 404);
    }

    private function convertPatternToRegex(string $pattern): string
    {
        $escaped = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . rtrim($escaped, '/') . '/?$#';
    }

    private function extractVariables(string $pattern): array
    {
        preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $pattern, $matches);
        return $matches[1];
    }
}

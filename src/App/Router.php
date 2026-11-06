<?php

declare(strict_types=1);

namespace Indx\App;

/**
 * Path in, handler out. A route may carry one placeholder segment written as
 * {name}, and the matched value reaches the handler by that name. The base
 * prefix is stripped once, here.
 */
final class Router
{
    /** @var list<array{pattern: string, handler: callable}> */
    private array $routes = [];

    public function __construct(private readonly string $base = '/')
    {
    }

    public function get(string $pattern, callable $handler): void
    {
        $this->routes[] = ['pattern' => $this->normalise($pattern), 'handler' => $handler];
    }

    private function normalise(string $pattern): string
    {
        $pattern = '/' . trim($pattern, '/');

        return $pattern === '/' ? '/' : $pattern . '/';
    }

    public function strip(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim($this->base, '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        return '/' . trim($uri, '/');
    }

    /** @return array{0: int, 1: array<string, mixed>} */
    public function handle(string $method, string $uri): array
    {
        $path = $this->strip($uri);
        $key = $this->normalise($path);

        foreach ($this->routes as $route) {
            $args = $this->match($route['pattern'], $key);
            if ($args !== null) {
                return (array) ($route['handler'])(...$args);
            }
        }

        return [404, ['error' => 'not found', 'path' => $path]];
    }

    /** @return list<string>|null */
    private function match(string $pattern, string $key): ?array
    {
        if (!str_contains($pattern, '{')) {
            return $pattern === $key ? [] : null;
        }

        $regex = '#^' . preg_replace('/\{[a-z]+\}/', '([^/]+)', $pattern) . '$#';
        if (preg_match($regex, $key, $m) !== 1) {
            return null;
        }
        array_shift($m);

        return array_map('urldecode', $m);
    }
}

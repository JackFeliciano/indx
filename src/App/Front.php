<?php

declare(strict_types=1);

namespace Indx\App;

use Indx\Card\Issuer;
use Indx\Card\Sample;

/**
 * The front controller. The registry is built once and handed to the routes, so
 * a reader sees the same card on every page.
 */
final class Front
{
    public function __construct(private readonly string $base)
    {
    }

    public function run(): void
    {
        $router = new Router($this->base);
        $views = new Views($this->base);
        $registry = Sample::registry();
        $today = Sample::today();

        $router->get('/', static fn (): array => [
            200, ['html' => $views->card($registry->card('sample-holder'), $today)],
        ]);

        $router->get('/card/{handle}', static function (string $handle) use ($registry, $views, $today): array {
            if (!$registry->has($handle)) {
                return [404, ['html' => $views->notFound($handle)]];
            }

            return [200, ['html' => $views->card($registry->card($handle), $today)]];
        });

        $router->get('/answer/{handle}/{verifier}', static function (
            string $handle,
            string $verifier,
        ) use ($registry, $views, $today): array {
            if (!$registry->has($handle)) {
                return [404, ['html' => $views->notFound($handle)]];
            }

            return [200, ['html' => $views->answer($registry->answer($handle, $verifier, $today))]];
        });

        $router->get('/health', static fn (): array => [
            200, ['json' => ['ok' => true, 'cards' => $registry->count(), 'today' => $today]],
        ]);

        [$status, $body] = $router->handle(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
        );

        http_response_code($status);
        if (isset($body['json'])) {
            header('Content-Type: application/json');
            echo json_encode($body['json'], JSON_UNESCAPED_SLASHES), "\n";

            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $body['html'] ?? $views->notFound((string) ($body['path'] ?? '/'));
    }

    /** The registry is rebuilt per call in tests, so this keeps the seam visible. */
    public static function registry(): Issuer
    {
        return Sample::registry();
    }
}

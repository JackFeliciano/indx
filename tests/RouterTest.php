<?php

declare(strict_types=1);

use Indx\App\Router;
use Indx\Tests\Assert;

return [
    ['a plain route matches', static function (): void {
        $router = new Router('/indx/');
        $router->get('/health', static fn (): array => [200, ['html' => 'ok']]);
        Assert::same(200, $router->handle('GET', '/indx/health')[0]);
    }],
    ['a placeholder carries its value to the handler', static function (): void {
        $router = new Router('/');
        $seen = '';
        $router->get('/card/{handle}', static function (string $handle) use (&$seen): array {
            $seen = $handle;

            return [200, ['html' => 'card']];
        });
        Assert::same(200, $router->handle('GET', '/card/sample-holder')[0]);
        Assert::same('sample-holder', $seen);
    }],
    ['two placeholders carry both values', static function (): void {
        $router = new Router('/');
        $seen = [];
        $router->get('/answer/{handle}/{verifier}', static function (string $h, string $v) use (&$seen): array {
            $seen = [$h, $v];

            return [200, ['html' => 'answer']];
        });
        $router->handle('GET', '/answer/sample-holder/Borderless%20Bank');
        Assert::same(['sample-holder', 'Borderless Bank'], $seen);
    }],
    ['a prefix is stripped before matching', static function (): void {
        $router = new Router('/indx/');
        $router->get('/card/{handle}', static fn (string $h): array => [200, ['html' => $h]]);
        Assert::same('x', $router->handle('GET', '/indx/card/x')[1]['html']);
    }],
    ['an unknown path is a 404 that names it', static function (): void {
        $router = new Router('/');
        [$status, $body] = $router->handle('GET', '/nowhere');
        Assert::same(404, $status);
        Assert::same('/nowhere', $body['path']);
    }],
    ['a placeholder does not span a slash', static function (): void {
        $router = new Router('/');
        $router->get('/card/{handle}', static fn (string $h): array => [200, ['html' => $h]]);
        Assert::same(404, $router->handle('GET', '/card/a/b')[0]);
    }],
];

<?php

declare(strict_types=1);

/**
 * Classes load from src/ by namespace, and the test helpers load from tests/.
 * No Composer, no vendor directory.
 */
spl_autoload_register(static function (string $class): void {
    $sep = chr(92);
    $prefix = 'Indx' . $sep . 'Tests' . $sep;
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/tests/' . str_replace($sep, '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

spl_autoload_register(static function (string $class): void {
    $sep = chr(92);
    $prefix = 'Indx' . $sep;
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace($sep, '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

<?php

declare(strict_types=1);

require __DIR__ . '/../autoload.php';

use Indx\App\Front;

$base = getenv('INDX_BASE') ?: '/';

// The built-in server sends every request to this file, so the stylesheet needs
// its own branch before the router sees the path.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$prefix = rtrim($base, '/');
if ($prefix !== '' && str_starts_with($path, $prefix)) {
    $path = substr($path, strlen($prefix));
}
if (preg_match('#^/assets/(site\.css|holder\.jpg)$#', $path, $asset) === 1) {
    $types = ['site.css' => 'text/css; charset=utf-8', 'holder.jpg' => 'image/jpeg'];
    header('Content-Type: ' . $types[$asset[1]]);
    header('Cache-Control: public, max-age=300');
    readfile(__DIR__ . '/assets/' . $asset[1]);

    return;
}

(new Front($base))->run();

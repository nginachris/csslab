<?php
declare(strict_types=1);

$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = trim($requestedPath, '/');

if ($path !== '') {
    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . $path;
    if (is_file($absolutePath)) {
        return false;
    }
}

$routes = [
    '' => 'index.html',
    'index' => 'index.html',
    'courses' => 'courses.html',
    'resources' => 'resources.html',
    'gallery' => 'gallery.html',
    'register' => 'register.php',
    'setup' => 'setup.php',
    'register.html' => 'register.php',
    'home' => 'index.html',
];

$target = $routes[$path] ?? 'index.html';
$targetPath = __DIR__ . DIRECTORY_SEPARATOR . $target;

if (!is_file($targetPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Requested resource was not found.';
    exit;
}

if (str_ends_with($target, '.php')) {
    require $targetPath;
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
readfile($targetPath);

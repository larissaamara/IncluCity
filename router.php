<?php

declare(strict_types=1);

// O servidor embutido do PHP não interpreta as regras do .htaccess.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$path = str_replace('\\', '/', $path);
$file = realpath(__DIR__ . $path);
$root = str_replace('\\', '/', __DIR__) . '/';
$relative = $file === false ? '' : substr(str_replace('\\', '/', $file), strlen($root));

if (preg_match('~(?:^|/)\.|\x00~', $path)
    || ($file !== false && !str_starts_with(str_replace('\\', '/', $file) . '/', $root))
    || ($path !== '/' && !preg_match('~^(?:(?:pages|actions|api)/.*\.php|assets/.*\.(?:css|js|png|jpg|jpeg|webp|gif|svg|ico|woff2?|ttf)|(?:index|oauth)\.php)$~i', $relative))) {
    http_response_code(404);
    exit('Página não encontrada.');
}

return false;

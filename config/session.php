<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfToken(): string
{
    return $_SESSION['csrf_token'];
}

function csrfValido(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function usuarioAutenticado(): bool
{
    return isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] > 0;
}
 
function paginaDaConta(): string
{
    return ($_SESSION['tipo_usuario'] ?? 'usuario') === 'admin'
        ? 'admin.php'
        : 'TelaUsuario.php';
}

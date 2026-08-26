<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

function usuarioEhAdmin(): bool
{
    return isset($_SESSION['usuario_id']) && ($_SESSION['tipo_usuario'] ?? '') === 'admin';
}

function exigirAdmin(bool $json = false): void
{
    if (usuarioEhAdmin()) {
        return;
    }

    if ($json) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(403);
        echo json_encode(['erro' => 'Acesso permitido somente para administradores.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Location: login.php');
    exit;
}

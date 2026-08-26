<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/admin.php';
require_once dirname(__DIR__) . '/config/conn.php';

exigirAdmin(true);
header('Content-Type: application/json; charset=UTF-8');

function responderUsuario(array $dados, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderUsuario(['erro' => 'Método não permitido.'], 405);
}
if (!csrfValido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    responderUsuario(['erro' => 'Sessão expirada. Atualize a página.'], 403);
}

$dados = json_decode((string) file_get_contents('php://input'), true);
$usuarioId = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
$tipo = (string) ($dados['tipo_usuario'] ?? '');
if (!$usuarioId || !in_array($tipo, ['usuario', 'admin'], true)) {
    responderUsuario(['erro' => 'Dados inválidos.'], 422);
}
if ($usuarioId === (int) $_SESSION['usuario_id'] && $tipo !== 'admin') {
    responderUsuario(['erro' => 'Você não pode remover sua própria permissão administrativa.'], 422);
}

$stmt = $con->prepare('UPDATE usuarios SET tipo_usuario = ? WHERE id = ?');
$stmt->bind_param('si', $tipo, $usuarioId);
$stmt->execute();
if ($stmt->affected_rows === 0) {
    $verificar = $con->prepare('SELECT id FROM usuarios WHERE id = ?');
    $verificar->bind_param('i', $usuarioId);
    $verificar->execute();
    if (!$verificar->get_result()->fetch_assoc()) responderUsuario(['erro' => 'Usuário não encontrado.'], 404);
}
$stmt->close();
responderUsuario(['sucesso' => true, 'mensagem' => $tipo === 'admin' ? 'Usuário promovido a administrador.' : 'Permissão administrativa removida.']);

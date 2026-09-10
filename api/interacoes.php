<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/conn.php';
header('Content-Type: application/json; charset=UTF-8');

function jsonResposta(array $dados, int $status = 200): never {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$localId = filter_input(INPUT_GET, 'local_id', FILTER_VALIDATE_INT)
    ?: filter_var($_POST['local_id'] ?? null, FILTER_VALIDATE_INT);
if (!$localId) jsonResposta(['erro' => 'Local inválido.'], 422);

$verificar = $con->prepare("SELECT id FROM locais WHERE id = ? AND status = 'aprovado'");
$verificar->bind_param('i', $localId); $verificar->execute();
if (!$verificar->get_result()->fetch_assoc()) jsonResposta(['erro' => 'Local não encontrado.'], 404);
$verificar->close();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $con->prepare('SELECT a.usuario_id, a.nota, a.comentario, a.data_atualizacao, u.nome AS usuario FROM avaliacoes a JOIN usuarios u ON u.id = a.usuario_id WHERE a.local_id = ? ORDER BY a.data_atualizacao DESC');
    $stmt->bind_param('i', $localId); $stmt->execute();
    $avaliacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    $usuarioId = usuarioAutenticado() ? (int) $_SESSION['usuario_id'] : 0;
    $minhaAvaliacao = null;
    $soma = 0;
    foreach ($avaliacoes as &$item) {
        $item['nota'] = (int) $item['nota'];
        $soma += $item['nota'];
        if ((int) $item['usuario_id'] === $usuarioId) {
            $minhaAvaliacao = ['nota' => $item['nota'], 'comentario' => (string) $item['comentario']];
            $item['minha'] = true;
        } else {
            $item['minha'] = false;
        }
        unset($item['usuario_id']);
    }
    unset($item);
    $total = count($avaliacoes);
    jsonResposta([
        'resumo' => ['media' => $total ? round($soma / $total, 1) : 0, 'total' => $total],
        'minha_avaliacao' => $minhaAvaliacao,
        'avaliacoes' => $avaliacoes,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResposta(['erro' => 'Método não permitido.'], 405);
if (!usuarioAutenticado()) jsonResposta(['erro' => 'Faça login para avaliar este local.'], 401);
if (!csrfValido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) jsonResposta(['erro' => 'Sua sessão expirou. Atualize a página.'], 403);

$nota = filter_var($_POST['nota'] ?? null, FILTER_VALIDATE_INT);
$comentario = trim((string) ($_POST['comentario'] ?? ''));
if (!$nota || $nota < 1 || $nota > 5) jsonResposta(['erro' => 'Escolha uma nota de 1 a 5 estrelas.'], 422);
if (mb_strlen($comentario) > 1500) jsonResposta(['erro' => 'O comentário deve ter no máximo 1.500 caracteres.'], 422);

$usuarioId = (int) $_SESSION['usuario_id'];
$stmt = $con->prepare('INSERT INTO avaliacoes (local_id, usuario_id, nota, comentario) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nota = VALUES(nota), comentario = VALUES(comentario)');
$stmt->bind_param('iiis', $localId, $usuarioId, $nota, $comentario);
$stmt->execute(); $stmt->close();
jsonResposta(['sucesso' => true, 'mensagem' => 'Sua avaliação foi publicada.'], 201);

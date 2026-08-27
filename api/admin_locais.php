<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/admin.php';
require_once dirname(__DIR__) . '/config/conn.php';

exigirAdmin(true);
header('Content-Type: application/json; charset=UTF-8');

function responderAdmin(array $dados, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    responderAdmin(['erro' => 'Método não permitido.'], 405);
}

if (!csrfValido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    responderAdmin(['erro' => 'Sessão expirada. Atualize a página.'], 403);
}

$dados = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
    responderAdmin(['erro' => 'JSON inválido.'], 400);
}

$localId = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
$acao = (string) ($dados['acao'] ?? '');

if (!$localId || !in_array($acao, ['aprovar', 'recusar', 'excluir'], true)) {
    responderAdmin(['erro' => 'Solicitação inválida.'], 422);
}

$transacaoAtiva = false;
try {
    $stmt = $con->prepare('SELECT id FROM locais WHERE id = ?');
    $stmt->bind_param('i', $localId);
    $stmt->execute();
    $local = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$local) {
        responderAdmin(['erro' => 'Local não encontrado.'], 404);
    }

    if ($acao === 'aprovar' || $acao === 'recusar') {
        $novoStatus = $acao === 'aprovar' ? 'aprovado' : 'reprovado';
        $stmt = $con->prepare('UPDATE locais SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $novoStatus, $localId);
        $stmt->execute();
        $stmt->close();

        $mensagem = $acao === 'aprovar'
            ? 'Local aprovado e publicado no mapa.'
            : 'Solicitação recusada e retirada do mapa.';
        responderAdmin(['sucesso' => true, 'mensagem' => $mensagem]);
    }

    $con->begin_transaction();
    $transacaoAtiva = true;

    $stmt = $con->prepare('SELECT arquivo FROM local_fotos WHERE local_id = ? FOR UPDATE');
    $stmt->bind_param('i', $localId);
    $stmt->execute();
    $fotos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $con->prepare('DELETE FROM locais WHERE id = ?');
    $stmt->bind_param('i', $localId);
    $stmt->execute();
    $stmt->close();
    $con->commit();
    $transacaoAtiva = false;

    $pastaUploads = realpath(dirname(__DIR__) . '/assets/uploads/solicitacoes');
    if ($pastaUploads !== false) {
        foreach ($fotos as $foto) {
            $arquivo = basename((string) $foto['arquivo']);
            $caminho = $pastaUploads . DIRECTORY_SEPARATOR . $arquivo;
            if (is_file($caminho) && !@unlink($caminho)) {
                error_log('Não foi possível excluir a foto do local: ' . $caminho);
            }
        }
    }

    responderAdmin(['sucesso' => true, 'mensagem' => 'Solicitação excluída.']);
} catch (Throwable $erro) {
    if ($transacaoAtiva) {
        $con->rollback();
    }
    error_log('Erro ao administrar local: ' . $erro->getMessage());
    responderAdmin(['erro' => 'Não foi possível concluir a ação.'], 500);
}

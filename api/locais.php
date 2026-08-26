<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/conn.php';

header('Content-Type: application/json; charset=UTF-8');

function responder(array $dados, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $resultado = $con->query(
        "SELECT id, nome, endereco, numero, bairro, cidade, estado, latitude, longitude,
                categorias, deficiencias, recursos, observacoes, site, instagram, telefone, horario_funcionamento
         FROM locais WHERE status = 'aprovado' ORDER BY data_cadastro DESC"
    );
    $locais = [];
    while ($linha = $resultado->fetch_assoc()) {
        $linha['id'] = (int) $linha['id'];
        $linha['lat'] = (float) $linha['latitude'];
        $linha['lng'] = (float) $linha['longitude'];
        $linha['categorias'] = json_decode($linha['categorias'], true) ?: [];
        $linha['deficiencias'] = json_decode($linha['deficiencias'] ?? '[]', true) ?: [];
        $linha['recursos'] = json_decode($linha['recursos'], true) ?: [];
        unset($linha['latitude'], $linha['longitude']);
        $locais[] = $linha;
    }
    responder(['locais' => $locais]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['erro' => 'Método não permitido.'], 405);
}

if (!isset($_SESSION['usuario_id']) || (int) $_SESSION['usuario_id'] <= 0) {
    responder(['erro' => 'Faça login para contribuir com o IncluCity.'], 401);
}

if (!csrfValido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    responder(['erro' => 'Solicitação inválida. Atualize a página.'], 403);
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$endereco = trim((string) ($_POST['endereco'] ?? ''));
$numero = trim((string) ($_POST['numero'] ?? ''));
$complemento = trim((string) ($_POST['complemento'] ?? ''));
$bairro = trim((string) ($_POST['bairro'] ?? ''));
$cidade = trim((string) ($_POST['cidade'] ?? ''));
$estado = strtoupper(trim((string) ($_POST['estado'] ?? '')));
$cep = preg_replace('/\D/', '', (string) ($_POST['cep'] ?? '')) ?? '';
$latitude = filter_var($_POST['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_POST['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
$categorias = array_values(array_filter($_POST['categorias'] ?? [], 'is_string'));
$deficienciasPermitidas = ['fisica', 'visual', 'auditiva', 'cognitiva'];
$deficiencias = array_values(array_intersect(
    array_filter($_POST['deficiencias'] ?? [], 'is_string'),
    $deficienciasPermitidas
));
$recursos = array_values(array_filter($_POST['recursos'] ?? [], 'is_string'));
$outraCategoria = trim((string) ($_POST['outra_categoria'] ?? ''));
$outroRecurso = trim((string) ($_POST['outro_recurso'] ?? ''));
$observacoes = trim((string) ($_POST['observacoes'] ?? ''));
$site = trim((string) ($_POST['site'] ?? ''));
$instagram = trim((string) ($_POST['instagram'] ?? ''));
$telefone = trim((string) ($_POST['telefone'] ?? ''));
$horario = trim((string) ($_POST['horario_funcionamento'] ?? ''));

if (mb_strlen($nome) < 3 || mb_strlen($nome) > 150 || mb_strlen($endereco) < 3
    || $numero === '' || $bairro === '' || $cidade === '' || !preg_match('/^[A-Z]{2}$/', $estado)
    || strlen($cep) !== 8 || $latitude === false || $longitude === false
    || !$categorias || !$deficiencias || !$recursos || empty($_POST['declaracao'])) {
    responder(['erro' => 'Preencha todos os campos obrigatórios e confirme a declaração.'], 422);
}

if (in_array('Outro', $categorias, true) && $outraCategoria === '') {
    responder(['erro' => 'Especifique a outra categoria.'], 422);
}
if (in_array('Outro', $recursos, true) && $outroRecurso === '') {
    responder(['erro' => 'Especifique o outro recurso de acessibilidade.'], 422);
}
if ($site !== '' && !filter_var($site, FILTER_VALIDATE_URL)) {
    responder(['erro' => 'Informe um endereço válido para o site.'], 422);
}

$fotos = $_FILES['fotos'] ?? null;
$quantidadeFotos = is_array($fotos['name'] ?? null) ? count($fotos['name']) : 0;
if ($quantidadeFotos < 1 || $quantidadeFotos > 8) {
    responder(['erro' => 'Envie entre 1 e 8 fotos.'], 422);
}

$diretorio = dirname(__DIR__) . '/assets/uploads/solicitacoes';
if (!is_dir($diretorio) && !mkdir($diretorio, 0750, true) && !is_dir($diretorio)) {
    responder(['erro' => 'Não foi possível preparar o envio das fotos.'], 500);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$arquivos = [];
for ($i = 0; $i < $quantidadeFotos; $i++) {
    if (($fotos['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($fotos['size'][$i] ?? 0) > 5 * 1024 * 1024) {
        responder(['erro' => 'Cada foto deve ter no máximo 5 MB.'], 422);
    }
    $mime = $finfo->file($fotos['tmp_name'][$i]);
    if (!isset($extensoes[$mime])) {
        responder(['erro' => 'Use somente fotos JPG, PNG ou WEBP.'], 422);
    }
    $nomeArquivo = bin2hex(random_bytes(20)) . '.' . $extensoes[$mime];
    $arquivos[] = ['temporario' => $fotos['tmp_name'][$i], 'nome' => $nomeArquivo];
}

$usuarioId = (int) $_SESSION['usuario_id'];
$categoriasJson = json_encode($categorias, JSON_UNESCAPED_UNICODE);
$deficienciasJson = json_encode($deficiencias, JSON_UNESCAPED_UNICODE);
$recursosJson = json_encode($recursos, JSON_UNESCAPED_UNICODE);
$con->begin_transaction();
try {
    $stmt = $con->prepare(
        'INSERT INTO locais (usuario_id, nome, endereco, numero, complemento, bairro, cidade, estado, cep,
         latitude, longitude, categorias, deficiencias, outra_categoria, recursos, outro_recurso, observacoes, site,
         instagram, telefone, horario_funcionamento, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'pendente\')'
    );
    $stmt->bind_param('issssssssddssssssssss', $usuarioId, $nome, $endereco, $numero, $complemento, $bairro,
        $cidade, $estado, $cep, $latitude, $longitude, $categoriasJson, $deficienciasJson, $outraCategoria, $recursosJson,
        $outroRecurso, $observacoes, $site, $instagram, $telefone, $horario);
    $stmt->execute();
    $localId = $stmt->insert_id;
    $stmtFoto = $con->prepare('INSERT INTO local_fotos (local_id, arquivo) VALUES (?, ?)');
    foreach ($arquivos as $arquivo) {
        if (!move_uploaded_file($arquivo['temporario'], $diretorio . '/' . $arquivo['nome'])) {
            throw new RuntimeException('Falha ao armazenar uma foto.');
        }
        $caminho = 'assets/uploads/solicitacoes/' . $arquivo['nome'];
        $stmtFoto->bind_param('is', $localId, $caminho);
        $stmtFoto->execute();
    }
    $con->commit();
    responder(['sucesso' => true, 'id' => $localId, 'status' => 'Pendente de avaliação'], 201);
} catch (Throwable $erro) {
    $con->rollback();
    foreach ($arquivos as $arquivo) {
        $destino = $diretorio . '/' . $arquivo['nome'];
        if (is_file($destino)) unlink($destino);
    }
    error_log('Erro ao salvar solicitação: ' . $erro->getMessage());
    responder(['erro' => 'Não foi possível enviar a solicitação. Tente novamente.'], 500);
}

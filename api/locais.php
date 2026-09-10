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

function campoTexto(array $origem, string $campo): string
{
    $valor = $origem[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $resultado = $con->query(
            "SELECT id, nome, endereco, numero, bairro, cidade, estado, latitude, longitude,
                    categorias, deficiencias, recursos, observacoes, site, instagram, telefone, horario_funcionamento,
                    (SELECT GROUP_CONCAT(lf.arquivo ORDER BY lf.id SEPARATOR '||')
                     FROM local_fotos lf WHERE lf.local_id = locais.id) AS fotos,
                    (SELECT ROUND(AVG(a.nota), 1) FROM avaliacoes a WHERE a.local_id = locais.id) AS media_avaliacoes,
                    (SELECT COUNT(*) FROM avaliacoes a WHERE a.local_id = locais.id) AS total_avaliacoes
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
            $linha['fotos'] = array_values(array_filter(explode('||', (string) ($linha['fotos'] ?? ''))));
            $linha['media_avaliacoes'] = $linha['media_avaliacoes'] !== null ? (float) $linha['media_avaliacoes'] : 0.0;
            $linha['total_avaliacoes'] = (int) $linha['total_avaliacoes'];
            unset($linha['latitude'], $linha['longitude']);
            $locais[] = $linha;
        }
        responder(['locais' => $locais]);
    } catch (Throwable $erro) {
        error_log('Erro ao carregar locais: ' . $erro->getMessage());
        responder(['erro' => 'Não foi possível carregar os locais.'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    responder(['erro' => 'Método não permitido.'], 405);
}

if (!isset($_SESSION['usuario_id']) || (int) $_SESSION['usuario_id'] <= 0) {
    responder(['erro' => 'Faça login para contribuir com o IncluCity.'], 401);
}

if (!csrfValido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    responder(['erro' => 'Solicitação inválida. Atualize a página.'], 403);
}

$nome = campoTexto($_POST, 'nome');
$endereco = campoTexto($_POST, 'endereco');
$numero = campoTexto($_POST, 'numero');
$complemento = campoTexto($_POST, 'complemento');
$bairro = campoTexto($_POST, 'bairro');
$cidade = campoTexto($_POST, 'cidade');
$estado = strtoupper(campoTexto($_POST, 'estado'));
$cep = preg_replace('/\D/', '', campoTexto($_POST, 'cep')) ?? '';
$latitude = filter_var($_POST['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_POST['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
$categoriasRecebidas = is_array($_POST['categorias'] ?? null) ? $_POST['categorias'] : [];
$deficienciasRecebidas = is_array($_POST['deficiencias'] ?? null) ? $_POST['deficiencias'] : [];
$recursosRecebidos = is_array($_POST['recursos'] ?? null) ? $_POST['recursos'] : [];
$categoriasPermitidas = ['Restaurante', 'Shopping', 'Mercado', 'Hospital', 'Clínica', 'Farmácia', 'Escola',
    'Faculdade', 'Instituição/serviço', 'Órgão público', 'Igreja', 'Parque', 'Praça', 'Hotel',
    'Transporte público', 'Comércio', 'Espaço cultural', 'Evento', 'Outro'];
$deficienciasPermitidas = ['fisica', 'visual', 'auditiva', 'cognitiva'];
$recursosPermitidos = ['Banheiro acessível', 'Rampa de acesso', 'Elevador acessível', 'Piso tátil',
    'Entrada acessível', 'Vaga acessível', 'Sala de conforto', 'Espaço para cadeira de rodas',
    'Atendimento prioritário', 'Balcão acessível', 'Corrimão', 'Sinalização acessível', 'Braile',
    'Libras', 'Audiodescrição', 'Comunicação acessível', 'Cão-guia permitido', 'Outro'];
$categoriasRecebidas = array_filter($categoriasRecebidas, 'is_string');
$deficienciasRecebidas = array_filter($deficienciasRecebidas, 'is_string');
$recursosRecebidos = array_filter($recursosRecebidos, 'is_string');
$categorias = array_values(array_unique(array_intersect($categoriasRecebidas, $categoriasPermitidas)));
$deficiencias = array_values(array_unique(array_intersect($deficienciasRecebidas, $deficienciasPermitidas)));
$recursos = array_values(array_unique(array_intersect($recursosRecebidos, $recursosPermitidos)));
$outraCategoria = campoTexto($_POST, 'outra_categoria');
$outroRecurso = campoTexto($_POST, 'outro_recurso');
$observacoes = campoTexto($_POST, 'observacoes');
$site = campoTexto($_POST, 'site');
$instagram = campoTexto($_POST, 'instagram');
$telefone = campoTexto($_POST, 'telefone');
$horario = campoTexto($_POST, 'horario_funcionamento');

// Campos de endereço que ajudam na moderação, mas não devem impedir a contribuição.
$numero = $numero !== '' ? $numero : 'S/N';
$bairro = $bairro !== '' ? $bairro : 'Não informado';
$cep = $cep !== '' ? $cep : '00000000';

if (mb_strlen($nome) < 3 || mb_strlen($nome) > 150 || mb_strlen($endereco) < 3 || mb_strlen($endereco) > 255
    || mb_strlen($numero) > 20 || mb_strlen($bairro) > 100
    || $cidade === '' || mb_strlen($cidade) > 100 || mb_strlen($complemento) > 100
    || !preg_match('/^[A-Z]{2}$/', $estado)
    || strlen($cep) !== 8 || $latitude === false || $longitude === false
    || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180
    || !$categorias || !$recursos || ($_POST['declaracao'] ?? null) !== '1') {
    responder(['erro' => 'Preencha todos os campos obrigatórios e confirme a declaração.'], 422);
}

if (mb_strlen($outraCategoria) > 100 || (in_array('Outro', $categorias, true) && $outraCategoria === '')) {
    responder(['erro' => 'Especifique a outra categoria.'], 422);
}
if (mb_strlen($outroRecurso) > 150 || (in_array('Outro', $recursos, true) && $outroRecurso === '')) {
    responder(['erro' => 'Especifique o outro recurso de acessibilidade.'], 422);
}
if ($site !== '' && !filter_var($site, FILTER_VALIDATE_URL)) {
    responder(['erro' => 'Informe um endereço válido para o site.'], 422);
}
if (mb_strlen($site) > 255 || mb_strlen($instagram) > 100 || mb_strlen($telefone) > 30
    || mb_strlen($horario) > 255 || mb_strlen($observacoes) > 2000) {
    responder(['erro' => 'Um ou mais campos excedem o tamanho permitido.'], 422);
}

$fotos = $_FILES['fotos'] ?? null;
$indicesFotos = [];
if (is_array($fotos['error'] ?? null)) {
    foreach ($fotos['error'] as $indice => $erroFoto) {
        if ($erroFoto !== UPLOAD_ERR_NO_FILE) {
            $indicesFotos[] = $indice;
        }
    }
}
$quantidadeFotos = count($indicesFotos);
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
foreach ($indicesFotos as $i) {
    $erroUpload = $fotos['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    $temporario = $fotos['tmp_name'][$i] ?? '';
    $tamanho = (int) ($fotos['size'][$i] ?? 0);
    if ($erroUpload !== UPLOAD_ERR_OK || $tamanho < 1 || $tamanho > 5 * 1024 * 1024
        || !is_string($temporario) || !is_uploaded_file($temporario)) {
        responder(['erro' => 'Cada foto deve ter no máximo 5 MB.'], 422);
    }
    $mime = $finfo->file($temporario);
    if (!isset($extensoes[$mime])) {
        responder(['erro' => 'Use somente fotos JPG, PNG ou WEBP.'], 422);
    }
    $nomeArquivo = bin2hex(random_bytes(20)) . '.' . $extensoes[$mime];
    $arquivos[] = ['temporario' => $temporario, 'nome' => $nomeArquivo];
}

$usuarioId = (int) $_SESSION['usuario_id'];
$categoriasJson = json_encode($categorias, JSON_UNESCAPED_UNICODE);
$deficienciasJson = json_encode($deficiencias, JSON_UNESCAPED_UNICODE);
$recursosJson = json_encode($recursos, JSON_UNESCAPED_UNICODE);
$transacaoAtiva = false;
try {
    $con->begin_transaction();
    $transacaoAtiva = true;
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
    $stmt->close();
    $stmtFoto = $con->prepare('INSERT INTO local_fotos (local_id, arquivo) VALUES (?, ?)');
    foreach ($arquivos as $arquivo) {
        if (!move_uploaded_file($arquivo['temporario'], $diretorio . '/' . $arquivo['nome'])) {
            throw new RuntimeException('Falha ao armazenar uma foto.');
        }
        $caminho = 'assets/uploads/solicitacoes/' . $arquivo['nome'];
        $stmtFoto->bind_param('is', $localId, $caminho);
        $stmtFoto->execute();
    }
    $stmtFoto->close();
    $con->commit();
    $transacaoAtiva = false;
    responder(['sucesso' => true, 'id' => $localId, 'status' => 'Pendente de avaliação'], 201);
} catch (Throwable $erro) {
    if ($transacaoAtiva) {
        $con->rollback();
    }
    foreach ($arquivos as $arquivo) {
        $destino = $diretorio . '/' . $arquivo['nome'];
        if (is_file($destino) && !@unlink($destino)) {
            error_log('Não foi possível remover o upload incompleto: ' . $destino);
        }
    }
    error_log('Erro ao salvar solicitação: ' . $erro->getMessage());
    responder(['erro' => 'Não foi possível enviar a solicitação. Tente novamente.'], 500);
}

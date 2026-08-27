<?php
 
declare(strict_types=1);
 
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/conn.php';

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');

function falharOAuth(string $mensagem): never
{
    http_response_code(400);
    $texto = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    exit("<!doctype html><html lang=\"pt-br\"><meta charset=\"utf-8\"><title>Falha no login</title><p>{$texto}</p><p><a href=\"../pages/login.php\">Voltar ao login</a></p></html>");
}
 
function requisicaoHttp(string $url, array $opcoes = []): array
{
    if (!function_exists('curl_init')) {
        falharOAuth('A extensão cURL do PHP precisa estar habilitada.');
    }
 
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => $opcoes['headers'] ?? ['Accept: application/json'],
    ]);
 
    if (isset($opcoes['post'])) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($opcoes['post']));
    }
 
    $resposta = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $erro = curl_error($curl);
    curl_close($curl);
 
    if ($resposta === false || $status < 200 || $status >= 300) {
        error_log("Falha OAuth HTTP {$status}: {$erro}");
        falharOAuth('Não foi possível concluir a comunicação com o provedor.');
    }
 
    $dados = json_decode($resposta, true);
    if (!is_array($dados)) {
        falharOAuth('O provedor retornou uma resposta inválida.');
    }
 
    return $dados;
}
 
function urlBase(): string
{
    $configurada = rtrim((string) ($_ENV['APP_URL'] ?? ''), '/');
    if ($configurada !== '') {
        $partes = parse_url($configurada);
        if (!is_array($partes) || !in_array($partes['scheme'] ?? '', ['http', 'https'], true)
            || empty($partes['host']) || isset($partes['user']) || isset($partes['pass'])) {
            falharOAuth('A URL da aplicação não está configurada corretamente.');
        }
        return $configurada;
    }
 
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $esquema = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (!is_string($host) || !preg_match('/^[a-z0-9.\-]+(?::\d+)?$/i', $host)) {
        falharOAuth('Não foi possível determinar o endereço da aplicação.');
    }
    $diretorio = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_ends_with(rtrim($diretorio, '/'), '/api')) {
        $diretorio = dirname(rtrim($diretorio, '/'));
    }
    return $esquema . '://' . $host . rtrim($diretorio, '/');
}
 
function configuracaoProvedor(string $provedor): array
{
    $configuracoes = [
        'google' => [
            'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
            'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
            'authorize' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token' => 'https://oauth2.googleapis.com/token',
            'userinfo' => 'https://openidconnect.googleapis.com/v1/userinfo',
            'scope' => 'openid profile email',
        ],
        'microsoft' => [
            'client_id' => $_ENV['MICROSOFT_CLIENT_ID'] ?? '',
            'client_secret' => $_ENV['MICROSOFT_CLIENT_SECRET'] ?? '',
            'authorize' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'token' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'userinfo' => 'https://graph.microsoft.com/oidc/userinfo',
            'scope' => 'openid profile email',
        ],
    ];
 
    if (!isset($configuracoes[$provedor])) {
        falharOAuth('Provedor de login inválido.');
    }
 
    $config = $configuracoes[$provedor];
    if ($config['client_id'] === '' || $config['client_secret'] === '') {
        falharOAuth('Este login social ainda não foi configurado no servidor.');
    }
 
    return $config;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    falharOAuth('Método não permitido.');
}

$codigo = isset($_GET['code']) && is_string($_GET['code']) ? trim($_GET['code']) : '';
$erroProvedor = isset($_GET['error']) && is_string($_GET['error']) ? $_GET['error'] : '';
$callback = $codigo !== '' || $erroProvedor !== '';
$provedorRecebido = $callback ? ($_SESSION['oauth_provider'] ?? '') : ($_GET['provider'] ?? '');
$provedor = is_string($provedorRecebido) ? strtolower(trim($provedorRecebido)) : '';
$config = configuracaoProvedor($provedor);
$redirectUri = trim((string) ($_ENV['OAUTH_REDIRECT_URI'] ?? ''));
if ($redirectUri === '') {
    $redirectUri = urlBase() . '/oauth.php';
}
$redirectPartes = parse_url($redirectUri);
if (!is_array($redirectPartes) || !in_array($redirectPartes['scheme'] ?? '', ['http', 'https'], true)
    || empty($redirectPartes['host'])) {
    falharOAuth('A URL de retorno do login não está configurada corretamente.');
}

if (!$callback) {
    $state = bin2hex(random_bytes(32));
    $verificador = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    $desafio = rtrim(strtr(base64_encode(hash('sha256', $verificador, true)), '+/', '-_'), '=');
 
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_provider'] = $provedor;
    $_SESSION['oauth_code_verifier'] = $verificador;
 
    $parametros = [
        'client_id' => $config['client_id'],
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => $config['scope'],
        'state' => $state,
        'code_challenge' => $desafio,
        'code_challenge_method' => 'S256',
        'prompt' => 'select_account',
    ];
 
    header('Location: ' . $config['authorize'] . '?' . http_build_query($parametros));
    exit;
}

$stateRecebido = isset($_GET['state']) && is_string($_GET['state']) ? $_GET['state'] : '';
$stateEsperado = $_SESSION['oauth_state'] ?? '';
if (!is_string($stateEsperado) || $stateEsperado === '' || !hash_equals($stateEsperado, $stateRecebido)) {
    falharOAuth('A validação de segurança do login expirou. Tente novamente.');
}

$verificador = $_SESSION['oauth_code_verifier'] ?? '';
unset($_SESSION['oauth_state'], $_SESSION['oauth_provider'], $_SESSION['oauth_code_verifier']);

if ($erroProvedor !== '') {
    falharOAuth('O login foi cancelado ou recusado pelo provedor.');
}

if ($codigo === '' || !is_string($verificador) || $verificador === '') {
    falharOAuth('Os dados de segurança do login expiraram. Tente novamente.');
}
 
$token = requisicaoHttp($config['token'], [
    'headers' => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
    'post' => [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code' => $codigo,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code',
        'code_verifier' => $verificador,
    ],
]);
 
if (!isset($token['access_token']) || !is_string($token['access_token']) || $token['access_token'] === '') {
    falharOAuth('O provedor não retornou um token de acesso.');
}
 
$perfil = requisicaoHttp($config['userinfo'], [
    'headers' => ['Accept: application/json', 'Authorization: Bearer ' . $token['access_token']],
]);
 
$identificador = isset($perfil['sub']) && is_string($perfil['sub']) ? trim($perfil['sub']) : '';
$email = isset($perfil['email']) && is_string($perfil['email']) ? strtolower(trim($perfil['email'])) : '';
$nome = isset($perfil['name']) && is_string($perfil['name']) ? trim($perfil['name']) : $email;
 
if ($identificador === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    falharOAuth('O provedor não forneceu um e-mail válido para a conta.');
}
 
if ($provedor === 'google' && ($perfil['email_verified'] ?? false) !== true) {
    falharOAuth('O e-mail da conta Google ainda não foi verificado.');
}
 
$transacaoAtiva = false;
try {
    $con->begin_transaction();
    $transacaoAtiva = true;

    $stmt = $con->prepare(
        'SELECT id, nome, email, celular, cpf, tipo_usuario, oauth_provider, oauth_subject
         FROM usuarios WHERE oauth_provider = ? AND oauth_subject = ? LIMIT 1 FOR UPDATE'
    );
    $stmt->bind_param('ss', $provedor, $identificador);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        $stmt = $con->prepare(
            'SELECT id, nome, email, celular, cpf, tipo_usuario, oauth_provider, oauth_subject
             FROM usuarios WHERE email = ? LIMIT 1 FOR UPDATE'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($usuario) {
            if ($usuario['oauth_provider'] !== null || $usuario['oauth_subject'] !== null) {
                throw new DomainException('Esta conta já está vinculada a outro login social.');
            }

            $stmt = $con->prepare(
                'UPDATE usuarios SET oauth_provider = ?, oauth_subject = ?
                 WHERE id = ? AND oauth_provider IS NULL AND oauth_subject IS NULL'
            );
            $usuarioId = (int) $usuario['id'];
            $stmt->bind_param('ssi', $provedor, $identificador, $usuarioId);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) {
                throw new RuntimeException('A associação da conta foi alterada simultaneamente.');
            }
            $stmt->close();
        } else {
            $stmt = $con->prepare(
                'INSERT INTO usuarios (nome, email, celular, cpf, senha, oauth_provider, oauth_subject)
                 VALUES (?, ?, NULL, NULL, NULL, ?, ?)'
            );
            $stmt->bind_param('ssss', $nome, $email, $provedor, $identificador);
            $stmt->execute();
            $usuario = [
                'id' => $stmt->insert_id,
                'nome' => $nome,
                'email' => $email,
                'celular' => '',
                'cpf' => '',
                'tipo_usuario' => 'usuario',
            ];
            $stmt->close();
        }
    }

    $con->commit();
    $transacaoAtiva = false;
} catch (DomainException $erro) {
    if ($transacaoAtiva) {
        $con->rollback();
    }
    falharOAuth($erro->getMessage());
} catch (mysqli_sql_exception $erro) {
    if ($transacaoAtiva) {
        $con->rollback();
    }
    error_log('Erro no login OAuth: ' . $erro->getMessage());
    falharOAuth('Não foi possível acessar sua conta. Tente novamente.');
} catch (Throwable $erro) {
    if ($transacaoAtiva) {
        $con->rollback();
    }
    error_log('Erro inesperado no login OAuth: ' . $erro->getMessage());
    falharOAuth('Não foi possível concluir o login. Tente novamente.');
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_celular'] = $usuario['celular'] ?? '';
$_SESSION['usuario_cpf'] = $usuario['cpf'] ?? '';
$_SESSION['tipo_usuario'] = $usuario['tipo_usuario'] ?? 'usuario';

$con->close();

header('Location: ' . urlBase() . ($_SESSION['tipo_usuario'] === 'admin'
    ? '/pages/admin.php'
    : '/pages/TelaUsuario.php'));
exit;

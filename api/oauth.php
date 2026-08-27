<?php
 
declare(strict_types=1);
 
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/conn.php';

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
        return $configurada;
    }
 
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $esquema = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $diretorio = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
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
 
$provedor = strtolower((string) ($_GET['provider'] ?? $_SESSION['oauth_provider'] ?? 'google'));
$config = configuracaoProvedor($provedor);
$redirectUri = trim((string) ($_ENV['OAUTH_REDIRECT_URI'] ?? ''));
if ($redirectUri === '') {
    $redirectUri = urlBase() . '/oauth.php';
}
 
if (!isset($_GET['code'])) {
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
 
    // Garante que state, provedor e verificador PKCE estejam persistidos antes
    // de o navegador sair do site e seguir para o Google.
    session_write_close();
    header('Location: ' . $config['authorize'] . '?' . http_build_query($parametros));
    exit;
}
 
if (!isset($_GET['state']) || !hash_equals($_SESSION['oauth_state'] ?? '', (string) $_GET['state'])) {
    falharOAuth('A validação de segurança do login expirou. Tente novamente.');
}
 
$token = requisicaoHttp($config['token'], [
    'headers' => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
    'post' => [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code' => (string) $_GET['code'],
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code',
        'code_verifier' => $_SESSION['oauth_code_verifier'] ?? '',
    ],
]);
 
if (empty($token['access_token'])) {
    falharOAuth('O provedor não retornou um token de acesso.');
}
 
$perfil = requisicaoHttp($config['userinfo'], [
    'headers' => ['Accept: application/json', 'Authorization: Bearer ' . $token['access_token']],
]);
 
$identificador = trim((string) ($perfil['sub'] ?? ''));
$email = strtolower(trim((string) ($perfil['email'] ?? '')));
$nome = trim((string) ($perfil['name'] ?? $email));
 
if ($identificador === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    falharOAuth('O provedor não forneceu um e-mail válido para a conta.');
}
 
if ($provedor === 'google' && empty($perfil['email_verified'])) {
    falharOAuth('O e-mail da conta Google ainda não foi verificado.');
}
 
try {
    $stmt = $con->prepare('SELECT id, nome, email, celular, cpf, tipo_usuario FROM usuarios WHERE (oauth_provider = ? AND oauth_subject = ?) OR email = ? LIMIT 1');
    $stmt->bind_param('sss', $provedor, $identificador, $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
 
    if ($usuario) {
        $stmt = $con->prepare('UPDATE usuarios SET oauth_provider = ?, oauth_subject = ? WHERE id = ? AND oauth_subject IS NULL');
        $stmt->bind_param('ssi', $provedor, $identificador, $usuario['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $con->prepare('INSERT INTO usuarios (nome, email, celular, cpf, senha, oauth_provider, oauth_subject) VALUES (?, ?, NULL, NULL, NULL, ?, ?)');
        $stmt->bind_param('ssss', $nome, $email, $provedor, $identificador);
        $stmt->execute();
        $usuario = ['id' => $stmt->insert_id, 'nome' => $nome, 'email' => $email, 'celular' => '', 'cpf' => '', 'tipo_usuario' => 'usuario'];
        $stmt->close();
    }
} catch (mysqli_sql_exception $erro) {
    error_log('Erro no login OAuth: ' . $erro->getMessage());
    falharOAuth('O banco ainda não recebeu a migração OAuth. Importe database/oauth_migration.sql.');
}
 
unset($_SESSION['oauth_state'], $_SESSION['oauth_provider'], $_SESSION['oauth_code_verifier']);
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
 

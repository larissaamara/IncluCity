<?php

// Teste de nova branch da database

require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/conn.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/nova.senha.php");
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Solicitação inválida. Atualize a página e tente novamente.');
}

$recuperacaoAutorizada = !empty($_SESSION["recuperacao_verificada"])
    && isset($_SESSION["email_recuperacao"]);
$usuarioAutenticado = isset($_SESSION["usuario_id"], $_SESSION["usuario_email"]);

if (!$recuperacaoAutorizada && !$usuarioAutenticado) {
    header("Location: ../pages/esqueceu.senha.php");
    exit;
}

$email = $recuperacaoAutorizada
    ? $_SESSION["email_recuperacao"]
    : $_SESSION["usuario_email"];
$novaSenha = $_POST["novaSenha"] ?? "";
$confirmarNovaSenha = $_POST["confirmarNovaSenha"] ?? "";

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-]).{8,}$/', $novaSenha)) {
    echo "<script>alert('Use ao menos 8 caracteres, com maiúscula, minúscula, número e símbolo.'); history.back();</script>";
    exit;
}

if (!hash_equals($novaSenha, $confirmarNovaSenha)) {
    echo "<script>alert('As senhas não são iguais.'); history.back();</script>";
    exit;
}

$senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
$sql = "UPDATE usuarios SET senha = ? WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $senhaHash, $email);

if (!$stmt->execute()) {
    echo "<script>alert('Erro ao alterar senha.'); history.back();</script>";
    exit;
}

if ($stmt->affected_rows !== 1) {
    echo "<script>alert('Usuário não encontrado ou senha não alterada.'); history.back();</script>";
    exit;
}

$stmt->close();
$con->close();

unset(
    $_SESSION["email_recuperacao"],
    $_SESSION["codigo_recuperacao_hash"],
    $_SESSION["codigo_recuperacao_expira"],
    $_SESSION["recuperacao_verificada"],
    $_SESSION["tentativas_codigo"]
);

echo "<script>alert('Senha alterada com sucesso!'); window.location.href = '../pages/login.php';</script>";
exit;
?>

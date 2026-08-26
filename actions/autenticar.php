<?php

require_once dirname(__DIR__) . '/config/session.php';

require_once dirname(__DIR__) . '/config/conn.php';


/*
|--------------------------------------------------------------------------
| Verificar se o formulário foi enviado
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../pages/login.php");
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Solicitação inválida. Atualize a página e tente novamente.');
}


/*
|--------------------------------------------------------------------------
| Receber dados
|--------------------------------------------------------------------------
*/

$login = trim($_POST["login"] ?? "");
$senha = $_POST["senha"] ?? "";


/*
|--------------------------------------------------------------------------
| Verificar campos
|--------------------------------------------------------------------------
*/

if ($login === "" || $senha === "") {

    echo "<script>
        alert('Preencha o usuário e a senha.');
        window.location.href = '../pages/login.php';
    </script>";

    exit;
}


/*
|--------------------------------------------------------------------------
| Procurar usuário por e-mail ou CPF
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, nome, email, celular, cpf, senha, tipo_usuario
        FROM usuarios
        WHERE email = ? OR cpf = ?";

$stmt = $con->prepare($sql);

$stmt->bind_param("ss", $login, $login);

$stmt->execute();

$resultado = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Verificar se usuário existe
|--------------------------------------------------------------------------
*/

if ($resultado->num_rows === 0) {

    echo "<script>
        alert('E-mail, CPF ou senha incorretos.');
        window.location.href = '../pages/login.php';
    </script>";

    $stmt->close();
    $con->close();

    exit;
}


/*
|--------------------------------------------------------------------------
| Pegar dados do usuário
|--------------------------------------------------------------------------
*/

$usuario = $resultado->fetch_assoc();


/*
|--------------------------------------------------------------------------
| Verificar senha
|--------------------------------------------------------------------------
*/

if (!password_verify($senha, $usuario["senha"])) {

    echo "<script>
        alert('E-mail, CPF ou senha incorretos.');
        window.location.href = '../pages/login.php';
    </script>";

    $stmt->close();
    $con->close();

    exit;
}


/*
|--------------------------------------------------------------------------
| Criar sessão
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);
$_SESSION["usuario_id"] = $usuario["id"];
$_SESSION["usuario_nome"] = $usuario["nome"];
$_SESSION["usuario_email"] = $usuario["email"];
$_SESSION["usuario_celular"] = $usuario["celular"];
$_SESSION["usuario_cpf"] = $usuario["cpf"];
$_SESSION["tipo_usuario"] = $usuario["tipo_usuario"] ?? "usuario";


/*
|--------------------------------------------------------------------------
| Login realizado
|--------------------------------------------------------------------------
*/

$stmt->close();
$con->close();

header("Location: " . ($_SESSION["tipo_usuario"] === "admin"
    ? "../pages/admin.php"
    : "../pages/TelaUsuario.php"));
exit;

?>

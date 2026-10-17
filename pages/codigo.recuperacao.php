<?php
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/conn.php';
require_once dirname(__DIR__) . '/config/mailer.php';

$erro = "";
$mensagem = "";
$codigoDesenvolvimento = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["emailCelular"])) {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }
    $identificador = trim($_POST["emailCelular"] ?? "");

    if ($identificador === "") {
        $erro = "Informe seu e-mail ou celular.";
    } else {
        $sql = "SELECT email FROM usuarios WHERE email = ? OR celular = ? LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $identificador, $identificador);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        if (!$usuario) {
            $erro = "Se os dados estiverem cadastrados, um código será enviado.";
        } else {
            $codigo = (string) random_int(100000, 999999);
            $entregaNaTela = ($_ENV['APP_ENV'] ?? '') === 'development'
                && ($_ENV['RECOVERY_DELIVERY'] ?? '') === 'screen';

            try {
                if ($entregaNaTela) {
                    $codigoDesenvolvimento = $codigo;
                } else {
                    enviarCodigoRecuperacao($usuario['email'], $codigo);
                }

                $_SESSION["email_recuperacao"] = $usuario["email"];
                $_SESSION["codigo_recuperacao_hash"] = password_hash($codigo, PASSWORD_DEFAULT);
                $_SESSION["codigo_recuperacao_expira"] = time() + 600;
                $_SESSION["recuperacao_verificada"] = false;
                $_SESSION['tentativas_codigo'] = 0;
                $mensagem = $entregaNaTela
                    ? 'Código de desenvolvimento gerado.'
                    : 'Código enviado. Verifique sua caixa de entrada e a pasta de spam.';
            } catch (Throwable $falhaEmail) {
                error_log('Não foi possível enviar o código de recuperação: ' . $falhaEmail->getMessage());
                unset(
                    $_SESSION["email_recuperacao"],
                    $_SESSION["codigo_recuperacao_hash"],
                    $_SESSION["codigo_recuperacao_expira"],
                    $_SESSION["recuperacao_verificada"],
                    $_SESSION['tentativas_codigo']
                );
                $erro = 'Não foi possível enviar o código agora. Tente novamente em alguns minutos.';
            }
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["codigo"])) {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }

    $codigoInformado = trim($_POST["codigo"] ?? "");
    $hash = $_SESSION["codigo_recuperacao_hash"] ?? "";
    $expira = (int) ($_SESSION["codigo_recuperacao_expira"] ?? 0);

    $tentativas = (int) ($_SESSION['tentativas_codigo'] ?? 0);

    if ($tentativas >= 5) {
        $erro = "Limite de tentativas atingido. Solicite um novo código.";
        unset($_SESSION['codigo_recuperacao_hash'], $_SESSION['codigo_recuperacao_expira'], $_SESSION['tentativas_codigo']);
    } elseif (!isset($_SESSION["email_recuperacao"]) || $hash === "") {
        $erro = "Solicite um novo código de recuperação.";
    } elseif (time() > $expira) {
        $erro = "O código expirou. Solicite um novo código.";
        unset($_SESSION["codigo_recuperacao_hash"], $_SESSION["codigo_recuperacao_expira"]);
    } elseif (!password_verify($codigoInformado, $hash)) {
        $_SESSION['tentativas_codigo'] = $tentativas + 1;
        $erro = "Código inválido.";
    } else {
        $_SESSION["recuperacao_verificada"] = true;
        unset($_SESSION["codigo_recuperacao_hash"], $_SESSION["codigo_recuperacao_expira"], $_SESSION['tentativas_codigo']);
        header("Location: nova.senha.php");
        exit;
    }
} elseif (!isset($_SESSION["email_recuperacao"])) {
    header("Location: esqueceu.senha.php");
    exit;
}

$con->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Código de recuperação</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/codigo.recuperacao.css">
</head>
<body>
  <div class="container">
    <img class="login-img" src="../assets/img/Imagem1.png" alt="Código de recuperação">

    <?php if ($erro !== ""): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($mensagem !== ""): ?>
      <div class="alert alert-success" role="status"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <?php if ($codigoDesenvolvimento !== ""): ?>
      <div class="alert alert-info" role="status">
        Código para teste: <strong><?= htmlspecialchars($codigoDesenvolvimento) ?></strong>
        (válido por 10 minutos)
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION["email_recuperacao"])): ?>
      <form class="form" action="codigo.recuperacao.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input
          placeholder="Digite o código"
          id="codigo"
          name="codigo"
          type="text"
          class="input"
          required
          inputmode="numeric"
          pattern="[0-9]{6}"
          maxlength="6"
          autocomplete="one-time-code"
          title="O código deve conter exatamente 6 números."
        >
        <button type="submit" class="login-button">Confirmar código</button>
      </form>
    <?php else: ?>
      <a href="esqueceu.senha.php" class="btn btn-primary">Solicitar novo código</a>
    <?php endif; ?>
  </div>
</body>
</html>

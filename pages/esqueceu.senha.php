<?php
require_once dirname(__DIR__) . '/config/session.php';

// Remove uma tentativa de recuperacao anterior ao iniciar um novo fluxo.
unset(
    $_SESSION["email_recuperacao"],
    $_SESSION["codigo_recuperacao_hash"],
    $_SESSION["codigo_recuperacao_expira"],
    $_SESSION["recuperacao_verificada"]
);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Esqueceu sua senha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/esqueceu.senha.css">
</head>
<body>
  <div class="container">
    <img class="login-img" src="../assets/img/Imagem1.png" alt="Recuperar senha">

    <form class="form" action="codigo.recuperacao.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <input
        placeholder="Digite seu e-mail ou celular"
        id="emailCelular"
        name="emailCelular"
        type="text"
        class="input"
        required
        autocomplete="username"
      >
      <button type="submit" class="login-button">Gerar código de recupeção</button>
    </form>
  </div>
</body>
</html>

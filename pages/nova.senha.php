<?php
require_once dirname(__DIR__) . '/config/session.php';

$recuperacaoAutorizada = !empty($_SESSION["recuperacao_verificada"])
    && isset($_SESSION["email_recuperacao"]);
$usuarioAutenticado = isset($_SESSION["usuario_id"], $_SESSION["usuario_email"]);

if (!$recuperacaoAutorizada && !$usuarioAutenticado) {
  header("Location: esqueceu.senha.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nova Senha </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/nova.senha.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body onload="mudarposition()">

  <div class="container">
    <img class="login-img" src="../assets/img/Imagem1.png" alt="Nova senha">

    <form class="form"
      action="../actions/alterar_senha.php"
      method="POST"
      onsubmit="return validarNovaSenha()"
    >
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

      <div class="senha-container">
       <input
       placeholder="Nova senha"
       id="novaSenha"
       name="novaSenha"
       type="password"
       class="input"
       required
       minlength="8"
       title="A senha deve conter pelo menos 8 caracteres."
      >
      <span class="toggle-senha" onclick="verSenha('novaSenha')">👁</span>
      </div>

      <div class="senha-container">
        <input
        placeholder="Confirmar nova senha"
        id="confirmarNovaSenha"
        name="confirmarNovaSenha"
        type="password"
        class="input"
        required
        minlength="8"
        title="A senha deve conter pelo menos 8 caracteres."
      >
      <span class="toggle-senha" onclick="verSenha('confirmarNovaSenha')">👁</span>        
      </div>
      Conter 8 caracteres

      <button type="submit" class="login-button">Salvar nova senha</button>
    </form>

  </div>

  <script src="../assets/js/nova.senha.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
    <script src="../assets/js/telainicial.js"></script>
  
<div vw class="enabled">
  <div vw-access-button class="active"></div>
  <div vw-plugin-wrapper>
    <div class="vw-plugin-top-wrapper"></div>
  </div>
</div>
 
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
 
<script>
  new window.VLibras.Widget('https://vlibras.gov.br/app');
</script>
<script src="https://freewebaccessible.com/dist/sienna.min.js" defer></script>
<script>
    function mudarposition() {
      let btnteste = document.querySelector(".asw-menu-btn")
      btnteste.style.top = "315px";
      btnteste.style.width = "36px";
      btnteste.style.height = "36px"; 
      btnteste.style.right = "10px"

    }
  </script>

</body>

</html>

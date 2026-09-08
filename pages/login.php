<?php
require_once dirname(__DIR__) . '/config/session.php';
 
if (usuarioAutenticado()) {
  header('Location: ' . paginaDaConta());
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
 
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>tela de login </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="../assets/css/login.style.css?v=3">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
 
</head>
 
<body onload="mudarposition()">
 
  <div class="container">
    <img class="login-img" src="../assets/img/Imagem1.png" alt="logotipo do login">
    <form class="form" action="../actions/autenticar.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <input
     placeholder="E-mail ou CPF"
     id="login"
     name="login"
     type="text"
     class="input"
     required pattern="(^\d{3}\.\d{3}\.\d{3}-\d{2}$)|(^\d{11}$)|(^[^\s@]+@[^\s@]+\.[^\s@]+$)"
     title="Digite um CPF ou um e-mail válido"/>
 
 
    <div class="senha-container">
      <input
      placeholder="Senha"
      id="senha"
      name="senha"
      type="password"
      class="input"
      required
      pattern="^(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}$"
      title="Mínimo 6 caracteres, com letra, número e caractere especial"/>
      <span class="toggle-senha" onclick="verSenha('senha')">👁</span>
    </div>
   
    <span class="agreement2"><a href="esqueceu.senha.php">Esqueceu sua Senha?</a></span>
   
    <div class="termos-container">
      <input type="checkbox" id="aceite" required>
      <label for="aceite">
        Li e aceito os <a href="termos.php" target="_blank">Termos de uso</a>
        <a href="privacidade.php" target="_blank">Política de Privacidade</a>
      </label>
 
    </div>
     
    <input value="Entrar " type="submit" class="login-button" />
   
      <span class="agreement1"><a href="cadastro.php">Cadastre-se</a></span>
 
 
    </form>
 
    <div class="social-account-container">
      <span class="title">Ou faça login com</span>
      <div class="social-accounts">
         <button type="button" class="social-button google" aria-label="Entrar com Google" onclick="window.location.href='../oauth.php?provider=google'">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="svg" aria-hidden="true">
            <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.41Z"/>
            <path fill="#34A853" d="M12 22c2.7 0 4.98-.9 6.63-2.36l-3.24-2.54c-.9.6-2.05.96-3.39.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z"/>
            <path fill="#FBBC05" d="M6.39 13.93a6 6 0 0 1 0-3.86V7.45H3.04a10 10 0 0 0 0 9.1l3.35-2.62Z"/>
            <path fill="#EA4335" d="M12 5.94c1.47 0 2.79.5 3.83 1.5l2.87-2.88A9.65 9.65 0 0 0 12 2a10 10 0 0 0-8.96 5.45l3.35 2.62C7.18 7.7 9.39 5.94 12 5.94Z"/>
          </svg>
          <span class="google-text">Google</span>
        </button>
       
      </div>
    </div>
    <span class="agreement"><a href="#">Leia o contrato de licença do usuario</a></span>
  </div>
 
  <script src="../assets/js/login.js"></script>
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
      if (btnteste) {
        btnteste.style.top = "315px";
        btnteste.style.width = "36px";
        btnteste.style.height = "36px";
        btnteste.style.right = "10px";
      }
 
    }
  </script>
 
</body>
 
</html>
 

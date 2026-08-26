<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Como Funciona - IncluCity</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/comoFunciona.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body onload="mudarposition()">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="TelaInicial.php">
        <img src="../assets/img/Imagem1.png" alt="IncluCity" class="logotipo">
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="menuNavbar">
        <ul class="navbar-nav align-items-lg-center ms-auto">

          <li class="nav-item">
            <a class="nav-link" href="TelaInicial.php">
              <i class="fa-solid fa-house me-2"></i>Início
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="mapa.php">
              <i class="fa-solid fa-map me-2"></i>Mapa de acessibilidade
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="ComoFunciona.php">
              <i class="fa-solid fa-circle-info me-2"></i>Como funciona
            </a>
          </li>

          <?php if (usuarioAutenticado()): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= paginaDaConta() ?>">
                <i class="fa-solid fa-user me-2"></i>Minha conta
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">
                <i class="fa-solid fa-user me-2"></i>Login
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="cadastro.php">
                <i class="fa-solid fa-user-plus me-2"></i>Cadastre-se
              </a>
            </li>
          <?php endif; ?>

        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <div class="container_exp interactive-hero">
    <h1>Descubra lugares acessíveis de verdade</h1>
    <p>Veja avaliações reais de acessibilidade e ajude a tornar a cidade mais inclusiva.</p>
    <button class="btn btn-light btn-lg" onclick="window.location.href='mapa.php'">
      Explorar locais acessíveis
    </button>
  </div>

  <!-- STEPS -->
  <section class="steps">

    <div class="step card shadow-sm p-3 text-center">
      <h3>1 Busque</h3>
      <p>Pesquise por cidade ou tipo de estabelecimento e aplique filtros de acessibilidade.</p>
      <img src="../assets/img/Busque.jpg.jpg" alt="Pessoa buscando locais acessíveis">
    </div>

    <div class="step card shadow-sm p-3 text-center">
      <h3>2 Verifique</h3>
      <p>Confira informações detalhadas como rampas, banheiros adaptados e sinalização.</p>
      <img src="../assets/img/verifique.pnj.png" alt="Verificação de acessibilidade">
    </div>

    <div class="step card shadow-sm p-3 text-center">
      <h3>3 Avalie</h3>
      <p>Avalie e compartilhe sua experiência para ajudar outras pessoas.</p>
      <img src="../assets/img/Avalie.jpj.jpg" alt="Pessoa avaliando um local">
    </div>

  </section>

  <!-- FOOTER -->

  <footer class="footer">
    <div class="footer-container">

      <div class="footer-col footer-brand">
        <h2>IncluCity</h2>
        <p>
          Tecnologia para cidades mais acessíveis, conectando pessoas a informações
          sobre mobilidade urbana e inclusão.
        </p>
      </div>

      <div class="footer-col">
        <h3>Navegação</h3>
        <ul>
          <li><a href="TelaInicial.php">Início</a></li>
          <li><a href="mapa.php">Mapa de Acessibilidade</a></li>
          <li><a href="ComoFunciona.php">Como Funciona</a></li>
          <?php if (usuarioAutenticado()): ?>
            <li><a href="<?= paginaDaConta() ?>">Minha conta</a></li>
          <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="cadastro.php">Cadastre-se</a></li>
          <?php endif; ?>
        </ul>
        
      </div>

      <div class="footer-col">
        <h3>Contato</h3>
        <ul>
          <li><a href="#">contato@inclucity.com</a></li>
          <li><a href="#">(12) 98215-9944</a></li>
          <li><a href="#">São José dos Campos - SP</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h3>Redes</h3>
        <div class="footer-social">
          <a href="#"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <p>© 2026 IncluCity — Todos os direitos reservados.</p>
    </div>
  </footer>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/como_funciona.js"></script>
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

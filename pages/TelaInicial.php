<?php
require_once dirname(__DIR__) . '/config/session.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Tela Inicio- IncluCity</title>

  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ICONES -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/telainicial.css">
</head>



<body onload="mudarposition()">


  <!-- NAVBAR -->

  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="TelaInicial.php">
        <img src="../assets/img/Imagem_logo40up.webp" alt="IncluCity" class="logotipo">
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNavbar"
        aria-controls="menuNavbar" aria-expanded="false" aria-label="Alternar navegação">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="menuNavbar">
        <ul class="navbar-nav align-items-lg-center ms-auto">
          <li class="nav-item">

            <a class="nav-link" href="TelaInicial.php"><i class="fa-solid fa-house"></i>Início</a>

          </li>

          <li class="nav-item">

            <a class="nav-link" href="mapa.php"><i class="fa-solid fa-map"></i>Mapa de acessibilidade</a>

          </li>

          <li class="nav-item">

            <a class="nav-link" href="ComoFunciona.php"><i class="fa-solid fa-circle-info"></i>Como funciona</a>

          </li>


          <?php if (usuarioAutenticado()): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= paginaDaConta() ?>"><i class="fa-solid fa-user"></i>Minha conta</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php"><i class="fa-solid fa-user"></i>Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="cadastro.php"><i class="fa-solid fa-user-plus"></i>Cadastre-se</a>
            </li>
          <?php endif; ?>


        </ul>
      </div>
    </div>
  </nav>
  <!-- HERO -->

  <section class="hero">

    <span class="visually-hidden">
      Imagem de fundo mostrando acessibilidade urbana e inclusão nas cidades
    </span>

    <div>

      <h1>Como Funciona o IncluCity? </h1>

      <p>Uma plataforma colaborativa que promove acessibilidade urbana.</p>

      <button class="btn btn-light btn-lg" onclick="window.location.href='ComoFunciona.php'">Saiba Mais </button>

    </div>


  </section>

  <section class="quem-somos-section">
    <div class="quem-somos-card">
      <h2 class="card-title">Quem Somos</h2>
      <p class="small-desc">
        O IncluCity é uma iniciativa tecnológica criada para transformar a mobilidade urbana por meio da
        colaboração e da inovação digital. Somos desenvolvedores, cidadãos e defensores de uma cidade mais justa, que
        acreditam que a tecnologia deve servir para incluir — nunca excluir.
        Nosso propósito é construir uma plataforma que permita que qualquer pessoa navegue pela cidade com mais
        segurança, autonomia e informação confiável.
      </p>
    </div>
  </section>
  <div class="container-info d-flex justify-content-center gap-5 flex-wrap">

    <div class="card">
      <p class="card-title">🤝 Inclusão</p>
      <p class="small-desc">
        Acreditamos que toda pessoa merece acesso igual aos espaços urbanos.
      </p>

    </div>
    <div class="card">
      <p class="card-title">🌍 Impacto Social</p>
      <p class="small-desc">
        Desenvolvemos tecnologia com propósito e responsabilidade social.
      </p>

    </div>
    <div class="card">
      <p class="card-title">🔎 Transparência</p>
      <p class="small-desc">
        Valorizamos informações reais, colaborativas e confiáveis.
      </p>

    </div>
    <div class="card">
      <p class="card-title">🚀 Inovação</p>
      <p class="small-desc">
        Buscamos constantemente soluções tecnológicas que simplifiquem e ampliem o acesso.
      </p>

    </div>
    <div class="card">
      <p class="card-title">🧠 Empatia</p>
      <p class="small-desc">
        Entendemos que cada usuário vive uma realidade diferente, e respeitamos essa diversidade.
      </p>

    </div>
    <div class="card">
      <p class="card-title">🤲 Colaboração</p>
      <p class="small-desc">
        Acreditamos na força da comunidade para construir mudanças concretas.
      </p>

    </div>

  </div>

  <section class="filtros">

    <h2>Filtros Inteligentes</h2>

    <p>Personalize sua navegação conforme sua necessidade.</p>

    <button class="btn btn-primary">Física</button>

    <button class="btn btn-success">Visual</button>

    <button class="btn btn-warning">Auditiva</button>

    <button class="btn btn-danger">Intelectual</button>

    <button class="btn btn-dark">Mental</button>

  </section>

  <!-- AVALIAÇÕES -->

  <section class="avaliacoes">

    <h2>Avaliações e Alertas</h2>

    <p>Avalie locais de 1 a 5 estrelas.</p>

    <div class="estrelas">

      <i class="fa-solid fa-star"></i>
      <i class="fa-solid fa-star"></i>
      <i class="fa-solid fa-star"></i>
      <i class="fa-solid fa-star"></i>
      <i class="fa-regular fa-star"></i>

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

  <!-- JAVASCRIPT -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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


  <style>
    .asw-widget {
      position: absolute;
      top: 100px;
      right: 100px;
    }

    .asw-menu-btn {
      position: absolute;
      top: 100px;
      right: 100px;
    }
  </style>

 <script>
    function mudarposition() {
      let btnteste = document.querySelector(".asw-menu-btn")
      if (!btnteste) return;
      btnteste.style.top = "315px";
      btnteste.style.width = "36px";
      btnteste.style.height = "36px"; 
      btnteste.style.right = "10px"

    }
  </script>
</body>

</html>

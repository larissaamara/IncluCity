<?php
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/conn.php';

if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

$nomeUsuario = htmlspecialchars((string) ($_SESSION['usuario_nome'] ?? ''), ENT_QUOTES, 'UTF-8');
$emailUsuario = htmlspecialchars((string) ($_SESSION['usuario_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$cpfUsuario = htmlspecialchars((string) ($_SESSION['usuario_cpf'] ?? ''), ENT_QUOTES, 'UTF-8');
$usuarioId = (int) $_SESSION['usuario_id'];

// Mantém a tela compatível tanto com o esquema atual quanto com bancos antigos.
$colunasLocais = array_column(
  $con->query('SHOW COLUMNS FROM locais')->fetch_all(MYSQLI_ASSOC),
  'Field'
);
$colunaComentario = in_array('observacoes', $colunasLocais, true)
  ? 'COALESCE(observacoes, "")'
  : 'COALESCE(comentario, "")';
$colunaRecursos = in_array('recursos', $colunasLocais, true)
  ? 'recursos'
  : "'[]'";

$stmtLocais = $con->prepare(
  "SELECT nome, {$colunaComentario} AS comentario, {$colunaRecursos} AS recursos, status, data_cadastro
   FROM locais WHERE usuario_id = ? ORDER BY data_cadastro DESC"
);
$stmtLocais->bind_param('i', $usuarioId);
$stmtLocais->execute();
$locaisUsuario = $stmtLocais->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtLocais->close();

function escapar(string $valor): string
{
  return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tela Usuário - IncluCity</title>

  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ICONES -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="../assets/css/telaUsuario.css">
</head>

<body onload="mudarposition()">

  <!-- NAVBAR -->

  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="TelaInicial.php">
        <img src="../assets/img/Imagem1.png" alt="IncluCity" class="logotipo">
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

          <li class="nav-item">

            <a class="nav-link" href="login.php"><i class="fa-solid fa-user"></i>Login</a>

          </li>
          <li class="nav-item">
            <a class="nav-link" href="cadastro.php"><i class="fa-solid fa-user"></i>Cadastre-se</a>

          </li>

        </ul>
      </div>
    </div>
  </nav>

  <main class="painel-usuario">

    <!-- PERFIL -->
    <section class="coluna-perfil">
      <button class="btn-menu-usuario" id="btnMenuUsuario" aria-label="Abrir dados do usuário">
        <i class="fa-solid fa-bars"></i>
        <span>Dados do usuário</span>
      </button>

      <section class="bloco perfil-usuario" id="menuUsuario">
        <div class="foto-usuario">
          <img src="../assets/img/—Pngtree—avatar bussinesman man profile icon_7268049.png" alt="Foto do usuário">
        </div>

        <div class="dados-usuario">
          <h2>Dados do usuário</h2>

          <div class="linha-info">
            <span class="label">Nome:</span>
            <span class="valor"><?= $nomeUsuario ?></span>
          </div>

          <div class="linha-info">
            <span class="label">CPF:</span>
            <span class="valor"><?= $cpfUsuario ?></span>
          </div>

          <div class="linha-info">
            <span class="label">E-mail:</span>
            <span class="valor"><?= $emailUsuario ?></span>
          </div>

          <div class="linha-info">
            <span class="label">Senha:</span>
            <span class="valor">************</span>
          </div>

          <div class="linha-info">
            <span class="label">Ação:</span>
            <a href="nova.senha.php" class="valor link-acao">Alterar senha</a>
          </div>

          <div class="linha-info">
            <span class="label">Sessão:</span>
            <form action="../actions/logout.php" method="POST" class="valor">
              <input type="hidden" name="csrf_token" value="<?= escapar(csrfToken()) ?>">
              <button type="submit" class="link-acao btn btn-link p-0">Sair</button>
            </form>
          </div>
        </div>
      </section>
    </section>

    <!-- COMENTÁRIOS -->
    <section class="bloco comentarios-usuario">
      <h2>Comentários</h2>

      <div class="lista-cards">
        <?php if (!$locaisUsuario): ?>
          <p>Você ainda não publicou comentários.</p>
        <?php else: ?>
          <?php foreach ($locaisUsuario as $local): ?>
            <article class="card-item">
              <h3><?= escapar($local['nome']) ?></h3>
              <p><?= escapar($local['comentario']) ?></p>
              <span class="meta-card">Comentado em <?= date('d/m/Y', strtotime($local['data_cadastro'])) ?></span>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- LOCAIS ADICIONADOS -->
    <section class="bloco locais-usuario">
      <h2>Locais adicionados por você</h2>

      <div class="lista-cards">
        <?php if (!$locaisUsuario): ?>
          <p>Você ainda não adicionou locais.</p>
        <?php else: ?>
          <?php foreach ($locaisUsuario as $local): ?>
            <?php $recursos = json_decode($local['recursos'], true) ?: []; ?>
            <article class="card-item">
              <h3><?= escapar($local['nome']) ?></h3>
              <p><?= escapar($recursos ? implode(', ', $recursos) : 'Recursos não informados') ?></p>
              <span class="meta-card">
                Adicionado em <?= date('d/m/Y', strtotime($local['data_cadastro'])) ?> — <?= escapar($local['status']) ?>
              </span>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

  </main>

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
          <li><a href="login.php">Login</a></li>
          <li><a href="cadastro.php">Cadastre-se</a></li>
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

  <script src="../assets/js/Telausuario.script.js"></script>
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

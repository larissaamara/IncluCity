<?php
require_once dirname(__DIR__) . '/config/session.php';
$usuarioAutenticado = isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] > 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
  <title>Mapa de Acessibilidade - IncluCity</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/mapa.css?v=7">
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
          <li class="nav-item"><a class="nav-link" href="TelaInicial.php"><i
                class="fa-solid fa-house me-2"></i>Início</a></li>

          <li class="nav-item"><a class="nav-link" href="mapa.php"><i class="fa-solid fa-map me-2"></i>Mapa de acessibilidade</a></li>

          <li class="nav-item"><a class="nav-link" href="ComoFunciona.php"><i
                class="fa-solid fa-circle-info me-2"></i>Como funciona</a></li>

          <?php if ($usuarioAutenticado): ?>
            <li class="nav-item"><a class="nav-link" href="<?= paginaDaConta() ?>"><i class="fa-solid fa-user me-2"></i>Minha conta</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa-solid fa-user me-2"></i>Login</a></li>
            <li class="nav-item"><a class="nav-link" href="cadastro.php"><i class="fa-solid fa-user-plus me-2"></i>Cadastre-se</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- BOTÃO MOBILE 
  <button id="btnListaAcessivel" class="btn btn-primary mb-3 d-lg-none">Ver lista acessível</button>-->

  <!-- LAYOUT -->
  <div class="container-principal">


    <!-- SIDEBAR -->
    <div class="sidebar">
      <button type="button" id="btnMenuMapa" class="menu-hamburguer" aria-label="Abrir menu de contribuição" aria-expanded="false" <?= $usuarioAutenticado ? '' : 'hidden' ?>>
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
        <span>Contribuir</span>
      </button>
      <h3>Mapa Acessível</h3>

      <label class="rotulo-busca" for="buscaLocal">Pesquisar local</label>
      <div class="campo-busca">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" id="buscaLocal" class="form-control" placeholder="Digite o nome do local" autocomplete="off" aria-describedby="resumoLocais">
      </div>

      <select id="filtroCategoria" class="form-select mb-2" onchange="filtrar()">
        <option value="todos">Todas as categorias</option>
        <option value="Restaurante">Restaurante</option>
        <option value="Shopping">Shopping</option>
        <option value="Mercado">Mercado</option>
        <option value="Hospital">Hospital</option>
        <option value="Clínica">Clínica</option>
        <option value="Farmácia">Farmácia</option>
        <option value="Escola">Escola</option>
        <option value="Faculdade">Faculdade</option>
        <option value="Instituição/serviço">Instituição ou serviço</option>
        <option value="Órgão público">Órgão público</option>
        <option value="Igreja">Igreja ou espaço religioso</option>
        <option value="Parque">Parque</option>
        <option value="Praça">Praça</option>
        <option value="Hotel">Hotel ou hospedagem</option>
        <option value="Transporte público">Transporte público</option>
        <option value="Comércio">Comércio</option>
        <option value="Espaço cultural">Espaço cultural</option>
        <option value="Evento">Evento</option>
        <option value="Outro">Outro</option>
      </select>

      <select id="filtroDeficiencia" class="form-select mb-2" onchange="filtrar()">
        <option value="todos">Todas as deficiências</option>
        <option value="fisica">Deficiência física ou mobilidade reduzida</option>
        <option value="visual">Deficiência visual</option>
        <option value="auditiva">Deficiência auditiva</option>
        <option value="cognitiva">Deficiência intelectual, cognitiva ou psicossocial</option>
      </select>

      <select id="filtroRecurso" class="form-select mb-3" onchange="filtrar()">
        <option value="todos">Todos os recursos</option>
        <option value="Banheiro acessível">Banheiro acessível</option>
        <option value="Rampa de acesso">Rampa de acesso</option>
        <option value="Elevador acessível">Elevador acessível</option>
        <option value="Piso tátil">Piso tátil</option>
        <option value="Entrada acessível">Entrada acessível</option>
        <option value="Vaga acessível">Vaga acessível</option>
        <option value="Sala de conforto">Sala de conforto</option>
        <option value="Espaço para cadeira de rodas">Espaço para cadeira de rodas</option>
        <option value="Atendimento prioritário">Atendimento prioritário</option>
        <option value="Balcão acessível">Balcão acessível</option>
        <option value="Corrimão">Corrimão</option>
        <option value="Sinalização acessível">Sinalização acessível</option>
        <option value="Braile">Braile</option>
        <option value="Libras">Atendimento em Libras</option>
        <option value="Audiodescrição">Audiodescrição</option>
        <option value="Comunicação acessível">Comunicação acessível</option>
        <option value="Cão-guia permitido">Cão-guia permitido</option>
        <option value="Outro">Outro recurso</option>
      </select>

      <div class="rodape-filtros">
        <p id="resumoLocais" class="resumo-locais" aria-live="polite">Carregando locais...</p>
        <button type="button" id="btnLimparFiltros" class="btn-limpar-filtros" hidden>Limpar filtros</button>
      </div>

      <div id="listaLocais"></div>
    </div>

    <!-- BOTÃO OFFCANVAS LATERAL 
    <button class="btn btn-primary btn-offcanvas-lateral d-lg-none" type="button" data-bs-toggle="offcanvas"
      data-bs-target="#offcanvasLista">
      Lista
    </button>-->

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasLista">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title">Locais Acessíveis</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body" id="demoLocais"></div>
    </div>

    <!-- MAPA -->
    <div id="map"></div>
  </div>
  <aside id="menuMapa" class="menu-mapa" aria-hidden="true" <?= $usuarioAutenticado ? '' : 'hidden' ?>>
    <button type="button" id="btnFecharMenu" class="menu-fechar" aria-label="Fechar menu">&times;</button>
    <h2>Contribua com o mapa</h2>
    <button type="button" id="btnAdicionarLocal" class="menu-opcao">
      <i class="fa-solid fa-location-dot" aria-hidden="true"></i> Sugerir um novo local
    </button>
  </aside>

  <div id="formAdicionarLocal" class="modal-form" role="dialog" aria-modal="true" aria-labelledby="tituloSolicitacao" <?= $usuarioAutenticado ? '' : 'hidden' ?>>
    <form id="formLocal" class="form-accessible" enctype="multipart/form-data" novalidate>
      <button type="button" id="btnFechar" class="btn-fechar" aria-label="Fechar formulário">&times;</button>
      <h3 id="tituloSolicitacao">Sugerir um novo local</h3>
      <p class="form-intro">Conte o que você sabe. São apenas três etapas, com orientações simples em cada uma.</p>
      <p class="form-obrigatorios"><span aria-hidden="true">*</span> indica informação obrigatória.</p>

      <ol class="progresso-formulario" aria-label="Progresso do cadastro">
        <li class="ativo" aria-current="step"><span>1</span> Local</li>
        <li><span>2</span> Acessibilidade</li>
        <li><span>3</span> Finalizar</li>
      </ol>
      <div id="erroFormulario" class="erro-formulario" role="alert" tabindex="-1"></div>

      <section class="form-etapa" data-etapa="0" aria-labelledby="etapaLocal">

      <fieldset>
        <legend id="etapaLocal">1. Qual é o local?</legend>
        <div class="form-grid">
          <label class="campo campo-largo">Nome do estabelecimento ou local <span class="asterisco" aria-hidden="true">*</span><input name="nome" id="nome" required minlength="3" maxlength="150" autocomplete="organization"></label>
          <label class="campo campo-largo">Rua ou endereço <span class="asterisco" aria-hidden="true">*</span><input name="endereco" id="endereco" required minlength="3" maxlength="255" autocomplete="street-address" placeholder="Ex.: Avenida Central"></label>
          <label class="campo">Número <small>(opcional)</small><input name="numero" id="numero" maxlength="20" placeholder="Ex.: 120 ou S/N"></label>
          <label class="campo">Complemento <small>(opcional)</small><input name="complemento" id="complemento" maxlength="100"></label>
          <label class="campo">Bairro <small>(opcional)</small><input name="bairro" id="bairro" maxlength="100" autocomplete="address-level3"></label>
          <label class="campo">Cidade <span class="asterisco" aria-hidden="true">*</span><input name="cidade" id="cidade" required maxlength="100" autocomplete="address-level2"></label>
          <label class="campo">Estado <span class="asterisco" aria-hidden="true">*</span><input name="estado" id="estado" required maxlength="2" pattern="[A-Za-z]{2}" value="SP" autocomplete="address-level1" aria-describedby="ajudaEstado"><small id="ajudaEstado">Use duas letras, como SP.</small></label>
          <label class="campo">CEP <small>(opcional)</small><input name="cep" id="cep" inputmode="numeric" maxlength="9" autocomplete="postal-code" placeholder="00000-000"></label>
        </div>
        <input type="hidden" name="latitude" id="latitude"><input type="hidden" name="longitude" id="longitude">
        <button type="button" id="btnSelecionarMapa" class="btn-selecionar-mapa"><i class="fa-solid fa-map-pin"></i> Selecionar endereço diretamente no mapa</button>
        <p id="localizacaoStatus" class="status-localizacao" role="status">Localização ainda não selecionada.</p>
      </fieldset>

      <fieldset>
        <legend>Tipo de local <span class="asterisco" aria-hidden="true">*</span><span class="visually-hidden"> (obrigatório)</span></legend>
        <p>Escolha pelo menos uma opção.</p>
        <div class="chips" id="categorias">
          <?php foreach (['Restaurante', 'Shopping', 'Mercado', 'Hospital', 'Clínica', 'Farmácia', 'Escola', 'Faculdade', 'Instituição/serviço', 'Órgão público', 'Igreja', 'Parque', 'Praça', 'Hotel', 'Transporte público', 'Comércio', 'Espaço cultural', 'Evento', 'Outro'] as $i => $categoria): ?>
            <label class="chip"><input type="checkbox" name="categorias[]" value="<?= htmlspecialchars($categoria) ?>"><span><?= htmlspecialchars($categoria) ?></span></label>
          <?php endforeach; ?>
        </div>
        <label id="campoOutraCategoria" class="campo condicional">Especifique a categoria<input name="outra_categoria" id="outraCategoria" maxlength="100"></label>
      </fieldset>

        <div class="navegacao-etapa navegacao-direita">
          <button type="button" class="btn-proximo" data-proxima>Continuar para acessibilidade <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
      </section>

      <section class="form-etapa" data-etapa="1" aria-labelledby="etapaAcessibilidade" hidden>

      <fieldset>
        <legend id="etapaAcessibilidade">2. Acessibilidade</legend>
        <p>Se souber, selecione quais pessoas podem ser atendidas. Esta parte é opcional.</p>
        <div class="chips" id="deficiencias">
          <?php foreach (['fisica' => 'Deficiência física ou mobilidade reduzida', 'visual' => 'Deficiência visual', 'auditiva' => 'Deficiência auditiva', 'cognitiva' => 'Deficiência intelectual, cognitiva ou psicossocial'] as $valor => $rotulo): ?>
            <label class="chip"><input type="checkbox" name="deficiencias[]" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></span></label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <fieldset>
        <legend>Quais recursos o local possui? <span class="asterisco" aria-hidden="true">*</span><span class="visually-hidden"> (obrigatório)</span></legend>
        <p>Escolha pelo menos um recurso que você observou. Não precisa conhecer todos.</p>
        <div class="chips" id="recursos">
          <?php foreach (['Banheiro acessível', 'Rampa de acesso', 'Elevador acessível', 'Piso tátil', 'Entrada acessível', 'Vaga acessível', 'Sala de conforto', 'Espaço para cadeira de rodas', 'Atendimento prioritário', 'Balcão acessível', 'Corrimão', 'Sinalização acessível', 'Braile', 'Libras', 'Audiodescrição', 'Comunicação acessível', 'Cão-guia permitido', 'Outro'] as $recurso): ?>
            <label class="chip"><input type="checkbox" name="recursos[]" value="<?= htmlspecialchars($recurso) ?>"><span><?= htmlspecialchars($recurso) ?></span></label>
          <?php endforeach; ?>
        </div>
        <label id="campoOutroRecurso" class="campo condicional">Especifique o recurso<input name="outro_recurso" id="outroRecurso" maxlength="150"></label>
      </fieldset>

        <div class="navegacao-etapa">
          <button type="button" class="btn-voltar" data-anterior><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Voltar</button>
          <button type="button" class="btn-proximo" data-proxima>Continuar <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
      </section>

      <section class="form-etapa" data-etapa="2" aria-labelledby="etapaFinalizar" hidden>

      <fieldset>
        <legend id="etapaFinalizar">3. Foto <span class="asterisco" aria-hidden="true">*</span><span class="visually-hidden"> (obrigatória)</span></legend>
        <p>Envie pelo menos uma foto que ajude a confirmar a acessibilidade informada.</p>
        <label class="botao-fotos"><i class="fa-solid fa-camera"></i> Escolher fotos<input type="file" name="fotos[]" id="fotos" accept="image/jpeg,image/png,image/webp" multiple required></label>
        <div id="previewFotos" class="preview-fotos" aria-live="polite"></div>
        <small>Envie de 1 a 8 fotos, com no máximo 5 MB cada. Evite imagens com dados pessoais de outras pessoas.</small>
      </fieldset>

      <fieldset>
        <legend>Conte mais <small>(opcional)</small></legend>
        <label class="campo"><textarea name="observacoes" id="observacoes" maxlength="2000" placeholder="Conte algo importante sobre a acessibilidade desse local…"></textarea></label>
      </fieldset>

      <fieldset>
        <legend>Informações adicionais <small>(opcional)</small></legend>
        <div class="form-grid">
          <label class="campo">Site<input type="url" name="site" id="site" placeholder="https://"></label>
          <label class="campo">Instagram<input name="instagram" id="instagram" maxlength="100" placeholder="@perfil"></label>
          <label class="campo">Telefone<input name="telefone" id="telefone" maxlength="30"></label>
          <label class="campo">Horário de funcionamento<input name="horario_funcionamento" id="horarioFuncionamento" maxlength="255"></label>
        </div>
      </fieldset>

      <label class="declaracao"><input type="checkbox" name="declaracao" value="1" required> Declaro que as informações fornecidas foram verificadas por mim e correspondem ao que encontrei no local.</label>
      <p class="aviso-envio">O envio desta solicitação não garante a publicação do local. As informações serão avaliadas pela equipe responsável pelo mapa.</p>
      <div class="navegacao-etapa">
        <button type="button" class="btn-voltar" data-anterior><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Voltar</button>
        <div class="form-buttons"><button type="submit" class="btn-enviar">Enviar local para análise</button><button type="button" id="btnCancelar" class="btn-cancelar">Cancelar</button></div>
      </div>
      </section>
    </form>
  </div>

  <div id="confirmacaoSolicitacao" class="modal-confirmacao" role="dialog" aria-modal="true" aria-labelledby="tituloConfirmacao" <?= $usuarioAutenticado ? '' : 'hidden' ?>>
    <div>
      <h2 id="tituloConfirmacao">Solicitação enviada com sucesso! 💙</h2>
      <p>Obrigado por contribuir com o nosso mapa. Nossa equipe irá analisar as informações e as evidências enviadas. Se a solicitação for aprovada, o local será adicionado ao mapa.</p><button type="button" id="btnFecharConfirmacao">Entendi</button>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-col footer-brand">
        <h2>IncluCity</h2>
        <p>Tecnologia para cidades mais acessíveis, conectando pessoas a informações sobre mobilidade urbana e inclusão.
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="../assets/js/mapa.js?v=8"></script>
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
      if (!btnteste) return;
      btnteste.style.top = "315px";
      btnteste.style.width = "36px";
      btnteste.style.height = "36px";
      btnteste.style.right = "10px"

    }
  </script>
</body>

</html>

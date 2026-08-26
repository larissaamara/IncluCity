<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/admin.php';
require_once dirname(__DIR__) . '/config/conn.php';
exigirAdmin();

$sql = "SELECT l.*, u.nome AS usuario_nome, u.email AS usuario_email,
        GROUP_CONCAT(f.arquivo ORDER BY f.id SEPARATOR '||') AS fotos
        FROM locais l
        LEFT JOIN usuarios u ON u.id = l.usuario_id
        LEFT JOIN local_fotos f ON f.local_id = l.id
        GROUP BY l.id
        ORDER BY FIELD(l.status, 'pendente', 'em_analise', 'mais_informacoes', 'aprovado', 'reprovado'), l.data_cadastro DESC";
$locais = $con->query($sql)->fetch_all(MYSQLI_ASSOC);
$usuarios = $con->query('SELECT id, nome, email, tipo_usuario, data_cadastro FROM usuarios ORDER BY nome, email')->fetch_all(MYSQLI_ASSOC);
$administradores = array_values(array_filter($usuarios, static fn(array $usuario): bool => $usuario['tipo_usuario'] === 'admin'));

function e(string|null $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$contadores = array_fill_keys(['todos', 'pendente', 'aprovado', 'reprovado', 'outros'], 0);
foreach ($locais as $local) {
    $contadores['todos']++;
    if ($local['status'] === 'pendente') $contadores['pendente']++;
    elseif ($local['status'] === 'aprovado') $contadores['aprovado']++;
    elseif ($local['status'] === 'reprovado') $contadores['reprovado']++;
    else $contadores['outros']++;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
  <title>Painel administrativo — IncluCity</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
</head>
<body>
  <header class="topo-admin">
    <div class="topo-container">
      <a href="TelaInicial.php" class="marca"><img src="../assets/img/Imagem_logo40up.webp" alt="IncluCity"></a>
      <nav class="navegacao-admin" aria-label="Navegação principal">
        <a href="TelaInicial.php"><i class="fa-solid fa-house"></i> Início</a>
        <a href="mapa.php"><i class="fa-solid fa-map"></i> Mapa de acessibilidade</a>
      </nav>
      <div class="admin-identidade"><span>Administrador</span><strong><?= e($_SESSION['usuario_nome'] ?? '') ?></strong>
        <button type="button" id="btnConfiguracoes" class="btn-configuracoes" aria-label="Abrir configurações" aria-expanded="false" aria-controls="painelConfiguracoes"><i class="fa-solid fa-gear"></i></button>
      </div>
    </div>
  </header>

  <div id="fundoConfiguracoes" class="fundo-configuracoes" hidden></div>
  <aside id="painelConfiguracoes" class="painel-configuracoes" aria-hidden="true" aria-labelledby="tituloConfiguracoes">
    <div class="configuracoes-topo">
      <div><span class="rotulo">CONTA E ACESSOS</span><h2 id="tituloConfiguracoes">Configurações</h2></div>
      <button type="button" id="btnFecharConfiguracoes" aria-label="Fechar configurações"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="opcoes-configuracoes" aria-label="Configurações da conta">
      <a href="TelaUsuario.php"><i class="fa-solid fa-user-pen"></i><span><strong>Informações pessoais</strong><small>Visualize seus dados e suas contribuições</small></span></a>
      <a href="nova.senha.php"><i class="fa-solid fa-key"></i><span><strong>Alterar senha</strong><small>Atualize sua senha de acesso</small></span></a>
    </nav>
    <section class="usuarios-configuracoes">
      <div class="secao-titulo"><h3>Usuários cadastrados</h3><p><?= count($usuarios) ?> conta<?= count($usuarios) === 1 ? '' : 's' ?> na plataforma</p></div>
      <div class="lista-usuarios-configuracoes">
        <?php foreach ($usuarios as $usuario): ?>
          <div class="usuario-linha" data-usuario-id="<?= (int) $usuario['id'] ?>">
            <div class="usuario-avatar"><?= e(mb_strtoupper(mb_substr($usuario['nome'], 0, 1))) ?></div>
            <div class="usuario-dados"><strong><?= e($usuario['nome']) ?></strong><span><?= e($usuario['email']) ?></span></div>
            <select class="tipo-usuario" aria-label="Permissão de <?= e($usuario['nome']) ?>" <?= (int) $usuario['id'] === (int) $_SESSION['usuario_id'] ? 'disabled title="Sua própria permissão não pode ser removida"' : '' ?>>
              <option value="usuario" <?= $usuario['tipo_usuario'] === 'usuario' ? 'selected' : '' ?>>Usuário</option>
              <option value="admin" <?= $usuario['tipo_usuario'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <form class="sair-configuracoes" action="../actions/logout.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Sair da conta</button>
    </form>
  </aside>

  <main class="admin-container">
    <section class="cabecalho-painel">
      <div><span class="rotulo">MODERAÇÃO</span><h1>Solicitações de locais</h1><p>Analise as informações enviadas antes de publicá-las no mapa.</p></div>
    </section>

    <section class="resumo" aria-label="Resumo das solicitações">
      <button type="button" class="resumo-card ativo" data-resumo="todos" data-filtrar-status="todos" aria-pressed="true"><span>Total</span><strong><?= $contadores['todos'] ?></strong></button>
      <button type="button" class="resumo-card pendentes" data-resumo="pendente" data-filtrar-status="pendente" aria-pressed="false"><span>Pendentes</span><strong><?= $contadores['pendente'] ?></strong></button>
      <button type="button" class="resumo-card aprovados" data-resumo="aprovado" data-filtrar-status="aprovado" aria-pressed="false"><span>Aprovados</span><strong><?= $contadores['aprovado'] ?></strong></button>
      <button type="button" class="resumo-card recusados" data-resumo="reprovado" data-filtrar-status="reprovado" aria-pressed="false"><span>Recusados</span><strong><?= $contadores['reprovado'] ?></strong></button>
    </section>

    <section class="barra-filtros">
      <label><i class="fa-solid fa-magnifying-glass"></i><input id="buscaAdmin" type="search" placeholder="Buscar por nome, bairro ou cidade"></label>
      <select id="statusAdmin" aria-label="Filtrar por status">
        <option value="todos">Todos os status</option><option value="pendente">Pendentes</option><option value="aprovado">Aprovados</option><option value="reprovado">Recusados</option><option value="outros">Outros status</option>
      </select>
    </section>

    <p id="mensagemAdmin" class="mensagem" role="status" aria-live="polite"></p>
    <section id="listaAdmin" class="lista-admin">
      <?php foreach ($locais as $local):
        $categorias = json_decode($local['categorias'], true) ?: [];
        $deficiencias = json_decode($local['deficiencias'] ?? '[]', true) ?: [];
        $recursos = json_decode($local['recursos'], true) ?: [];
        $fotos = $local['fotos'] ? explode('||', $local['fotos']) : [];
        $busca = strtolower($local['nome'] . ' ' . $local['bairro'] . ' ' . $local['cidade']);
      ?>
        <article class="solicitacao" data-id="<?= (int) $local['id'] ?>" data-status="<?= e($local['status']) ?>" data-busca="<?= e($busca) ?>">
          <div class="solicitacao-topo">
            <div><span class="status status-<?= e($local['status']) ?>"><?= e(str_replace('_', ' ', $local['status'])) ?></span><h2><?= e($local['nome']) ?></h2><p><i class="fa-solid fa-location-dot"></i> <?= e($local['endereco']) ?>, <?= e($local['numero']) ?> — <?= e($local['bairro']) ?>, <?= e($local['cidade']) ?>/<?= e($local['estado']) ?></p></div>
            <time datetime="<?= e($local['data_cadastro']) ?>"><?= date('d/m/Y H:i', strtotime($local['data_cadastro'])) ?></time>
          </div>
          <div class="detalhes-grid">
            <div><h3>Categorias</h3><div class="chips"><?php foreach ($categorias as $item): ?><span><?= e($item) ?></span><?php endforeach; ?></div></div>
            <div><h3>Deficiências atendidas</h3><div class="chips"><?php foreach ($deficiencias as $item): ?><span><?= e(['fisica'=>'Física ou mobilidade reduzida','visual'=>'Visual','auditiva'=>'Auditiva','cognitiva'=>'Intelectual, cognitiva ou psicossocial'][$item] ?? $item) ?></span><?php endforeach; ?><?php if (!$deficiencias): ?><span>Não informado</span><?php endif; ?></div></div>
            <div><h3>Recursos informados</h3><div class="chips recursos"><?php foreach ($recursos as $item): ?><span><?= e($item) ?></span><?php endforeach; ?></div></div>
            <div><h3>Responsável pelo envio</h3><p><?= e($local['usuario_nome'] ?: 'Registro sem responsável') ?><br><small><?= e($local['usuario_email']) ?></small></p></div>
            <div><h3>Observações</h3><p><?= e($local['observacoes'] ?: 'Nenhuma observação informada.') ?></p></div>
          </div>
          <?php if ($fotos): ?><div class="galeria"><?php foreach ($fotos as $foto): ?><a href="../<?= e($foto) ?>" target="_blank"><img src="../<?= e($foto) ?>" alt="Evidência enviada para <?= e($local['nome']) ?>"></a><?php endforeach; ?></div><?php endif; ?>
          <div class="acoes">
            <?php if ($local['status'] !== 'aprovado'): ?><button class="btn-aprovar" data-acao="aprovar"><i class="fa-solid fa-check"></i> Aprovar e publicar</button><?php endif; ?>
            <?php if ($local['status'] !== 'reprovado'): ?><button class="btn-recusar" data-acao="recusar"><i class="fa-solid fa-ban"></i> Recusar</button><?php endif; ?>
            <button class="btn-excluir" data-acao="excluir"><i class="fa-regular fa-trash-can"></i> Excluir</button>
          </div>
        </article>
      <?php endforeach; ?>
      <p id="semResultados" class="sem-resultados" hidden>Nenhuma solicitação encontrada.</p>
    </section>

    <section class="usuarios-admin">
      <div class="secao-titulo"><span class="rotulo">ACESSOS</span><h2>Administradores</h2><p>Escolha quais contas podem acessar este painel e moderar solicitações.</p></div>
      <div class="tabela-usuarios">
        <?php foreach ($administradores as $usuario): ?>
          <div class="usuario-linha" data-usuario-id="<?= (int) $usuario['id'] ?>">
            <div class="usuario-avatar"><?= e(mb_strtoupper(mb_substr($usuario['nome'], 0, 1))) ?></div>
            <div class="usuario-dados"><strong><?= e($usuario['nome']) ?></strong><span><?= e($usuario['email']) ?></span></div>
            <span class="administrador-badge"><i class="fa-solid fa-shield-halved"></i> Administrador</span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
  <script src="../assets/js/admin.js?v=5"></script>
</body>
</html>
